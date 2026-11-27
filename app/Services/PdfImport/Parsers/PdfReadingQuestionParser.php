<?php

namespace App\Services\PdfImport\Parsers;

use App\Services\PdfImport\Contracts\ToeicPdfSectionParserInterface;

/**
 * Strategy phân tích phần TOEIC Reading (Question 101 - 200) từ layout PDF:
 * - Xử lý chuẩn layout 2 cột (nhờ thứ tự block cột trái x0 < 0.48*W trước, cột phải sau).
 * - Part 5 (101-130): Hỗ trợ dấu ------- / _____ / ---, thân câu nhiều dòng, options 1/2/4 dòng & ngắt dòng.
 * - Part 6 (131-146): Tự điền "Blank X" nếu câu chỉ có số thứ tự + 4 options, gom toàn bộ đoạn văn vào stimulus_text.
 * - Part 7 (147-200): Gom đoạn văn, email (To/From/Date/Subject), bài báo và bảng biểu pdfplumber vào stimulus_text.
 */
class PdfReadingQuestionParser implements ToeicPdfSectionParserInterface
{
    public function supportsScope(string $importScope): bool
    {
        return in_array(strtoupper($importScope), ['FULL_LR', 'READING'], true);
    }

    public function parse(array $extractedDocument): array
    {
        $questions = [];
        $currentGroupKey = null;
        $currentGroupTitle = null;
        $currentGroupRangeStart = null;
        $currentGroupRangeEnd = null;
        $currentGroupStimulus = null;
        $currentGroupMedia = null;
        $capturingStimulus = false;
        $activeQuestionNumber = null;
        $activeOptionLetter = null;
        $lastQuestionNumber = 100;

        foreach ($extractedDocument['pages'] ?? [] as $page) {
            $pageNo = (int) ($page['page_no'] ?? 1);
            $pageWidth = (float) ($page['width'] ?? 595.0);
            $blocks = is_array($page['blocks'] ?? null) ? $page['blocks'] : [];
            $tables = is_array($page['tables'] ?? null) ? $page['tables'] : [];
            $images = is_array($page['images'] ?? null) ? $page['images'] : [];
            $usedImages = [];

            $timelineItems = $this->buildOrderedTimeline($blocks, $tables, $pageWidth, ! empty($page['is_two_column']));

            foreach ($timelineItems as $item) {
                if ($item['kind'] === 'table') {
                    if ($currentGroupKey !== null && trim($item['text']) !== '') {
                        $currentGroupStimulus = $this->appendText($currentGroupStimulus, "[Bảng biểu]\n".$item['text']);
                        $this->syncGroupStimulus($questions, $currentGroupKey, $currentGroupStimulus);
                    }

                    continue;
                }

                $lines = preg_split('/\R/u', str_replace("\f", "\n", $item['text'])) ?: [];
                $blockY = $item['y0'];

                foreach ($lines as $rawLine) {
                    $line = trim(preg_replace('/\s+/u', ' ', $rawLine) ?? '');
                    if ($line === '' || $this->isBoilerplateLine($line)) {
                        continue;
                    }

                    // 1. Tiêu đề Part 5 / 6 / 7
                    if (preg_match('/^PART\s*([567])\b/iu', $line, $pm)) {
                        if ((int) $pm[1] === 5) {
                            $currentGroupKey = null;
                            $currentGroupTitle = null;
                            $currentGroupRangeStart = null;
                            $currentGroupRangeEnd = null;
                            $currentGroupStimulus = null;
                            $capturingStimulus = false;
                            $activeOptionLetter = null;
                        }

                        continue;
                    }

                    // 2. Tiêu đề nhóm Part 6 / Part 7 (hỗ trợ -, –, —, through, to và lỗi OCR l/I thay số 1)
                    $normalizedHeaderLine = $this->normalizeOcrNumbersInHeader($line);
                    if (preg_match('/^(?:Questions?|Q\.?)\s*(\d{3})\s*(?:[\-\–\—]|through|to)\s*(\d{3})\b(.*)$/iu', $normalizedHeaderLine, $gm)) {
                        $start = (int) $gm[1];
                        $end = (int) $gm[2];
                        if ($start >= 131 && $end <= 200 && $end >= $start) {
                            $isSameActiveGroup = $currentGroupRangeStart === $start && $currentGroupRangeEnd === $end && $currentGroupKey !== null;
                            if (! $isSameActiveGroup) {
                                $currentGroupRangeStart = $start;
                                $currentGroupRangeEnd = $end;
                                $currentGroupKey = "R_P{$pageNo}_{$start}_{$end}";
                                $currentGroupTitle = $line;
                                $currentGroupStimulus = null;
                                $currentGroupMedia = $this->findNearestImage($images, $usedImages, $blockY, 240.0);
                            }
                            $capturingStimulus = true;
                            $activeQuestionNumber = null;
                            $activeOptionLetter = null;

                            continue;
                        }
                    }

                    // 3. Dòng bắt đầu câu hỏi Reading (101..200)
                    if (preg_match('/^(?:(Question|Q)\s*)?(\d{3})\s*[\.\)\:](?:\s+(.*)|(?=\([A-D]\))(.*)|$)$/iu', $line, $qm)) {
                        $hasExplicitPrefix = trim((string) $qm[1]) !== '';
                        $qNum = (int) $qm[2];
                        $stem = trim((string) (($qm[3] ?? '') !== '' ? $qm[3] : ($qm[4] ?? '')));

                        // Tránh nhận nhầm số nhà/năm/giá tiền (VD: "120. Main Street") bên trong đoạn văn Part 6/7 thành câu hỏi
                        $isInsideActiveGroup = $currentGroupRangeStart !== null && $currentGroupRangeEnd !== null
                            && $qNum >= $currentGroupRangeStart && $qNum <= $currentGroupRangeEnd;
                        $looksLikePart6Blank = $qNum >= 131 && $qNum <= 146 && ($stem === '' || preg_match('/^(?:\([A-D]\)|[A-D][\.\)])/u', $stem) === 1);
                        $looksLikeSequentialPart7Q = $qNum >= 147 && $qNum <= 200
                            && $qNum > $lastQuestionNumber
                            && $qNum <= $lastQuestionNumber + 8
                            && (str_contains($stem, '?') || preg_match('/^(?:What|Which|Who|Where|When|Why|How|According|In|The|At|To|For|On|By|From|Why|\([A-D]\))/i', $stem) === 1);

                        $isAllowedReadingQuestion = $qNum >= 101 && $qNum <= 200 && (
                            $hasExplicitPrefix
                            || ! $capturingStimulus
                            || $isInsideActiveGroup
                            || $looksLikePart6Blank
                            || $looksLikeSequentialPart7Q
                        );

                        if ($isAllowedReadingQuestion) {
                            $partCode = $this->resolveReadingPart($qNum);
                            $capturingStimulus = false;
                            $activeOptionLetter = null;
                            $lastQuestionNumber = max($lastQuestionNumber, $qNum);

                            if ($partCode === 'PART5') {
                                $currentGroupKey = null;
                                $currentGroupTitle = null;
                                $currentGroupRangeStart = null;
                                $currentGroupRangeEnd = null;
                                $currentGroupStimulus = null;
                                $currentGroupMedia = null;
                            } elseif ($partCode === 'PART6' && ($currentGroupKey === null || $currentGroupRangeStart === null || $qNum < $currentGroupRangeStart || $qNum > $currentGroupRangeEnd)) {
                                $blockIdx = intdiv($qNum - 131, 4);
                                $gStart = 131 + ($blockIdx * 4);
                                $gEnd = min(146, $gStart + 3);
                                $currentGroupRangeStart = $gStart;
                                $currentGroupRangeEnd = $gEnd;
                                $currentGroupKey = "R_P6_{$gStart}_{$gEnd}";
                                $currentGroupTitle = "Questions {$gStart}-{$gEnd}";
                            } elseif ($currentGroupRangeStart !== null && ($qNum < $currentGroupRangeStart || $qNum > $currentGroupRangeEnd)) {
                                $currentGroupKey = null;
                                $currentGroupTitle = null;
                                $currentGroupStimulus = null;
                                $currentGroupMedia = null;
                            }

                            // Nếu options dính ngay cùng dòng với số câu (VD: "131. (A) foo (B) bar")
                            $inlineOptsTail = null;
                            if (preg_match('/^(.*?)(\([A-D]\)\s*.+)$/u', $stem, $splitM)) {
                                $stem = trim($splitM[1]);
                                $inlineOptsTail = trim($splitM[2]);
                            }

                            if ($partCode === 'PART6' && $stem === '') {
                                $stem = "Blank {$qNum}";
                            }

                            $questions[$qNum] = [
                                'question_number' => $qNum,
                                'part_code' => $partCode,
                                'group_key' => $currentGroupKey,
                                'group_title' => $currentGroupTitle,
                                'stimulus_text' => $currentGroupStimulus,
                                'content' => $stem,
                                'options' => [],
                                'correct_answer' => null,
                                'transcript' => null,
                                'explanation' => null,
                                'media_url' => $currentGroupMedia,
                            ];
                            $activeQuestionNumber = $qNum;

                            if ($inlineOptsTail !== null && $inlineOptsTail !== '') {
                                $this->parseOptionsFromLine($inlineOptsTail, $questions[$qNum], $activeOptionLetter);
                            }

                            continue;
                        }
                    }

                    // 4. Đang thu thập đoạn văn / email / bảng biểu cho Part 6 / Part 7
                    if ($capturingStimulus && $currentGroupKey !== null) {
                        $currentGroupStimulus = $this->appendText($currentGroupStimulus, $line);

                        continue;
                    }

                    // 5. Nhận diện 1 hoặc nhiều options trên dòng: (A)...(D)... hoặc A. ... D. ...
                    if ($activeQuestionNumber !== null && $this->parseOptionsFromLine($line, $questions[$activeQuestionNumber], $activeOptionLetter)) {
                        continue;
                    }

                    // 6. Nối tiếp nội dung option bị ngắt xuống dòng (tránh mất chữ ở C/D)
                    if ($activeQuestionNumber !== null && $activeOptionLetter !== null) {
                        $existingOpt = (string) ($questions[$activeQuestionNumber]['options'][$activeOptionLetter] ?? '');
                        $questions[$activeQuestionNumber]['options'][$activeOptionLetter] = trim($existingOpt.' '.$line);

                        continue;
                    }

                    // 7. Nối tiếp thân câu hỏi nhiều dòng (chưa vào options)
                    if ($activeQuestionNumber !== null && empty($questions[$activeQuestionNumber]['options'])) {
                        $existing = (string) $questions[$activeQuestionNumber]['content'];
                        $questions[$activeQuestionNumber]['content'] = $existing === '' ? $line : ($existing.' '.$line);
                    }
                }
            }
        }

        ksort($questions);

        return array_values($questions);
    }

    /**
     * Sắp xếp timeline blocks + tables theo cột trái (x0 < 0.48*W) trước, cột phải sau nếu là trang 2 cột.
     *
     * @param  list<array<string, mixed>>  $blocks
     * @param  list<array<string, mixed>>  $tables
     * @return list<array<string, mixed>>
     */
    private function buildOrderedTimeline(array $blocks, array $tables, float $pageWidth, bool $isTwoColumn): array
    {
        $splitX = $pageWidth * 0.48;
        $items = [];

        foreach ($blocks as $b) {
            $x0 = (float) ($b['x0'] ?? 0);
            $x1 = (float) ($b['x1'] ?? 0);
            $col = (string) ($b['column'] ?? '');
            if ($col === '') {
                if (! $isTwoColumn) {
                    $col = 'single';
                } elseif ($x0 < $pageWidth * 0.42 && $x1 > $pageWidth * 0.58) {
                    $col = 'full';
                } else {
                    $col = $x0 < $splitX ? 'left' : 'right';
                }
            }

            $items[] = [
                'kind' => 'block',
                'column' => $col,
                'y0' => (float) ($b['y0'] ?? 0),
                'x0' => $x0,
                'text' => (string) ($b['text'] ?? ''),
            ];
        }

        foreach ($tables as $t) {
            $x0 = (float) ($t['x0'] ?? 0);
            $x1 = (float) ($t['x1'] ?? 0);
            $col = ! $isTwoColumn ? 'single' : (($x0 < $pageWidth * 0.42 && $x1 > $pageWidth * 0.58) ? 'full' : ($x0 < $splitX ? 'left' : 'right'));
            $items[] = [
                'kind' => 'table',
                'column' => $col,
                'y0' => (float) ($t['y0'] ?? 0),
                'x0' => $x0,
                'text' => (string) ($t['text'] ?? ''),
            ];
        }

        if (! $isTwoColumn) {
            usort($items, static fn (array $a, array $b): int => ($a['y0'] <=> $b['y0']) ?: ($a['x0'] <=> $b['x0']));

            return $items;
        }

        $colWeight = ['full' => 0, 'left' => 1, 'right' => 2, 'single' => 1];
        usort($items, static function (array $a, array $b) use ($colWeight): int {
            $wa = $colWeight[$a['column']] ?? 1;
            $wb = $colWeight[$b['column']] ?? 1;
            if ($wa !== $wb) {
                return $wa <=> $wb;
            }

            return ($a['y0'] <=> $b['y0']) ?: ($a['x0'] <=> $b['x0']);
        });

        return $items;
    }

    /**
     * Bóc 1 hoặc nhiều options từ một dòng: hỗ trợ (A)...(D)... và A. ... D. ...
     *
     * @param  array<string, mixed>  $question
     */
    private function parseOptionsFromLine(string $line, array &$question, ?string &$activeOptionLetter): bool
    {
        // Nhiều options trên cùng 1 dòng: "(A) foo (B) bar" hoặc "A. foo B. bar"
        if (preg_match_all('/(?:^|\s)(?:\(([A-D])\)|([A-D])[\.\)])\s*(.+?)(?=(?:\s+(?:\([A-D]\)|[A-D][\.\)]))|$)/u', $line, $multiOpts, PREG_SET_ORDER) && count($multiOpts) >= 2) {
            foreach ($multiOpts as $om) {
                $letter = strtoupper($om[1] !== '' ? $om[1] : $om[2]);
                $question['options'][$letter] = trim($om[3]);
                $activeOptionLetter = $letter;
            }

            return true;
        }

        // 1 option bắt đầu dòng: "(A) foo" hoặc "A. foo" hoặc "A) foo"
        if (preg_match('/^\s*(?:\(([A-D])\)|([A-D])[\.\)])\s*(.+)$/u', $line, $singleOpt)) {
            $letter = strtoupper($singleOpt[1] !== '' ? $singleOpt[1] : $singleOpt[2]);
            $question['options'][$letter] = trim($singleOpt[3]);
            $activeOptionLetter = $letter;

            return true;
        }

        // Trường hợp phần đầu dòng là đoạn ngắt của option trước, theo sau bởi "(D) ..."
        if ($activeOptionLetter !== null && preg_match('/^(.+?)\s+\(([A-D])\)\s+(.+)$/u', $line, $midOpt)) {
            $prevText = (string) ($question['options'][$activeOptionLetter] ?? '');
            $question['options'][$activeOptionLetter] = trim($prevText.' '.trim($midOpt[1]));
            $nextLetter = strtoupper($midOpt[2]);
            $question['options'][$nextLetter] = trim($midOpt[3]);
            $activeOptionLetter = $nextLetter;

            return true;
        }

        return false;
    }

    private function normalizeOcrNumbersInHeader(string $line): string
    {
        return (string) preg_replace_callback('/\b(Questions|Q)\s+([lI1]\d{2})\s*([\-\–\—])\s*([lI1]\d{2})\b/iu', static function (array $m): string {
            $start = str_replace(['l', 'I'], '1', $m[2]);
            $end = str_replace(['l', 'I'], '1', $m[4]);

            return "{$m[1]} {$start}{$m[3]}{$end}";
        }, $line);
    }

    private function resolveReadingPart(int $questionNumber): string
    {
        return match (true) {
            $questionNumber >= 101 && $questionNumber <= 130 => 'PART5',
            $questionNumber >= 131 && $questionNumber <= 146 => 'PART6',
            default => 'PART7',
        };
    }

    /**
     * @param  list<array<string, mixed>>  $images
     * @param  array<int, bool>  $usedImages
     */
    private function findNearestImage(array $images, array &$usedImages, float $targetY, float $maxDistance): ?string
    {
        $bestIdx = null;
        $bestDist = $maxDistance;

        foreach ($images as $idx => $img) {
            if (! empty($usedImages[$idx])) {
                continue;
            }
            $imgCenterY = (((float) ($img['y0'] ?? 0)) + ((float) ($img['y1'] ?? 0))) / 2.0;
            $dist = abs($targetY - $imgCenterY);
            if ($dist <= $bestDist) {
                $bestDist = $dist;
                $bestIdx = $idx;
            }
        }

        if ($bestIdx === null) {
            return null;
        }

        $usedImages[$bestIdx] = true;

        return (string) ($images[$bestIdx]['relative_path'] ?? '');
    }

    private function isBoilerplateLine(string $line): bool
    {
        return preg_match('/^(?:Directions\s*:|GO\s+ON\s+TO\s+THE\s+NEXT\s+PAGE\b.*|READING\s+TEST|STOP!\s*Do\s+not\s+turn.*|ACTUAL\s+TEST\s+\d+.*|ETS\s+TOEIC.*|(?:Page\s*)?\d{1,3}(?:\s*\/\s*\d{1,3})?)$/iu', $line) === 1;
    }

    private function appendText(?string $base, string $addition): string
    {
        $baseTrim = trim((string) $base);
        $addTrim = trim($addition);
        if ($baseTrim === '') {
            return $addTrim;
        }

        return $baseTrim."\n".$addTrim;
    }

    /**
     * @param  array<int, array<string, mixed>>  $questions
     */
    private function syncGroupStimulus(array &$questions, string $groupKey, string $stimulus): void
    {
        foreach ($questions as $qNum => $q) {
            if (($q['group_key'] ?? null) === $groupKey) {
                $questions[$qNum]['stimulus_text'] = $stimulus;
            }
        }
    }
}
