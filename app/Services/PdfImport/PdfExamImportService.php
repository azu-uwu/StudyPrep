<?php

namespace App\Services\PdfImport;

use App\Models\PdfImportSession;
use App\Models\PdfImportStagingQuestion;
use App\Services\DocxToStandardXmlService;
use App\Services\PdfImport\Contracts\PdfLayoutExtractorInterface;
use App\Services\PdfImport\Parsers\PdfAnswerScriptParser;
use App\Services\PdfImport\Parsers\PdfListeningQuestionParser;
use App\Services\PdfImport\Parsers\PdfReadingQuestionParser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Pipeline điều phối toàn bộ quy trình Bulk Import đề thi TOEIC L&R từ PDF:
 * 1. Lưu file PDF theo import_session_id
 * 2. Extract & Parse PDF Đề (Listening / Reading Strategy)
 * 3. Extract & Parse PDF Đáp án/Script (nếu có)
 * 4. Merge theo question_number (ToeicExamMapper)
 * 5. Validate cấu trúc chuẩn TOEIC
 * 6. Lưu vào bảng Staging (pdf_import_sessions + pdf_import_staging_questions) & staging.json
 * 7. Trả về kết quả cho Staging Review UI
 */
class PdfExamImportService
{
    public function __construct(
        private readonly PdfLayoutExtractorInterface $extractor,
        private readonly PdfListeningQuestionParser $listeningParser,
        private readonly PdfReadingQuestionParser $readingParser,
        private readonly PdfAnswerScriptParser $answerScriptParser,
        private readonly ToeicExamMapper $mapper,
        private readonly DocxToStandardXmlService $stagingBridge,
    ) {}

    /**
     * @return array{
     *   import_session_id: string,
     *   batch_id: string,
     *   summary: array<string, mixed>,
     *   validation_errors: list<string>,
     *   sets: list<array<string, mixed>>
     * }
     */
    public function importToStaging(
        UploadedFile $questionPdf,
        ?UploadedFile $answerPdf = null,
        string $importScope = 'FULL_LR',
        ?int $targetTestId = null,
        string $publishStatus = 'ACTIVE',
        ?int $adminUserId = null
    ): array {
        $this->stagingBridge->cleanupExpiredStagingDirectories(24);

        $sessionId = 'pdf_'.now()->format('Ymd_His').'_'.Str::lower(Str::random(6));
        $scope = in_array(strtoupper($importScope), ['FULL_LR', 'LISTENING', 'READING'], true)
            ? strtoupper($importScope)
            : 'FULL_LR';

        $stagingDir = storage_path('app/public/staging/import_'.$sessionId);
        $imageDir = $stagingDir.'/images';
        File::ensureDirectoryExists($imageDir);

        // 1. Lưu file PDF theo import_session_id
        $qPdfName = $questionPdf->getClientOriginalName() ?: 'questions.pdf';
        $qPdfPath = $stagingDir.'/questions.pdf';
        File::copy($questionPdf->getRealPath(), $qPdfPath);

        $aPdfName = null;
        $aPdfPath = null;
        if ($answerPdf !== null) {
            $aPdfName = $answerPdf->getClientOriginalName() ?: 'answers.pdf';
            $aPdfPath = $stagingDir.'/answers.pdf';
            File::copy($answerPdf->getRealPath(), $aPdfPath);
        }

        $session = PdfImportSession::query()->create([
            'import_session_id' => $sessionId,
            'admin_user_id' => $adminUserId,
            'question_file_name' => $qPdfName,
            'question_pdf_path' => $qPdfPath,
            'answer_file_name' => $aPdfName,
            'answer_pdf_path' => $aPdfPath,
            'import_scope' => $scope,
            'target_test_id' => $targetTestId,
            'publish_status' => $publishStatus,
            'status' => PdfImportSession::STATUS_PARSING,
        ]);

        try {
            // 2. Extract & Parse PDF Đề qua các Strategy (Source-of-Truth Routing: Kịch bản A vs B)
            $relativeImgPrefix = 'storage/staging/import_'.$sessionId.'/images';
            $extractedQuestionDoc = $this->extractor->extract($qPdfPath, $imageDir, $relativeImgPrefix);
            $hasTextLayer = $this->hasSubstantialTextLayer($extractedQuestionDoc);

            $parsedQuestions = [];
            if ($hasTextLayer) {
                // Kịch bản A: PDF Đề có text layer đầy đủ -> File 1 là nguồn chính cho content/options/stimulus/media
                foreach ([$this->listeningParser, $this->readingParser] as $strategy) {
                    if ($strategy->supportsScope($scope)) {
                        $parsedQuestions = array_merge($parsedQuestions, $strategy->parse($extractedQuestionDoc));
                    }
                }
            }

            // 3. Extract & Parse PDF Đáp án/Script (nếu có)
            $answerData = ['answers' => [], 'transcripts' => [], 'explanations' => [], 'fallback_questions' => []];
            if ($aPdfPath !== null) {
                $answerImgDir = $stagingDir.'/answer_images';
                $extractedAnswerDoc = $this->extractor->extract($aPdfPath, $answerImgDir, $relativeImgPrefix);
                $answerData = $this->answerScriptParser->parse($extractedAnswerDoc);
            }

            // Kịch bản B: PDF Đề là bản Scan (không có text layer) -> dùng bảng Script/Key làm khung câu hỏi & ghép ảnh Part 1 (1..6) từ File 1
            if ((! $hasTextLayer || empty($parsedQuestions)) && ! empty($answerData['fallback_questions'])) {
                $parsedQuestions = $this->attachScanImagesToFallbackQuestions(
                    $answerData['fallback_questions'],
                    $extractedQuestionDoc
                );
            }

            // Nếu Admin để mặc định FULL_LR nhưng file PDF chỉ chứa toàn bộ câu Reading (101..200), tự chuyển scope sang READING
            if ($scope === 'FULL_LR' && ! empty($parsedQuestions)) {
                $qNumbers = array_map(static fn (array $q): int => (int) ($q['question_number'] ?? 0), $parsedQuestions);
                $minQ = min($qNumbers);
                $maxQ = max($qNumbers);
                if ($minQ >= 101 && $maxQ <= 200 && count($parsedQuestions) >= 30) {
                    $scope = 'READING';
                    $session->update(['import_scope' => 'READING']);
                }
            }

            if (! empty($answerData['fallback_questions'])) {
                $parsedQuestions = $this->mergeReadingFallbackOptionsFromAnswerScript(
                    $parsedQuestions,
                    $answerData['fallback_questions'],
                    $scope
                );
            }

            $parsedQuestions = $this->mergeFragmentedQuestionsBySeq($parsedQuestions);

            // 4 & 5. Merge theo question_number & Validate
            $mapped = $this->mapper->mapAndValidate($parsedQuestions, $answerData, $scope, $aPdfPath !== null);

            // 6. Lưu vào bảng Staging DB + staging.json để hiển thị trên Review UI
            DB::transaction(function () use ($session, $sessionId, $mapped): void {
                foreach ($mapped['questions'] as $q) {
                    PdfImportStagingQuestion::query()->create([
                        'import_session_id' => $sessionId,
                        'question_number' => $q['question_number'],
                        'part_code' => $q['part_code'],
                        'group_key' => $q['group_key'],
                        'group_title' => $q['group_title'],
                        'stimulus_text' => $q['stimulus_text'],
                        'content' => $q['content'],
                        'options_json' => $q['options'],
                        'correct_answer' => $q['correct_answer'],
                        'transcript' => $q['transcript'],
                        'explanation' => $q['explanation'],
                        'media_url' => $q['media_url'],
                        'validation_status' => $q['validation_status'],
                        'validation_issues_json' => $q['validation_issues'],
                    ]);
                }

                $session->update([
                    'status' => PdfImportSession::STATUS_READY_FOR_REVIEW,
                    'summary_json' => $mapped['summary'],
                    'validation_errors_json' => $mapped['validation_errors'],
                ]);
            });

            $pdfLines = [];
            foreach ($extractedQuestionDoc['pages'] ?? [] as $p) {
                foreach ($p['blocks'] ?? [] as $b) {
                    foreach (preg_split('/\R/u', (string) ($b['text'] ?? '')) ?: [] as $ln) {
                        $pdfLines[] = $ln;
                    }
                }
            }
            $partCodesInBatch = collect($mapped['sets'])->pluck('part')->unique()->values()->all();
            $partDirections = $this->stagingBridge->extractPartDirectionsFromLines($pdfLines, $partCodesInBatch);

            $this->stagingBridge->saveStagingPayload($sessionId, [
                'batch_id' => $sessionId,
                'import_session_id' => $sessionId,
                'source_type' => 'PDF',
                'file_name' => $aPdfName ? "{$qPdfName} + {$aPdfName}" : $qPdfName,
                'import_scope' => $scope,
                'target_test_id' => $targetTestId,
                'publish_status' => $publishStatus,
                'sets' => $mapped['sets'],
                'part_directions' => $partDirections,
                'summary' => $mapped['summary'],
                'validation_errors' => $mapped['validation_errors'],
                'created_at' => now()->toIso8601String(),
            ]);

            return [
                'import_session_id' => $sessionId,
                'batch_id' => $sessionId,
                'summary' => $mapped['summary'],
                'validation_errors' => $mapped['validation_errors'],
                'sets' => $mapped['sets'],
                'part_directions' => $partDirections,
            ];
        } catch (\Throwable $e) {
            $session->update([
                'status' => PdfImportSession::STATUS_FAILED,
                'validation_errors_json' => [$e->getMessage()],
            ]);

            throw $e;
        }
    }

    /**
     * Kiểm tra PDF Đề có text layer thực sự hay là file Scan (chỉ có ảnh).
     *
     * @param  array<string, mixed>  $extractedDoc
     */
    private function hasSubstantialTextLayer(array $extractedDoc): bool
    {
        $totalBlocks = 0;
        $totalChars = 0;

        foreach ($extractedDoc['pages'] ?? [] as $page) {
            foreach ($page['blocks'] ?? [] as $block) {
                $text = trim((string) ($block['text'] ?? ''));
                if ($text !== '') {
                    $totalBlocks++;
                    $totalChars += mb_strlen($text);
                }
            }
        }

        return $totalBlocks >= 1 && $totalChars >= 20;
    }

    /**
     * Kịch bản B (Scan Listening PDF): Gắn tuần tự 6 ảnh Part 1 (câu 1..6) từ File 1 vào khung câu hỏi từ File 2.
     *
     * @param  list<array<string, mixed>>  $fallbackQuestions
     * @param  array<string, mixed>  $extractedQuestionDoc
     * @return list<array<string, mixed>>
     */
    private function attachScanImagesToFallbackQuestions(array $fallbackQuestions, array $extractedQuestionDoc): array
    {
        $pages = $extractedQuestionDoc['pages'] ?? [];
        $pageCount = count($pages);
        $hasAnyText = false;
        foreach ($pages as $p) {
            if (! empty($p['blocks'])) {
                $hasAnyText = true;
                break;
            }
        }

        $candidates = [];
        foreach ($pages as $page) {
            if (! empty($page['is_directions_or_example'])) {
                continue;
            }
            $pageNo = (int) ($page['page_no'] ?? 1);
            // Với PDF Scan nguyên cuốn đề Listening (>= 10 trang, 0 text layer): trang 1 là bìa, trang 2 là Directions/Example
            if (! $hasAnyText && $pageCount >= 10 && $pageNo <= 2) {
                continue;
            }
            $pageText = '';
            foreach ($page['blocks'] ?? [] as $b) {
                $pageText .= ' '.(string) ($b['text'] ?? '');
            }
            $lowerText = mb_strtolower($pageText);
            if (
                str_contains($lowerText, 'directions')
                || str_contains($lowerText, 'example')
                || str_contains($lowerText, 'statement (c)')
            ) {
                continue;
            }

            foreach ($page['images'] ?? [] as $img) {
                $w = (int) ($img['width'] ?? 0);
                $h = (int) ($img['height'] ?? 0);
                $relPath = (string) ($img['relative_path'] ?? '');
                if ($relPath !== '' && $w >= 120 && $h >= 120) {
                    $candidates[] = [
                        'page_no' => $pageNo,
                        'y0' => (float) ($img['y0'] ?? 0),
                        'x0' => (float) ($img['x0'] ?? 0),
                        'path' => $relPath,
                    ];
                }
            }
        }

        usort($candidates, static function (array $a, array $b): int {
            return ($a['page_no'] <=> $b['page_no'])
                ?: ($a['y0'] <=> $b['y0'])
                ?: ($a['x0'] <=> $b['x0']);
        });

        $part1Window = array_slice($candidates, 0, 8);
        $part1Paths = array_column($part1Window, 'path');
        // Nếu vùng Part 1 có 7 hoặc 8 ảnh (do trang Directions/Example tạo ra 1-2 mảnh trước 6 ảnh thật) -> luôn giữ 6 ảnh cuối cùng cho Câu 1..6
        $contentImages = count($part1Paths) > 6
            ? array_slice($part1Paths, -6)
            : $part1Paths;

        $part1Idx = 0;
        foreach ($fallbackQuestions as $idx => $q) {
            $qNum = (int) ($q['question_number'] ?? 0);
            if ($qNum >= 1 && $qNum <= 6 && empty($q['media_url']) && isset($contentImages[$part1Idx])) {
                $fallbackQuestions[$idx]['media_url'] = $contentImages[$part1Idx];
                $part1Idx++;
            }
        }

        return $fallbackQuestions;
    }

    /**
     * Hợp nhất các mảnh câu hỏi bị tách do ngắt trang hoặc chia ô bảng có cùng question_number (seq).
     *
     * @param  list<array<string, mixed>>  $questions
     * @return list<array<string, mixed>>
     */
    public function mergeFragmentedQuestionsBySeq(array $questions): array
    {
        $bySeq = [];

        foreach ($questions as $q) {
            $seq = (int) ($q['question_number'] ?? 0);
            if ($seq <= 0) {
                continue;
            }

            if (! isset($bySeq[$seq])) {
                $bySeq[$seq] = $q;

                continue;
            }

            $base = $bySeq[$seq];
            if (trim((string) ($base['content'] ?? '')) === '' && trim((string) ($q['content'] ?? '')) !== '') {
                $base['content'] = $q['content'];
            }
            if (trim((string) ($base['stimulus_text'] ?? '')) === '' && trim((string) ($q['stimulus_text'] ?? '')) !== '') {
                $base['stimulus_text'] = $q['stimulus_text'];
            }
            if (empty($base['media_url']) && ! empty($q['media_url'])) {
                $base['media_url'] = $q['media_url'];
            }
            if (empty($base['correct_answer']) && ! empty($q['correct_answer'])) {
                $base['correct_answer'] = $q['correct_answer'];
            }
            if (trim((string) ($base['explanation'] ?? '')) === '' && trim((string) ($q['explanation'] ?? '')) !== '') {
                $base['explanation'] = $q['explanation'];
            }
            if (trim((string) ($base['transcript'] ?? '')) === '' && trim((string) ($q['transcript'] ?? '')) !== '') {
                $base['transcript'] = $q['transcript'];
            }

            $baseOpts = is_array($base['options'] ?? null) ? $base['options'] : [];
            $newOpts = is_array($q['options'] ?? null) ? $q['options'] : [];
            foreach ($newOpts as $lbl => $txt) {
                if (trim((string) ($baseOpts[$lbl] ?? '')) === '' && trim((string) $txt) !== '') {
                    $baseOpts[$lbl] = $txt;
                }
            }
            ksort($baseOpts);
            $base['options'] = $baseOpts;

            $bySeq[$seq] = $base;
        }

        ksort($bySeq);

        return array_values($bySeq);
    }

    /**
     * Bù phương án (A..D) hoặc câu hỏi Reading (101..200) bị khuyết từ file lời giải (RC 10)
     * khi file đề (de10) bị ngắt cột / ngắt trang.
     *
     * @param  list<array<string, mixed>>  $parsedQuestions
     * @param  list<array<string, mixed>>  $fallbackQuestions
     * @return list<array<string, mixed>>
     */
    private function mergeReadingFallbackOptionsFromAnswerScript(
        array $parsedQuestions,
        array $fallbackQuestions,
        string $scope
    ): array {
        $fallbackByNum = [];
        foreach ($fallbackQuestions as $fq) {
            $num = (int) ($fq['question_number'] ?? 0);
            if ($num >= 101 && $num <= 200) {
                $fallbackByNum[$num] = $fq;
            }
        }

        if (empty($fallbackByNum)) {
            return $parsedQuestions;
        }

        $hasReadingQuestions = $scope === 'READING';
        $presentReadingNums = [];
        foreach ($parsedQuestions as $idx => $q) {
            $num = (int) ($q['question_number'] ?? 0);
            if ($num < 101 || $num > 200) {
                continue;
            }
            $hasReadingQuestions = true;
            $presentReadingNums[$num] = true;

            if (! isset($fallbackByNum[$num])) {
                continue;
            }

            $fb = $fallbackByNum[$num];
            $opts = is_array($q['options'] ?? null) ? $q['options'] : [];
            $fbOpts = is_array($fb['options'] ?? null) ? $fb['options'] : [];
            foreach (['A', 'B', 'C', 'D'] as $letter) {
                if (trim((string) ($opts[$letter] ?? '')) === '' && trim((string) ($fbOpts[$letter] ?? '')) !== '') {
                    $opts[$letter] = $fbOpts[$letter];
                }
            }
            ksort($opts);
            $parsedQuestions[$idx]['options'] = $opts;

            if (trim((string) ($q['content'] ?? '')) === '' && trim((string) ($fb['content'] ?? '')) !== '') {
                $parsedQuestions[$idx]['content'] = $fb['content'];
            }
        }

        if ($hasReadingQuestions) {
            foreach ($fallbackByNum as $num => $fb) {
                if (! isset($presentReadingNums[$num])) {
                    $parsedQuestions[] = $fb;
                }
            }
        }

        return $parsedQuestions;
    }
}
