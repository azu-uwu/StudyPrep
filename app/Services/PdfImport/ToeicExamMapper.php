<?php

namespace App\Services\PdfImport;

use App\Models\PdfImportStagingQuestion;

/**
 * Mapper & Validator trung tâm cho đề thi TOEIC Listening & Reading:
 * - Hợp nhất câu hỏi (Question Booklet) + Đáp án/Transcript/Giải thích theo đúng question_number (1..200).
 * - Không bao giờ xảy ra lệch chỉ số (off-by-one) vì luôn join theo khóa số câu thực tế.
 * - Kiểm tra chặt chẽ định mức số câu từng Part (P1: 6, P2: 25, P3: 39, P4: 30, P5: 30, P6: 16, P7: 54).
 */
class ToeicExamMapper
{
    public const EXPECTED_PART_COUNTS = [
        'PART1' => 6,
        'PART2' => 25,
        'PART3' => 39,
        'PART4' => 30,
        'PART5' => 30,
        'PART6' => 16,
        'PART7' => 54,
    ];

    /**
     * @param  list<array<string, mixed>>  $parsedQuestions
     * @param  array{answers: array<int, string>, transcripts: array<int, string>, explanations: array<int, string>}  $answerData
     * @return array{
     *   questions: list<array<string, mixed>>,
     *   sets: list<array<string, mixed>>,
     *   summary: array<string, mixed>,
     *   validation_errors: list<string>
     * }
     */
    public function mapAndValidate(
        array $parsedQuestions,
        array $answerData,
        string $importScope = 'FULL_LR',
        bool $hasAnswerPdf = false
    ): array {
        $scope = strtoupper($importScope);
        $answers = $answerData['answers'] ?? [];
        $transcripts = $answerData['transcripts'] ?? [];
        $explanations = $answerData['explanations'] ?? [];

        $questionsByNum = [];
        foreach ($parsedQuestions as $q) {
            $qNum = (int) ($q['question_number'] ?? 0);
            if ($qNum < 1 || $qNum > 200) {
                continue;
            }
            // Chuẩn hóa Part theo dải số câu ETS chuẩn (tránh nhận diện nhầm Part)
            $q['part_code'] = $this->canonicalPartForQuestionNumber($qNum);
            $questionsByNum[$qNum] = $q;
        }
        ksort($questionsByNum);

        // Phát hiện đáp án bị thừa / lệch dải câu (ví dụ lệch off-by-one khiến đáp án trỏ vào câu không tồn tại)
        $validationErrors = [];
        $orphanAnswerNums = array_values(array_diff(array_keys($answers), array_keys($questionsByNum)));
        if (! empty($orphanAnswerNums)) {
            $sample = implode(', ', array_slice($orphanAnswerNums, 0, 10));
            $validationErrors[] = 'Cảnh báo lệch đáp án: tìm thấy '.count($orphanAnswerNums)." đáp án không khớp câu hỏi trong đề (câu: {$sample}).";
        }

        // Kiểm tra thiếu câu theo scope
        [$expectedMin, $expectedMax] = match ($scope) {
            'LISTENING' => [1, 100],
            'READING' => [101, 200],
            default => [1, 200],
        };
        $expectedNumbers = range($expectedMin, $expectedMax);
        $missingNumbers = array_values(array_diff($expectedNumbers, array_keys($questionsByNum)));
        if (! empty($missingNumbers)) {
            $sample = implode(', ', array_slice($missingNumbers, 0, 12));
            $more = count($missingNumbers) > 12 ? '...' : '';
            $validationErrors[] = 'Thiếu '.count($missingNumbers)." câu so với chuẩn {$scope} ({$expectedMin}-{$expectedMax}): {$sample}{$more}";
        }

        $partCounts = array_fill_keys(array_keys(self::EXPECTED_PART_COUNTS), 0);
        $validCount = 0;
        $needsReviewCount = 0;
        $errorCount = 0;
        $mappedQuestions = [];

        foreach ($questionsByNum as $qNum => $q) {
            $partCode = $q['part_code'];
            $partCounts[$partCode] = ($partCounts[$partCode] ?? 0) + 1;

            $correct = $answers[$qNum] ?? $q['correct_answer'] ?? null;
            if ($correct !== null) {
                $correct = strtoupper(trim((string) $correct));
            }
            $transcript = $transcripts[$qNum] ?? $q['transcript'] ?? null;
            $explanation = $explanations[$qNum] ?? $q['explanation'] ?? null;

            $issues = [];
            $options = is_array($q['options'] ?? null) ? $q['options'] : [];
            $expectedLetters = $partCode === 'PART2' ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'D'];

            // Kiểm tra nội dung câu hỏi
            $content = trim((string) ($q['content'] ?? ''));
            $hasPart6Stimulus = $partCode === 'PART6' && trim((string) ($q['stimulus_text'] ?? '')) !== '';
            if ($content === '' && $partCode !== 'PART1' && ! $hasPart6Stimulus) {
                $issues[] = 'Nội dung câu hỏi đang trống.';
            }

            // Kiểm tra ảnh Part 1
            if ($partCode === 'PART1' && empty($q['media_url'])) {
                $issues[] = 'Part 1 thiếu ảnh minh họa.';
            }

            // Kiểm tra đủ phương án A/B/C(/D)
            $filledLetters = 0;
            foreach ($expectedLetters as $letter) {
                if (isset($options[$letter]) && trim((string) $options[$letter]) !== '') {
                    $filledLetters++;
                }
            }
            if ($filledLetters < count($expectedLetters)) {
                $issues[] = $partCode === 'PART2'
                    ? 'Thiếu phương án trả lời (cần đủ A, B, C).'
                    : 'Thiếu phương án trả lời (cần đủ A, B, C, D).';
            }

            // Kiểm tra đáp án đúng
            $status = PdfImportStagingQuestion::VALIDATION_VALID;
            if ($correct === null || $correct === '') {
                if ($hasAnswerPdf) {
                    $issues[] = "Thiếu hoặc sai định dạng đáp án đúng trong file Đáp án (câu {$qNum}).";
                    $status = PdfImportStagingQuestion::VALIDATION_ERROR;
                } else {
                    $issues[] = 'Chưa có đáp án đúng (chế độ chỉ 1 file PDF đề) — cần Admin rà soát.';
                    $status = PdfImportStagingQuestion::VALIDATION_NEEDS_REVIEW;
                }
            } elseif (! in_array($correct, $expectedLetters, true)) {
                if ($partCode === 'PART2') {
                    $issues[] = "Đáp án gốc là {$correct} nhưng Part 2 chỉ có A, B, C — vui lòng chọn lại đáp án đúng.";
                } else {
                    $issues[] = "Đáp án gốc là {$correct} không hợp lệ — vui lòng chọn lại đáp án đúng.";
                }
                $status = PdfImportStagingQuestion::VALIDATION_ERROR;
            }

            if (! empty($issues) && $status === PdfImportStagingQuestion::VALIDATION_VALID) {
                $status = PdfImportStagingQuestion::VALIDATION_ERROR;
            }

            if ($status === PdfImportStagingQuestion::VALIDATION_VALID) {
                $validCount++;
            } elseif ($status === PdfImportStagingQuestion::VALIDATION_NEEDS_REVIEW) {
                $needsReviewCount++;
            } else {
                $errorCount++;
            }

            $mappedQuestions[] = [
                'question_number' => $qNum,
                'part_code' => $partCode,
                'group_key' => $q['group_key'] ?? null,
                'group_title' => $q['group_title'] ?? null,
                'stimulus_text' => $q['stimulus_text'] ?? null,
                'content' => $content,
                'options' => $options,
                'correct_answer' => $correct,
                'transcript' => $transcript !== null ? trim((string) $transcript) : null,
                'explanation' => $explanation !== null ? trim((string) $explanation) : null,
                'media_url' => $q['media_url'] ?? null,
                'validation_status' => $status,
                'validation_issues' => $issues,
            ];
        }

        $sets = $this->buildStagingSets($mappedQuestions);

        return [
            'questions' => $mappedQuestions,
            'sets' => $sets,
            'summary' => [
                'total_questions' => count($mappedQuestions),
                'expected_questions' => count($expectedNumbers),
                'valid_questions' => $validCount,
                'needs_review_questions' => $needsReviewCount,
                'error_questions' => $errorCount,
                'missing_count' => count($missingNumbers),
                'missing_numbers' => $missingNumbers,
                'missing_question_numbers' => $missingNumbers,
                'orphan_answer_numbers' => $orphanAnswerNums,
                'part_counts' => $partCounts,
                'image_count' => count(array_filter($mappedQuestions, static fn (array $q): bool => ! empty($q['media_url']))),
                'transcript_count' => count(array_filter($sets, static fn (array $s): bool => trim((string) ($s['transcript'] ?? '')) !== '')),
            ],
            'validation_errors' => $validationErrors,
        ];
    }

    public function canonicalPartForQuestionNumber(int $qNum): string
    {
        return match (true) {
            $qNum >= 1 && $qNum <= 6 => 'PART1',
            $qNum >= 7 && $qNum <= 31 => 'PART2',
            $qNum >= 32 && $qNum <= 70 => 'PART3',
            $qNum >= 71 && $qNum <= 100 => 'PART4',
            $qNum >= 101 && $qNum <= 130 => 'PART5',
            $qNum >= 131 && $qNum <= 146 => 'PART6',
            default => 'PART7',
        };
    }

    /**
     * Chuyển đổi danh sách câu đã map sang cấu trúc `sets` chuẩn của màn hình Staging Review.
     *
     * @param  list<array<string, mixed>>  $mappedQuestions
     * @return list<array<string, mixed>>
     */
    private function buildStagingSets(array $mappedQuestions): array
    {
        $sets = [];
        $groupIndexMap = [];

        foreach ($mappedQuestions as $q) {
            $partCode = $q['part_code'];
            $isGrouped = in_array($partCode, ['PART3', 'PART4', 'PART6', 'PART7'], true);
            $groupKey = $isGrouped && ! empty($q['group_key'])
                ? $partCode.'_'.$q['group_key']
                : 'single_'.$q['question_number'];

            if (! isset($groupIndexMap[$groupKey])) {
                $groupIndexMap[$groupKey] = count($sets);
                $sets[] = [
                    'set_key' => $groupKey,
                    'part' => $partCode,
                    'title' => $q['group_title'] ?? "Câu #{$q['question_number']} ({$partCode})",
                    'media_url' => $q['media_url'] ?? null,
                    'audio_url' => null,
                    'transcript' => $q['transcript'] ?? null,
                    'stimulus' => $q['stimulus_text'] ?? null,
                    'raw_source' => $this->buildRawSourcePreview($q),
                    'raw_images' => ! empty($q['media_url']) ? [$q['media_url']] : [],
                    'questions' => [],
                    'errors' => [],
                ];
            }

            $sIdx = $groupIndexMap[$groupKey];
            if (empty($sets[$sIdx]['transcript']) && ! empty($q['transcript'])) {
                $sets[$sIdx]['transcript'] = $q['transcript'];
            }
            if (empty($sets[$sIdx]['media_url']) && ! empty($q['media_url'])) {
                $sets[$sIdx]['media_url'] = $q['media_url'];
            }

            $qErrors = $q['validation_issues'] ?? [];
            $sets[$sIdx]['questions'][] = [
                'seq' => $q['question_number'],
                'content' => $q['content'],
                'options' => $q['options'],
                'correct' => $q['correct_answer'],
                'explanation' => $q['explanation'],
                'media_url' => $q['media_url'],
                'difficulty' => 'UNSET',
                'errors' => $qErrors,
            ];

            if (! empty($qErrors)) {
                $sets[$sIdx]['errors'] = array_values(array_unique(array_merge($sets[$sIdx]['errors'], $qErrors)));
            }
        }

        return $sets;
    }

    /**
     * @param  array<string, mixed>  $q
     */
    private function buildRawSourcePreview(array $q): string
    {
        $parts = [];
        if (! empty($q['stimulus_text'])) {
            $parts[] = (string) $q['stimulus_text'];
        }
        $parts[] = "Question {$q['question_number']}: ".(string) ($q['content'] ?? '');
        foreach (($q['options'] ?? []) as $lbl => $txt) {
            $parts[] = "({$lbl}) {$txt}";
        }
        if (! empty($q['correct_answer'])) {
            $parts[] = "Đáp án: {$q['correct_answer']}";
        }
        if (! empty($q['transcript'])) {
            $parts[] = "Transcript: {$q['transcript']}";
        }
        if (! empty($q['explanation'])) {
            $parts[] = "Giải thích: {$q['explanation']}";
        }

        return implode("\n", $parts);
    }
}
