<?php

namespace App\Services\PdfImport\Extractors;

use App\Services\PdfImport\Contracts\PdfLayoutExtractorInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Adapter gọi script Python (PyMuPDF + pdfplumber) để trích xuất layout PDF chính xác.
 */
class PythonPdfLayoutExtractor implements PdfLayoutExtractorInterface
{
    public function extract(string $pdfAbsolutePath, string $imageOutputDir, string $relativeImagePrefix): array
    {
        if (! File::isFile($pdfAbsolutePath)) {
            throw new RuntimeException("Không tìm thấy file PDF tại đường dẫn: {$pdfAbsolutePath}");
        }

        File::ensureDirectoryExists($imageOutputDir);

        $scriptPath = base_path('scripts/pdf_toeic_extract.py');
        if (! File::isFile($scriptPath)) {
            throw new RuntimeException('Thiếu script trích xuất PDF: scripts/pdf_toeic_extract.py');
        }

        $pythonBin = $this->resolvePythonBinary();
        $result = Process::timeout(120)->run([
            $pythonBin,
            $scriptPath,
            $pdfAbsolutePath,
            $imageOutputDir,
        ]);

        if (! $result->successful()) {
            $err = trim($result->errorOutput() ?: $result->output());
            $decodedErr = json_decode($err, true);
            $errorCode = is_array($decodedErr) ? (string) ($decodedErr['error_code'] ?? '') : '';

            if (
                $errorCode === 'PDF_PASSWORD_PROTECTED'
                || str_contains(strtolower($err), 'needs_pass')
                || str_contains(strtolower($err), 'password')
                || str_contains(strtolower($err), 'encrypted')
            ) {
                throw new RuntimeException('File PDF đang được bảo vệ bằng mật khẩu hoặc không thể trích xuất, vui lòng mở khóa PDF hoặc chuyển sang file .docx.');
            }

            if (
                str_contains($err, 'ModuleNotFoundError')
                || str_contains($err, 'No module named')
                || str_contains($err, 'is not recognized')
                || str_contains($err, 'No such file or directory')
            ) {
                throw new RuntimeException('Môi trường trích xuất PDF chưa sẵn sàng (thiếu Python/PyMuPDF/pdfplumber). Vui lòng kiểm tra cấu hình hoặc chuyển sang file .docx.');
            }

            $friendlyMsg = is_array($decodedErr) && ! empty($decodedErr['error'])
                ? (string) $decodedErr['error']
                : $err;

            throw new RuntimeException('File PDF không thể trích xuất, vui lòng kiểm tra lại file hoặc chuyển sang file .docx. Chi tiết: '.$friendlyMsg);
        }

        $rawOutput = trim($result->output());
        $jsonStart = strpos($rawOutput, '{');
        if ($jsonStart !== false && $jsonStart > 0) {
            $rawOutput = substr($rawOutput, $jsonStart);
        }

        $decoded = json_decode($rawOutput, true);
        if (! is_array($decoded) || ! isset($decoded['pages']) || ! is_array($decoded['pages'])) {
            throw new RuntimeException('Dữ liệu JSON trả về từ trình trích xuất PDF không hợp lệ.');
        }

        $prefix = rtrim(str_replace('\\', '/', $relativeImagePrefix), '/');
        foreach ($decoded['pages'] as $pIdx => $page) {
            if (! isset($page['images']) || ! is_array($page['images'])) {
                continue;
            }
            foreach ($page['images'] as $iIdx => $img) {
                $fileName = (string) ($img['file_name'] ?? '');
                $decoded['pages'][$pIdx]['images'][$iIdx]['relative_path'] = $fileName !== ''
                    ? $prefix.'/'.$fileName
                    : '';
            }
        }

        return $decoded;
    }

    private function resolvePythonBinary(): string
    {
        $configured = (string) env('PDF_IMPORT_PYTHON_BIN', '');
        if ($configured !== '' && File::exists($configured)) {
            return $configured;
        }

        $laragonPython = 'D:\\HoangMyLinh\\laragon\\bin\\python\\python-3.10\\python.exe';
        if (File::exists($laragonPython)) {
            return $laragonPython;
        }

        return PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
    }
}
