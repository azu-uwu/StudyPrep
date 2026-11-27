<?php

namespace App\Services\PdfImport\Parsers;

/**
 * Parser cho file PDF Đáp án + Transcript + Giải thích (1..200).
 * Hỗ trợ nhiều định dạng phổ biến:
 * - Bảng Answer Key dạng lưới: "1. (B)  2. (A)  3. C" hoặc "101. B"
 * - Bảng Script & Key 3-4 cột (STT | Transcript/Câu hỏi | Đáp án | Dịch nghĩa tiếng Việt),
 *   kể cả trường hợp ô STT bị gộp/trống qua nhiều hàng hoặc tách qua trang mới (Part 3: 32-70, Part 4: 71-100).
 * - Khối văn bản thường & Transcript nhóm "Questions 32-34 refer to...".
 */
class PdfAnswerScriptParser
{
    /**
     * @param  array<string, mixed>  $extractedDocument
     * @return array{
     *   answers: array<int, string>,
     *   transcripts: array<int, string>,
     *   explanations: array<int, string>,
     *   fallback_questions: list<array<string, mixed>>
     * }
     */
    public function parse(array $extractedDocument): array
    {
        $answers = [];
        $transcripts = [];
        $explanations = [];
        $fallbackQuestions = [];

        $activeRangeStart = null;
        $activeRangeEnd = null;
        $activeQuestionNumber = null;
        $activeReadingSeq = null;
        $captureMode = null; // 'transcript' | 'explanation' | null

        // Giữ trạng thái nhóm Part 3/4 và câu Reading (101-200) xuyên suốt các bảng qua nhiều trang
        $tableState = [
            'range_start' => null,
            'range_end' => null,
            'group_key' => null,
            'group_title' => null,
            'last_q_num' => null,
            'active_reading_seq' => null,
        ];

        foreach ($extractedDocument['pages'] ?? [] as $page) {
            $blocks = is_array($page['blocks'] ?? null) ? $page['blocks'] : [];
            $tables = is_array($page['tables'] ?? null) ? $page['tables'] : [];

            foreach ($tables as $tbl) {
                $this->parseStructuredScriptTable(
                    $tbl,
                    $answers,
                    $transcripts,
                    $explanations,
                    $fallbackQuestions,
                    $tableState
                );
                $this->extractGridAnswers($this->stripWatermarks((string) ($tbl['text'] ?? '')), $answers);
            }

            foreach ($blocks as $block) {
                $cleanBlock = $this->stripWatermarks(str_replace("\f", "\n", (string) ($block['text'] ?? '')));
                $lines = preg_split('/\R/u', $cleanBlock) ?: [];

                foreach ($lines as $rawLine) {
                    $line = trim(preg_replace('/\s+/u', ' ', $rawLine) ?? '');
                    if ($line === '' || $this->isPageNoiseLine($line)) {
                        continue;
                    }

                    // 1. Dạng lưới nhiều đáp án trên 1 dòng: "1. B  2. A  3. C  4. D" hoặc "101 (A) 102 (C)"
                    if (preg_match_all('/(?:^|\s)(\d{1,3})\s*[\.\:\-\)]?\s*\(?([A-D])\)?(?=\s+\d{1,3}\b|\s*$)/u', $line, $gridMatches, PREG_SET_ORDER) && count($gridMatches) >= 2) {
                        foreach ($gridMatches as $gm) {
                            $qNum = (int) $gm[1];
                            if ($qNum >= 1 && $qNum <= 200) {
                                $answers[$qNum] = strtoupper($gm[2]);
                            }
                        }

                        continue;
                    }

                    // 2. Dòng tiêu đề nhóm Transcript: "Questions 32-34..."
                    if (preg_match('/^(?:Questions|Q)\s*(\d{1,3})\s*[\-\–\—]\s*(\d{1,3})\b(.*)$/iu', $line, $gm)) {
                        $start = (int) $gm[1];
                        $end = (int) $gm[2];
                        if ($start >= 1 && $end <= 200 && $end >= $start) {
                            $activeRangeStart = $start;
                            $activeRangeEnd = $end;
                            $activeQuestionNumber = null;
                            $captureMode = 'transcript';

                            continue;
                        }
                    }

                    // 3. Dòng bắt đầu câu đơn trong file đáp án/script: "1. (B) ..." hoặc "Question 101: ..."
                    if (preg_match('/^(?:Question\s+|Câu\s+|Q)?(\d{1,3})\s*[\.\)\:]\s*(.*)$/iu', $line, $qm)) {
                        $qNum = (int) $qm[1];
                        if ($qNum >= 1 && $qNum <= 200) {
                            $body = trim($qm[2]);
                            $activeQuestionNumber = $qNum;
                            if ($qNum >= 101 && $qNum <= 200) {
                                $activeReadingSeq = $qNum;
                            }
                            $activeRangeStart = null;
                            $activeRangeEnd = null;
                            $captureMode = 'explanation';

                            if (preg_match('/^\(?([A-D])\)?(?:\s+(.*))?$/u', $body, $am)) {
                                $answers[$qNum] = strtoupper($am[1]);
                                $rest = trim((string) ($am[2] ?? ''));
                                if ($rest !== '') {
                                    $explanations[$qNum] = $this->appendLine($explanations[$qNum] ?? '', $rest);
                                }
                            } elseif ($body !== '') {
                                if (preg_match('/^(?:Đáp\s*án|Answer|Key)\s*[:\-]\s*\(?([A-D])\)?(.*)$/iu', $body, $akm)) {
                                    $answers[$qNum] = strtoupper($akm[1]);
                                    $rest = trim($akm[2]);
                                    if ($rest !== '') {
                                        $explanations[$qNum] = $this->appendLine($explanations[$qNum] ?? '', $rest);
                                    }
                                } else {
                                    $explanations[$qNum] = $this->appendLine($explanations[$qNum] ?? '', $body);
                                }
                            }

                            continue;
                        }
                    }

                    // 4. Mốc chuyển sang Transcript / Audio Script
                    if (preg_match('/^(?:Transcript|Audio\s*Script|Script)\s*[:\-]?\s*(.*)$/iu', $line, $tm)) {
                        $captureMode = 'transcript';
                        $body = trim($tm[1]);
                        if ($body !== '') {
                            $this->appendTargetText($body, $activeQuestionNumber, $activeRangeStart, $activeRangeEnd, $transcripts);
                        }

                        continue;
                    }

                    // 5. Mốc chuyển sang Giải thích / Explanation
                    if (preg_match('/^(?:Giải\s*thích(?:\s*chi\s*tiết)?|Lời\s*giải|Explanation)\s*[:\-]?\s*(.*)$/iu', $line, $em)) {
                        $captureMode = 'explanation';
                        $body = trim($em[1]);
                        if ($body !== '') {
                            $this->appendTargetText($body, $activeQuestionNumber, $activeRangeStart, $activeRangeEnd, $explanations);
                        }

                        continue;
                    }

                    // 6. Dòng Đáp án riêng: "Đáp án: C" / "Answer: C" / "Chọn (B)" / "→ Chọn (A)" / chữ cái đứng riêng đầu dòng
                    $targetSeq = $activeQuestionNumber ?? $activeReadingSeq;
                    if ($targetSeq !== null && ($ansLetter = $this->extractAnswerLetterFromText($line)) !== null) {
                        $answers[$targetSeq] = $ansLetter;
                        if ($targetSeq >= 101) {
                            $explanations[$targetSeq] = $this->appendLine($explanations[$targetSeq] ?? '', $line);
                        }

                        continue;
                    }

                    // 7. Nối tiếp nội dung Transcript hoặc Explanation đang mở
                    if ($captureMode === 'transcript') {
                        $this->appendTargetText($line, $activeQuestionNumber, $activeRangeStart, $activeRangeEnd, $transcripts);
                    } elseif ($captureMode === 'explanation' && $activeQuestionNumber !== null) {
                        $explanations[$activeQuestionNumber] = $this->appendLine($explanations[$activeQuestionNumber] ?? '', $line);
                    }
                }
            }
        }

        ksort($answers);
        ksort($transcripts);
        ksort($explanations);
        ksort($fallbackQuestions);

        return [
            'answers' => $answers,
            'transcripts' => $transcripts,
            'explanations' => $explanations,
            'fallback_questions' => array_values($fallbackQuestions),
        ];
    }

    /**
     * Bóc tách bảng Script & Key dạng cột: STT | Transcript / Câu hỏi | Đáp án | Dịch nghĩa tiếng Việt.
     * Tự động nhận diện và gắn các bảng Graphic (2 cột: Day|Time|Activity, Location|Grams...) vào stimulus_text.
     *
     * @param  array<string, mixed>  $tbl
     * @param  array<int, string>  $answers
     * @param  array<int, string>  $transcripts
     * @param  array<int, string>  $explanations
     * @param  array<int, array<string, mixed>>  $fallbackQuestions
     * @param  array{range_start: ?int, range_end: ?int, group_key: ?string, group_title: ?string, last_q_num: ?int}  $tableState
     */
    private function parseStructuredScriptTable(
        array $tbl,
        array &$answers,
        array &$transcripts,
        array &$explanations,
        array &$fallbackQuestions,
        array &$tableState
    ): void {
        $rows = $tbl['rows'] ?? [];
        if (empty($rows)) {
            return;
        }

        // Nếu là bảng Graphic nhỏ (VD: "Day | Time | Activity", "Location | Grams per Ton", "Tenant | Floors")
        // nằm xen giữa cụm Part 3/4 -> gắn vào stimulus_text của cụm hiện tại, không làm ngắt cụm.
        $firstRowCells = array_map('trim', explode('|', $this->stripWatermarks((string) $rows[0])));
        $firstColNorm = (string) preg_replace('/\s+/u', '', $firstRowCells[0] ?? '');
        $looksLikeGraphicTable = count($firstRowCells) >= 2
            && count($firstRowCells) <= 3
            && $firstColNorm !== ''
            && preg_match('/^(?:\d{1,3}(?:[\-\–\—]\d{1,3})?|STT|No\.?|Question)$/iu', $firstColNorm) !== 1
            && count($rows) <= 10;

        if ($looksLikeGraphicTable && $tableState['range_start'] !== null && $tableState['range_end'] !== null) {
            $graphicText = "[Bảng biểu]\n".$this->stripWatermarks((string) ($tbl['text'] ?? implode("\n", $rows)));
            for ($q = $tableState['range_start']; $q <= $tableState['range_end']; $q++) {
                if (isset($fallbackQuestions[$q])) {
                    $fallbackQuestions[$q]['stimulus_text'] = $this->appendLine(
                        (string) ($fallbackQuestions[$q]['stimulus_text'] ?? ''),
                        $graphicText
                    );
                }
            }

            return;
        }

        foreach ($rows as $rowStr) {
            $cleanRow = $this->stripWatermarks((string) $rowStr);
            $cells = array_map('trim', explode('|', $cleanRow));
            if (count($cells) < 3) {
                continue;
            }

            $sttCell = (string) preg_replace('/\s+/u', '', $cells[0]);
            $mainCell = $cells[1] ?? '';
            $ansCell = $cells[2] ?? '';
            $vnCell = $cells[3] ?? '';

            if (preg_match('/^(?:STT|No\.?|Question)$/iu', $sttCell)) {
                continue;
            }

            // Nếu cột 1 trống do câu hỏi bị ngắt trang giữa chừng (Part 3/4 hoặc Reading 101-200)
            if ($sttCell === '') {
                if ($tableState['range_start'] !== null && $tableState['range_end'] !== null) {
                    $this->extractInlineAnswers($ansCell, $answers);
                    $this->processGroupCellContent(
                        $mainCell,
                        $vnCell,
                        $tableState['range_start'],
                        $tableState['range_end'],
                        (string) $tableState['group_key'],
                        (string) $tableState['group_title'],
                        $transcripts,
                        $explanations,
                        $fallbackQuestions,
                        $tableState['last_q_num']
                    );

                    continue;
                }

                $activeReadingSeq = $tableState['active_reading_seq'] ?? null;
                if ($activeReadingSeq !== null && $activeReadingSeq >= 101 && $activeReadingSeq <= 200) {
                    $ansLetter = $this->extractAnswerLetterFromText($ansCell) ?? $this->extractAnswerLetterFromText($mainCell);
                    if ($ansLetter !== null) {
                        $answers[$activeReadingSeq] = $ansLetter;
                    }
                    if ($ansCell !== '') {
                        $explanations[$activeReadingSeq] = $this->appendLine($explanations[$activeReadingSeq] ?? '', $ansCell);
                    }
                    if ($vnCell !== '') {
                        $explanations[$activeReadingSeq] = $this->appendLine($explanations[$activeReadingSeq] ?? '', $vnCell);
                    }
                    if ($mainCell !== '') {
                        $this->appendReadingOptionsToFallback($activeReadingSeq, $mainCell, $fallbackQuestions);
                    }

                    continue;
                }
            }

            // Nhóm câu Part 3/4: "32-34", "62-64", "98-100"
            if (preg_match('/^(\d{1,3})[\-\–\—](\d{1,3})$/', $sttCell, $rm)) {
                $rStart = (int) $rm[1];
                $rEnd = (int) $rm[2];
                $groupKey = "SCRIPT_{$rStart}_{$rEnd}";
                $groupTitle = "Questions {$rStart}-{$rEnd}";

                $tableState['range_start'] = $rStart;
                $tableState['range_end'] = $rEnd;
                $tableState['group_key'] = $groupKey;
                $tableState['group_title'] = $groupTitle;

                $this->extractInlineAnswers($ansCell, $answers);
                $this->processGroupCellContent(
                    $mainCell,
                    $vnCell,
                    $rStart,
                    $rEnd,
                    $groupKey,
                    $groupTitle,
                    $transcripts,
                    $explanations,
                    $fallbackQuestions,
                    $tableState['last_q_num']
                );

                continue;
            }

            // Câu đơn Part 1 (1-6), Part 2 (7-31) hoặc ô STT chỉ ghi số đầu cụm (32..100)
            if (preg_match('/^(\d{1,3})$/', $sttCell, $sm)) {
                $qNum = (int) $sm[1];
                if ($qNum < 1 || $qNum > 200) {
                    continue;
                }

                if ($qNum >= 32 && $qNum <= 100) {
                    $tableState['range_start'] = $qNum;
                    $tableState['range_end'] = min(100, $qNum + 2);
                    $tableState['group_key'] = "SCRIPT_{$tableState['range_start']}_{$tableState['range_end']}";
                    $tableState['group_title'] = "Questions {$tableState['range_start']}-{$tableState['range_end']}";
                    $tableState['active_reading_seq'] = null;
                } elseif ($qNum >= 101 && $qNum <= 200) {
                    $tableState['range_start'] = null;
                    $tableState['range_end'] = null;
                    $tableState['group_key'] = null;
                    $tableState['group_title'] = null;
                    $tableState['active_reading_seq'] = $qNum;
                } else {
                    $tableState['range_start'] = null;
                    $tableState['range_end'] = null;
                    $tableState['group_key'] = null;
                    $tableState['group_title'] = null;
                    $tableState['active_reading_seq'] = null;
                }

                $ansLetter = $this->extractAnswerLetterFromText($ansCell);
                if ($ansLetter !== null) {
                    $answers[$qNum] = $ansLetter;
                }

                if ($qNum >= 101 && $qNum <= 200) {
                    if ($ansCell !== '') {
                        $explanations[$qNum] = $this->appendLine($explanations[$qNum] ?? '', $ansCell);
                    }
                    if ($vnCell !== '') {
                        $explanations[$qNum] = $this->appendLine($explanations[$qNum] ?? '', $vnCell);
                    }
                    $this->extractSingleQuestionFromCell($qNum, $mainCell, $fallbackQuestions);
                    $tableState['last_q_num'] = $qNum;
                } else {
                    $transcripts[$qNum] = $this->appendLine($transcripts[$qNum] ?? '', $mainCell);
                    if ($vnCell !== '') {
                        $explanations[$qNum] = $this->appendLine($explanations[$qNum] ?? '', $vnCell);
                    }

                    if ($qNum <= 31) {
                        $this->extractSingleQuestionFromCell($qNum, $mainCell, $fallbackQuestions);
                        $tableState['last_q_num'] = $qNum;
                    } else {
                        $this->processGroupCellContent(
                            $mainCell,
                            $vnCell,
                            (int) $tableState['range_start'],
                            (int) $tableState['range_end'],
                            (string) $tableState['group_key'],
                            (string) $tableState['group_title'],
                            $transcripts,
                            $explanations,
                            $fallbackQuestions,
                            $tableState['last_q_num']
                        );
                    }
                }
            }
        }
    }

    /**
     * @param  array<int, string>  $transcripts
     * @param  array<int, string>  $explanations
     * @param  array<int, array<string, mixed>>  $fallbackQuestions
     */
    private function processGroupCellContent(
        string $mainCell,
        string $vnCell,
        int $rStart,
        int $rEnd,
        string $groupKey,
        string $groupTitle,
        array &$transcripts,
        array &$explanations,
        array &$fallbackQuestions,
        ?int &$lastQNum
    ): void {
        if ($mainCell === '') {
            return;
        }

        // Kiểm tra xem cột 2 có chứa câu hỏi hoặc options bị tách trang của nhóm [rStart..rEnd] hay không
        $hasQuestionInRange = false;
        for ($q = $rStart; $q <= $rEnd; $q++) {
            if (preg_match('/(?:^|\s)'.$q.'\s*\.\s+[A-Z]/u', $mainCell) === 1) {
                $hasQuestionInRange = true;
                break;
            }
        }
        $hasLeadingOptions = preg_match('/^\s*(?:\([A-D]\)|[A-D][\.\)])/u', $mainCell) === 1;

        if ($hasQuestionInRange || $hasLeadingOptions) {
            $this->extractQuestionsFromBlock($mainCell, $rStart, $rEnd, $groupKey, $groupTitle, $fallbackQuestions, $lastQNum);
            if ($vnCell !== '') {
                for ($q = $rStart; $q <= $rEnd; $q++) {
                    $explanations[$q] = $this->appendLine($explanations[$q] ?? '', $vnCell);
                }
            }
        } else {
            for ($q = $rStart; $q <= $rEnd; $q++) {
                $transcripts[$q] = $this->appendLine($transcripts[$q] ?? '', $mainCell);
                if ($vnCell !== '') {
                    $explanations[$q] = $this->appendLine($explanations[$q] ?? '', $vnCell);
                }
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $fallbackQuestions
     */
    private function extractSingleQuestionFromCell(int $qNum, string $cellText, array &$fallbackQuestions): void
    {
        $partCode = match (true) {
            $qNum <= 6 => 'PART1',
            $qNum <= 31 => 'PART2',
            $qNum <= 70 => 'PART3',
            $qNum <= 100 => 'PART4',
            $qNum <= 130 => 'PART5',
            $qNum <= 146 => 'PART6',
            default => 'PART7',
        };
        $allowed = $partCode === 'PART2' ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'D'];
        $options = [];

        if (preg_match_all('/(?:^|\s)(?:\(([A-D])\)|([A-D])[\.\)])\s*(.+?)(?=(?:\s+(?:\([A-D]\)|[A-D][\.\)]))|$)/u', $cellText, $om, PREG_SET_ORDER)) {
            foreach ($om as $m) {
                $lbl = strtoupper($m[1] !== '' ? $m[1] : $m[2]);
                if (in_array($lbl, $allowed, true)) {
                    $options[$lbl] = trim($m[3]);
                }
            }
        }

        $stem = '';
        if ($partCode !== 'PART1') {
            if (preg_match('/(?:^|\s)(?:\(A\)|A[\.\)])\s*/u', $cellText, $posMatch, PREG_OFFSET_CAPTURE) === 1) {
                $stem = trim(substr($cellText, 0, (int) $posMatch[0][1]));
            } else {
                $stem = trim($cellText);
            }
        }

        $fallbackQuestions[$qNum] = [
            'question_number' => $qNum,
            'part_code' => $partCode,
            'group_key' => null,
            'group_title' => null,
            'stimulus_text' => null,
            'content' => $stem,
            'options' => $options,
            'correct_answer' => null,
            'transcript' => null,
            'explanation' => null,
            'media_url' => null,
        ];
    }

    /**
     * Bóc tách đủ 3 câu (rStart..rEnd) trong Part 3/4, hỗ trợ trường hợp câu bị ngắt trang giữa chừng.
     *
     * @param  array<int, array<string, mixed>>  $fallbackQuestions
     */
    private function extractQuestionsFromBlock(
        string $text,
        int $rStart,
        int $rEnd,
        string $groupKey,
        string $groupTitle,
        array &$fallbackQuestions,
        ?int &$lastQNum = null
    ): void {
        $partCode = match (true) {
            $rStart <= 70 => 'PART3',
            $rStart <= 100 => 'PART4',
            $rStart <= 146 => 'PART6',
            default => 'PART7',
        };

        // Nếu đầu ô có các options bị ngắt sang trang từ câu trước (VD: "(D) Near the overflow..." của câu 69)
        if ($lastQNum !== null && isset($fallbackQuestions[$lastQNum])) {
            $firstQPos = null;
            if (preg_match('/(?:^|\s)('.implode('|', range($rStart, $rEnd)).')\s*\.\s+/u', $text, $fm, PREG_OFFSET_CAPTURE) === 1) {
                $firstQPos = (int) $fm[0][1];
            }
            $prefix = $firstQPos !== null ? trim(substr($text, 0, $firstQPos)) : trim($text);
            if ($prefix !== '' && preg_match_all('/(?:^|\s)(?:\(([A-D])\)|([A-D])[\.\)])\s*(.+?)(?=(?:\s+(?:\([A-D]\)|[A-D][\.\)]))|$)/u', $prefix, $leadOpts, PREG_SET_ORDER)) {
                foreach ($leadOpts as $om) {
                    $lbl = strtoupper($om[1] !== '' ? $om[1] : $om[2]);
                    if (empty($fallbackQuestions[$lastQNum]['options'][$lbl])) {
                        $fallbackQuestions[$lastQNum]['options'][$lbl] = trim($om[3]);
                    }
                }
            }
        }

        $rangeNums = range($rStart, $rEnd);
        for ($i = 0; $i < count($rangeNums); $i++) {
            $q = $rangeNums[$i];
            $laterNums = array_slice($rangeNums, $i + 1);
            $lookahead = ! empty($laterNums)
                ? '(?=\s+(?:'.implode('|', $laterNums).')\s*\.\s+[A-Z]|$)'
                : '$';

            $pattern = '/(?:^|\s)'.$q.'\s*\.\s*(.+?)'.$lookahead.'/us';
            if (preg_match($pattern, $text, $qm) !== 1) {
                continue;
            }

            $qBody = trim($qm[1]);
            $stem = $qBody;
            if (preg_match('/(?:^|\s)(?:\(A\)|A[\.\)])\s*/u', $qBody, $posMatch, PREG_OFFSET_CAPTURE) === 1) {
                $stem = trim(substr($qBody, 0, (int) $posMatch[0][1]));
            }
            $options = [];
            if (preg_match_all('/(?:^|\s)(?:\(([A-D])\)|([A-D])[\.\)])\s*(.+?)(?=(?:\s+(?:\([A-D]\)|[A-D][\.\)]))|$)/u', $qBody, $om, PREG_SET_ORDER)) {
                foreach ($om as $m) {
                    $lbl = strtoupper($m[1] !== '' ? $m[1] : $m[2]);
                    $options[$lbl] = trim($m[3]);
                }
            }

            $existing = $fallbackQuestions[$q] ?? null;
            $fallbackQuestions[$q] = [
                'question_number' => $q,
                'part_code' => $partCode,
                'group_key' => $groupKey,
                'group_title' => $groupTitle,
                'stimulus_text' => $existing['stimulus_text'] ?? null,
                'content' => $stem !== '' ? $stem : ($existing['content'] ?? ''),
                'options' => array_merge($existing['options'] ?? [], $options),
                'correct_answer' => null,
                'transcript' => null,
                'explanation' => null,
                'media_url' => null,
            ];
            $lastQNum = $q;
        }
    }

    private function extractAnswerLetterFromText(string $text): ?string
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/(?:→|➡)?\s*(?:Chọn(?:\s*đáp\s*án)?|Đáp\s*án(?:\s*đúng)?|Answer|Key|Correct)\s*[:\-]?\s*\(?([A-D])\)?/iu', $trimmed, $m)) {
            return strtoupper($m[1]);
        }

        if (preg_match('/^\s*\(?([A-D])\)?(?:\s+.*)?$/u', $trimmed, $m)) {
            return strtoupper($m[1]);
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $fallbackQuestions
     */
    private function appendReadingOptionsToFallback(int $qNum, string $cellText, array &$fallbackQuestions): void
    {
        if (! preg_match_all('/(?:^|\s)(?:\(([A-D])\)|([A-D])[\.\)])\s*(.+?)(?=(?:\s+(?:\([A-D]\)|[A-D][\.\)]))|$)/u', $cellText, $om, PREG_SET_ORDER)) {
            return;
        }

        if (! isset($fallbackQuestions[$qNum])) {
            $this->extractSingleQuestionFromCell($qNum, $cellText, $fallbackQuestions);

            return;
        }

        foreach ($om as $m) {
            $lbl = strtoupper($m[1] !== '' ? $m[1] : $m[2]);
            if (empty($fallbackQuestions[$qNum]['options'][$lbl])) {
                $fallbackQuestions[$qNum]['options'][$lbl] = trim($m[3]);
            }
        }
    }

    private function isPageNoiseLine(string $line): bool
    {
        return preg_match('/^(?:(?:Page\s*)?\d{1,3}(?:\s*\/\s*\d{1,3})?|ACTUAL\s+TEST\s+\d+.*|ETS\s+TOEIC.*|GO\s+ON\s+TO\s+THE\s+NEXT\s+PAGE.*)$/iu', $line) === 1;
    }

    /**
     * Làm sạch chuỗi watermark lặp lại khỏi text PDF.
     */
    private function stripWatermarks(string $text): string
    {
        $cleaned = preg_replace('/Hien\s*Nhung\s*TOEIC/iu', '', $text) ?? $text;
        $cleaned = preg_replace('/Teach\s*By\s*Heart/iu', '', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/\b(?:Hien|Nhung|Nh|Teach|By|Heart)\b(?=\s+(?:Hien|Nhung|Nh|Teach|By|Heart)\b)/iu', '', $cleaned) ?? $cleaned;

        return trim((string) preg_replace('/[ \t]{2,}/u', ' ', $cleaned));
    }

    /**
     * @param  array<int, string>  $answers
     */
    private function extractInlineAnswers(string $ansCell, array &$answers): void
    {
        if (preg_match_all('/\b(\d{1,3})\s*[\.\:\-\)]\s*\(?([A-D])\)?\b/u', $ansCell, $am, PREG_SET_ORDER)) {
            foreach ($am as $m) {
                $qn = (int) $m[1];
                if ($qn >= 1 && $qn <= 200) {
                    $answers[$qn] = strtoupper($m[2]);
                }
            }
        }
    }

    /**
     * @param  array<int, string>  $answers
     */
    private function extractGridAnswers(string $text, array &$answers): void
    {
        if (preg_match_all('/\b(\d{1,3})\s*[\.\:\-\|\s]\s*\(?([A-D])\)?\b/u', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $qNum = (int) $m[1];
                if ($qNum >= 1 && $qNum <= 200) {
                    $answers[$qNum] = strtoupper($m[2]);
                }
            }
        }
    }

    /**
     * @param  array<int, string>  $bucket
     */
    private function appendTargetText(string $text, ?int $activeQ, ?int $rangeStart, ?int $rangeEnd, array &$bucket): void
    {
        if ($rangeStart !== null && $rangeEnd !== null) {
            for ($q = $rangeStart; $q <= $rangeEnd; $q++) {
                $bucket[$q] = $this->appendLine($bucket[$q] ?? '', $text);
            }

            return;
        }

        if ($activeQ !== null) {
            $bucket[$activeQ] = $this->appendLine($bucket[$activeQ] ?? '', $text);
        }
    }

    private function appendLine(string $existing, string $line): string
    {
        $existingTrim = trim($existing);
        $lineTrim = trim($line);
        if ($existingTrim === '') {
            return $lineTrim;
        }
        if ($lineTrim === '') {
            return $existingTrim;
        }

        return $existingTrim."\n".$lineTrim;
    }
}
