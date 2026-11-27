<?php

namespace App\Services\PdfImport\Contracts;

/**
 * Strategy interface cho từng bộ phân tích phần thi TOEIC từ dữ liệu layout PDF.
 */
interface ToeicPdfSectionParserInterface
{
    /**
     * Xác định Strategy có áp dụng cho phạm vi import này hay không.
     */
    public function supportsScope(string $importScope): bool;

    /**
     * Phân tích dữ liệu trang PDF đã extract thành danh sách câu hỏi chuẩn hóa theo question_number.
     *
     * @param  array<string, mixed>  $extractedDocument
     * @return list<array<string, mixed>>
     */
    public function parse(array $extractedDocument): array;
}
