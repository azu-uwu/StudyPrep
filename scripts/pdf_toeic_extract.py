#!/usr/bin/env python3
"""
TOEIC PDF Layout Extractor (PyMuPDF + pdfplumber)
-------------------------------------------------
Extracts text blocks (with 2-column reading-order sorting), tables, and
embedded images (with bounding-box coordinates) from a TOEIC PDF booklet
or Answer/Transcript PDF.

Usage:
    python pdf_toeic_extract.py <pdf_path> <image_output_dir>
"""

import json
import os
import re
import sys
from typing import Any, Dict, List

try:
    import pymupdf as fitz  # type: ignore[import-not-found]
except ImportError:
    import fitz  # type: ignore[import-not-found]

import pdfplumber  # type: ignore[import-not-found]
from PIL import Image  # type: ignore[import-not-found]


def auto_crop_photo_box(pil_img: Image.Image) -> Image.Image:
    """
    Find the largest non-white rectangular content block in a Part 1 scan half-page
    (skipping the small question number '1.'..'6.' separated by whitespace above)
    and tight-crop around the photograph with 3px padding.
    """
    gray = pil_img.convert("L")
    w, h = gray.size
    if w < 100 or h < 100:
        return pil_img

    # Downsample to 200px width for fast (< 0.05s) bounding-box detection
    thumb_w = 200
    thumb_h = max(1, int(h * thumb_w / w))
    small = gray.resize((thumb_w, thumb_h))
    sw, sh = small.size
    scale_x = w / float(sw)
    scale_y = h / float(sh)
    pixels = small.load()

    # Count non-white pixels (RGB/L < 240) per row
    row_counts = []
    for y in range(sh):
        cnt = 0
        for x in range(sw):
            if pixels[x, y] < 240:
                cnt += 1
        row_counts.append(cnt)

    # Rows belonging to a photo have substantial horizontal density (>= 18% of width)
    min_row_dark = max(12, int(sw * 0.18))
    bands = []
    in_band = False
    band_start = 0
    gap_tol = 4
    gap_count = 0

    for y, cnt in enumerate(row_counts):
        if cnt >= min_row_dark:
            if not in_band:
                in_band = True
                band_start = y
            gap_count = 0
        elif in_band:
            gap_count += 1
            if gap_count > gap_tol:
                bands.append((band_start, y - gap_count))
                in_band = False
    if in_band:
        bands.append((band_start, sh - 1))

    if not bands:
        return pil_img

    # Pick the tallest vertical band (the actual photograph frame, not small text lines)
    best_y0, best_y1 = max(bands, key=lambda b: b[1] - b[0])
    if (best_y1 - best_y0) < int(sh * 0.25):
        return pil_img

    # Find horizontal bounds inside the chosen vertical band
    col_counts = []
    min_col_dark = max(4, int((best_y1 - best_y0) * 0.12))
    for x in range(sw):
        cnt = 0
        for y in range(best_y0, best_y1 + 1):
            if pixels[x, y] < 240:
                cnt += 1
        col_counts.append(cnt)

    active_cols = [x for x, c in enumerate(col_counts) if c >= min_col_dark]
    if not active_cols:
        return pil_img

    best_x0 = active_cols[0]
    best_x1 = active_cols[-1]

    pad = 3
    crop_x0 = max(0, int(best_x0 * scale_x) - pad)
    crop_y0 = max(0, int(best_y0 * scale_y) - pad)
    crop_x1 = min(w, int((best_x1 + 1) * scale_x) + pad)
    crop_y1 = min(h, int((best_y1 + 1) * scale_y) + pad)

    if (crop_x1 - crop_x0) < 200 or (crop_y1 - crop_y0) < 150:
        return pil_img

    return pil_img.crop((crop_x0, crop_y0, crop_x1, crop_y1))


def has_photograph_region(pil_img: Image.Image) -> bool:
    """
    Detect whether a half-page scan crop contains an actual Part 1 photograph
    (tall contiguous dense band >= 28% of half-page height) rather than Directions text.
    """
    gray = pil_img.convert("L")
    w, h = gray.size
    if w < 100 or h < 100:
        return False

    thumb_w = 200
    thumb_h = max(1, int(h * thumb_w / w))
    small = gray.resize((thumb_w, thumb_h))
    sw, sh = small.size
    pixels = small.load()

    min_row_dark = max(16, int(sw * 0.22))
    best_span = 0
    in_band = False
    band_start = 0
    gap_count = 0

    for y in range(sh):
        cnt = 0
        for x in range(sw):
            if pixels[x, y] < 235:
                cnt += 1
        if cnt >= min_row_dark:
            if not in_band:
                in_band = True
                band_start = y
            gap_count = 0
        elif in_band:
            gap_count += 1
            if gap_count > 2:
                best_span = max(best_span, (y - gap_count) - band_start)
                in_band = False
    if in_band:
        best_span = max(best_span, (sh - 1) - band_start)

    return best_span >= int(sh * 0.28)


WATERMARK_PATTERNS = [
    re.compile(r"Hien\s*Nhung\s*TOEIC", re.IGNORECASE),
    re.compile(r"Teach\s*By\s*Heart", re.IGNORECASE),
    re.compile(r"\b(?:Hien|Nhung|Nh|Teach|By|Heart)\b(?=\s+(?:Hien|Nhung|Nh|Teach|By|Heart)\b)", re.IGNORECASE),
]


def clean_watermark_text(text: str) -> str:
    """Remove repeating watermark strings and fragmented watermark tokens."""
    cleaned = text
    for pat in WATERMARK_PATTERNS:
        cleaned = pat.sub("", cleaned)
    cleaned = re.sub(r"[ \t]{2,}", " ", cleaned)
    return cleaned.strip()


def detect_two_column(blocks: List[Dict[str, Any]], page_width: float) -> bool:
    """Detect whether a page uses a two-column layout based on block x-coordinates."""
    if len(blocks) < 4 or page_width <= 0:
        return False

    split_x = page_width * 0.48
    left_blocks = [b for b in blocks if b["x0"] < split_x and b["x1"] <= page_width * 0.56]
    right_blocks = [b for b in blocks if b["x0"] >= split_x]

    return len(left_blocks) >= 2 and len(right_blocks) >= 2


def sort_blocks_reading_order(
    blocks: List[Dict[str, Any]], page_width: float, is_two_column: bool
) -> List[Dict[str, Any]]:
    """Sort blocks in natural reading order (left column x0 < 0.48*W first, then right column)."""
    if not is_two_column:
        return sorted(blocks, key=lambda b: (round(b["y0"], 1), round(b["x0"], 1)))

    split_x = page_width * 0.48
    header_blocks = []
    left_blocks = []
    right_blocks = []

    for b in blocks:
        # Full-width banners / headers (e.g., "Questions 147-150 refer to...")
        if b["x0"] < page_width * 0.42 and b["x1"] > page_width * 0.58:
            header_blocks.append(b)
        elif b["x0"] < split_x:
            left_blocks.append(b)
        else:
            right_blocks.append(b)

    header_blocks.sort(key=lambda b: round(b["y0"], 1))
    left_blocks.sort(key=lambda b: (round(b["y0"], 1), round(b["x0"], 1)))
    right_blocks.sort(key=lambda b: (round(b["y0"], 1), round(b["x0"], 1)))

    top_y = min([b["y0"] for b in left_blocks + right_blocks], default=99999.0)
    top_headers = [b for b in header_blocks if b["y0"] <= top_y + 15]
    other_headers = [b for b in header_blocks if b["y0"] > top_y + 15]

    for b in left_blocks:
        b["column"] = "left"
    for b in right_blocks:
        b["column"] = "right"
    for b in top_headers + other_headers:
        b["column"] = "full"

    return top_headers + left_blocks + right_blocks + other_headers


def extract_pdf(pdf_path: str, image_output_dir: str) -> Dict[str, Any]:
    os.makedirs(image_output_dir, exist_ok=True)

    try:
        fitz_doc = fitz.open(pdf_path)
    except Exception as exc:
        print(
            json.dumps(
                {
                    "error_code": "PDF_OPEN_FAILED",
                    "error": f"Không thể mở file PDF: {exc}",
                },
                ensure_ascii=False,
            ),
            file=sys.stderr,
        )
        sys.exit(1)

    if getattr(fitz_doc, "needs_pass", False):
        fitz_doc.close()
        print(
            json.dumps(
                {
                    "error_code": "PDF_PASSWORD_PROTECTED",
                    "error": "File PDF đang được bảo vệ bằng mật khẩu hoặc mã hóa, vui lòng mở khóa PDF hoặc chuyển sang file .docx.",
                },
                ensure_ascii=False,
            ),
            file=sys.stderr,
        )
        sys.exit(2)

    try:
        plumber_doc = pdfplumber.open(pdf_path)
    except Exception as exc:
        fitz_doc.close()
        print(
            json.dumps(
                {
                    "error_code": "PDF_OPEN_FAILED",
                    "error": f"Không thể đọc cấu trúc bảng PDF: {exc}",
                },
                ensure_ascii=False,
            ),
            file=sys.stderr,
        )
        sys.exit(1)

    pages_out: List[Dict[str, Any]] = []
    xref_page_counts: Dict[int, int] = {}

    # Pre-scan image xref frequency to filter repeating header/footer logos
    for page_index in range(len(fitz_doc)):
        page_xrefs = {info[0] for info in fitz_doc[page_index].get_images(full=True)}
        for xr in page_xrefs:
            xref_page_counts[xr] = xref_page_counts.get(xr, 0) + 1

    try:
        for page_index in range(len(fitz_doc)):
            page_no = page_index + 1
            f_page = fitz_doc[page_index]
            p_page = plumber_doc.pages[page_index] if page_index < len(plumber_doc.pages) else None

            page_width = float(f_page.rect.width)
            page_height = float(f_page.rect.height)

            # 1. Extract text blocks via PyMuPDF
            raw_blocks = f_page.get_text("blocks")
            text_blocks: List[Dict[str, Any]] = []
            for b in raw_blocks:
                # block tuple: (x0, y0, x1, y1, text, block_no, block_type)
                if len(b) < 7 or b[6] != 0:
                    continue
                text = clean_watermark_text(b[4] or "")
                if not text:
                    continue
                text_blocks.append(
                    {
                        "x0": round(float(b[0]), 2),
                        "y0": round(float(b[1]), 2),
                        "x1": round(float(b[2]), 2),
                        "y1": round(float(b[3]), 2),
                        "text": text,
                        "column": "single",
                    }
                )

            is_two_col = detect_two_column(text_blocks, page_width)
            sorted_blocks = sort_blocks_reading_order(text_blocks, page_width, is_two_col)

            # Check whether this page is a Part 1 Directions / Example page (skip all images on it)
            page_text_lower = " ".join(b["text"] for b in sorted_blocks).lower()
            is_directions_or_example = (
                "directions" in page_text_lower
                or "example" in page_text_lower
                or "statement (c)" in page_text_lower
            )
            if not is_directions_or_example and "part 1" in page_text_lower:
                import re
                has_real_p1_q = bool(re.search(r"(?:question\s+[1-6]\b|\b[1-6]\s*[\.\)])", page_text_lower))
                if not has_real_p1_q:
                    is_directions_or_example = True

            # 2. Extract tables via pdfplumber
            tables_out: List[Dict[str, Any]] = []
            if p_page is not None:
                try:
                    found_tables = p_page.find_tables()
                    for t_idx, tbl in enumerate(found_tables):
                        rows = tbl.extract() or []
                        cleaned_rows = []
                        for row in rows:
                            cells = [
                                clean_watermark_text(" ".join(str(cell or "").split())) for cell in row
                            ]
                            if any(cells):
                                cleaned_rows.append(" | ".join(cells))
                        if cleaned_rows:
                            bbox = tbl.bbox or (0, 0, page_width, page_height)
                            tables_out.append(
                                {
                                    "table_index": t_idx,
                                    "x0": round(float(bbox[0]), 2),
                                    "y0": round(float(bbox[1]), 2),
                                    "x1": round(float(bbox[2]), 2),
                                    "y1": round(float(bbox[3]), 2),
                                    "rows": cleaned_rows,
                                    "text": "\n".join(cleaned_rows),
                                }
                            )
                except Exception:
                    # Keep text extraction resilient even if table detection fails on a page
                    pass

            # 3. Extract embedded images + bounding boxes via PyMuPDF
            images_out: List[Dict[str, Any]] = []
            if not is_directions_or_example:
                img_list = f_page.get_images(full=True)
                for img_idx, img_info in enumerate(img_list):
                    xref = img_info[0]
                    if len(fitz_doc) >= 3 and xref_page_counts.get(xref, 0) >= 3:
                        # Skip repeating header/footer logos across pages
                        continue
                    try:
                        base_img = fitz_doc.extract_image(xref)
                        img_bytes = base_img.get("image")
                        img_ext = base_img.get("ext", "png")
                        img_w = int(base_img.get("width", 0))
                        img_h = int(base_img.get("height", 0))
                        if not img_bytes or img_w < 120 or img_h < 120:
                            continue

                        file_name = f"p{page_no}_img{img_idx + 1}.{img_ext}"
                        out_path = os.path.join(image_output_dir, file_name)
                        with open(out_path, "wb") as f_out:
                            f_out.write(img_bytes)

                        rects = f_page.get_image_rects(xref)
                        rect = rects[0] if rects else fitz.Rect(0, 0, img_w, img_h)

                        images_out.append(
                            {
                                "file_name": file_name,
                                "path": out_path,
                                "page_no": page_no,
                                "width": img_w,
                                "height": img_h,
                                "x0": round(float(rect.x0), 2),
                                "y0": round(float(rect.y0), 2),
                                "x1": round(float(rect.x1), 2),
                                "y1": round(float(rect.y1), 2),
                            }
                        )
                    except Exception:
                        continue

                images_out.sort(key=lambda im: (round(im["y0"], 1), round(im["x0"], 1)))

                # Detect full-width horizontal scan strips (e.g. scanner splits 1 page into 3 strips)
                # Render and crop cleanly into top/bottom question regions instead of keeping cut strips.
                is_scan_strips = (
                    len(text_blocks) == 0
                    and len(images_out) >= 2
                    and sum(1 for im in images_out if (im["x1"] - im["x0"]) >= page_width * 0.85) >= 2
                )
                if is_scan_strips:
                    images_out = []
                    crop_boxes = [
                        fitz.Rect(page_width * 0.05, page_height * 0.04, page_width * 0.95, page_height * 0.48),
                        fitz.Rect(page_width * 0.05, page_height * 0.49, page_width * 0.95, page_height * 0.91),
                    ]
                    cropped_pairs = []
                    for c_idx, clip_rect in enumerate(crop_boxes):
                        try:
                            pix = f_page.get_pixmap(clip=clip_rect, dpi=140)
                            pil_raw = Image.frombytes("RGB", [pix.width, pix.height], pix.samples)
                            is_photo = has_photograph_region(pil_raw)
                            cropped_pairs.append((c_idx, pil_raw, is_photo))
                        except Exception:
                            continue

                    # Part 1 Directions/Example page: top half is Directions text (no photo), bottom half is 1 Example photo -> skip entire page
                    if len(cropped_pairs) == 2 and (not cropped_pairs[0][2]) and cropped_pairs[1][2]:
                        is_directions_or_example = True
                        cropped_pairs = []

                    for c_idx, pil_raw, _is_photo in cropped_pairs:
                        try:
                            tight_img = auto_crop_photo_box(pil_raw)
                            crop_name = f"p{page_no}_crop{c_idx + 1}.png"
                            crop_path = os.path.join(image_output_dir, crop_name)
                            tight_img.save(crop_path, format="PNG")
                            tw, th = tight_img.size
                            images_out.append(
                                {
                                    "file_name": crop_name,
                                    "path": crop_path,
                                    "page_no": page_no,
                                    "width": int(tw),
                                    "height": int(th),
                                    "x0": round(float(crop_boxes[c_idx].x0), 2),
                                    "y0": round(float(crop_boxes[c_idx].y0), 2),
                                    "x1": round(float(crop_boxes[c_idx].x1), 2),
                                    "y1": round(float(crop_boxes[c_idx].y1), 2),
                                }
                            )
                        except Exception:
                            continue

            pages_out.append(
                {
                    "page_no": page_no,
                    "width": round(page_width, 2),
                    "height": round(page_height, 2),
                    "is_two_column": is_two_col,
                    "is_directions_or_example": is_directions_or_example,
                    "blocks": sorted_blocks,
                    "tables": tables_out,
                    "images": images_out,
                }
            )
    finally:
        fitz_doc.close()
        plumber_doc.close()

    return {
        "page_count": len(pages_out),
        "pages": pages_out,
    }


def main() -> None:
    if len(sys.argv) < 3:
        print(
            json.dumps(
                {"error": "Usage: pdf_toeic_extract.py <pdf_path> <image_output_dir>"}
            ),
            file=sys.stderr,
        )
        sys.exit(1)

    pdf_path = sys.argv[1]
    image_output_dir = sys.argv[2]

    if not os.path.isfile(pdf_path):
        print(
            json.dumps({"error": f"PDF file not found: {pdf_path}"}),
            file=sys.stderr,
        )
        sys.exit(1)

    result = extract_pdf(pdf_path, image_output_dir)
    sys.stdout.reconfigure(encoding="utf-8")
    print(json.dumps(result, ensure_ascii=False))


if __name__ == "__main__":
    main()
