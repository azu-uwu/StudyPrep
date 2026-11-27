<?php

namespace App\Services\PdfImport\Contracts;

/**
 * Adapter interface tách biệt tầng trích xuất vật lý PDF (PyMuPDF + pdfplumber)
 * khỏi tầng phân tích ngữ nghĩa câu hỏi TOEIC.
 */
interface PdfLayoutExtractorInterface
{
    /**
     * Trích xuất text blocks (đã sắp xếp thứ tự đọc 1/2 cột), bảng biểu và ảnh kèm tọa độ từ file PDF.
     *
     * @return array{
     *   page_count: int,
     *   pages: list<array{
     *     page_no: int,
     *     width: float,
     *     height: float,
     *     is_two_column: bool,
     *     blocks: list<array{x0: float, y0: float, x1: float, y1: float, text: string, column: string}>,
     *     tables: list<array{table_index: int, x0: float, y0: float, x1: float, y1: float, rows: list<string>, text: string}>,
     *     images: list<array{file_name: string, relative_path: string, page_no: int, width: int, height: int, x0: float, y0: float, x1: float, y1: float}>
     *   }>
     * }
     */
    public function extract(string $pdfAbsolutePath, string $imageOutputDir, string $relativeImagePrefix): array;
}
