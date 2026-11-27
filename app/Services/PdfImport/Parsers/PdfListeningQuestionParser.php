<?php

namespace App\Services\PdfImport\Parsers;

use App\Services\PdfImport\Contracts\ToeicPdfSectionParserInterface;

/**
 * Strategy phân tích phần TOEIC Listening (Question 1 - 100) từ layout PDF:
 * - Part 1 (1-6): Gắn ảnh theo tọa độ Y gần nhất trên trang, không tự chèn text mặc định.
 * - Part 2 (7-31): 3 lựa chọn A/B/C.
 * - Part 3 (32-70) & Part 4 (71-100): Nhóm 3 câu theo mốc "Questions X-Y" kèm graphic/bảng biểu.
 */
class PdfListeningQuestionParser implements ToeicPdfSectionParserInterface
{
    public function supportsScope(string $importScope): bool
    {
        return in_array(strtoupper($importScope), ['FULL_LR', 'LISTENING'], true);
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
        $activeQuestionNumber = null;
        $part1ContentImages = $this->collectPart1ContentImages($extractedDocument);

        foreach ($extractedDocument['pages'] ?? [] as $page) {
            $pageNo = (int) ($page['page_no'] ?? 1);
            $blocks = is_array($page['blocks'] ?? null) ? $page['blocks'] : [];
            $tables = is_array($page['tables'] ?? null) ? $page['tables'] : [];
            $images = is_array($page['images'] ?? null) ? $page['images'] : [];
            $usedImages = [];

            // Ghép bảng vào danh sách phần tử có tọa độ để giữ đúng ngữ cảnh Part 3/4 graphic
            $timelineItems = [];
            foreach ($blocks as $b) {
                $timelineItems[] = [
                    'kind' => 'block',
                    'y0' => (float) ($b['y0'] ?? 0),
                    'x0' => (float) ($b['x0'] ?? 0),
                    'text' => (string) ($b['text'] ?? ''),
                ];
            }
            foreach ($tables as $t) {
                $timelineItems[] = [
                    'kind' => 'table',
                    'y0' => (float) ($t['y0'] ?? 0),
                    'x0' => (float) ($t['x0'] ?? 0),
                    'text' => (string) ($t['text'] ?? ''),
                ];
            }

            usort($timelineItems, static function (array $a, array $b): int {
                $cmpY = $a['y0'] <=> $b['y0'];

                return $cmpY !== 0 ? $cmpY : ($a['x0'] <=> $b['x0']);
            });

            foreach ($timelineItems as $item) {
                if ($item['kind'] === 'table') {
                    if ($currentGroupKey !== null && trim($item['text']) !== '') {
                        $currentGroupStimulus = $this->appendText($currentGroupStimulus, "[Bảng/Graphic]\n".$item['text']);
                        $this->syncGroupStimulus($questions, $currentGroupKey, $currentGroupStimulus);
                    }

                    continue;
                }

                $lines = preg_split('/\R/u', $item['text']) ?: [];
                $blockY = $item['y0'];

                foreach ($lines as $rawLine) {
                    $line = trim(preg_replace('/\s+/u', ' ', $rawLine) ?? '');
                    if ($line === '' || $this->isBoilerplateLine($line)) {
                        continue;
                    }

                    // Dừng nếu sang vùng Reading (Part 5 trở đi: câu 101+)
                    if (preg_match('/^\s*(?:PART\s*[567]\b|READING\s+TEST\b)/iu', $line)) {
                        return array_values($questions);
                    }

                    // Nhận diện tiêu đề nhóm Part 3 / Part 4: "Questions 32-34 refer to..."
                    if (preg_match('/^(?:Questions|Q)\s*(\d{1,3})\s*[\-\–]\s*(\d{1,3})\b(.*)$/iu', $line, $gm)) {
                        $start = (int) $gm[1];
                        $end = (int) $gm[2];
                        if ($start >= 101) {
                            return array_values($questions);
                        }
                        if ($start >= 32 && $end <= 100) {
                            $currentGroupRangeStart = $start;
                            $currentGroupRangeEnd = $end;
                            $currentGroupKey = "L_P{$pageNo}_{$start}_{$end}";
                            $currentGroupTitle = $line;
                            $currentGroupStimulus = null;
                            $currentGroupMedia = $this->findNearestImage($images, $usedImages, $blockY, 220.0);
                            $activeQuestionNumber = null;
                            $part1ContentImages = $this->collectPart1ContentImages($extractedDocument);

                            continue;
                        }
                    }

                    // Nhận diện dòng bắt đầu câu hỏi Listening (1..100)
                    if (preg_match('/^(?:Question\s+|Q)?(\d{1,3})\s*[\.\)\:]\s*(.*)$/iu', $line, $qm)) {
                        $qNum = (int) $qm[1];
                        $stem = trim($qm[2]);

                        if ($qNum >= 101) {
                            return array_values($questions);
                        }

                        if ($qNum >= 1 && $qNum <= 100) {
                            $partCode = $this->resolveListeningPart($qNum);

                            if ($partCode === 'PART1' || $partCode === 'PART2') {
                                $currentGroupKey = null;
                                $currentGroupTitle = null;
                                $currentGroupRangeStart = null;
                                $currentGroupRangeEnd = null;
                                $currentGroupStimulus = null;
                                $currentGroupMedia = null;
                            } elseif ($currentGroupRangeStart !== null && ($qNum < $currentGroupRangeStart || $qNum > $currentGroupRangeEnd)) {
                                $currentGroupKey = null;
                                $currentGroupTitle = null;
                                $currentGroupStimulus = null;
                                $currentGroupMedia = null;
                            }

                            $mediaUrl = null;
                            if ($partCode === 'PART1') {
                                // Khóa cứng gắn ảnh Part 1 tuần tự theo question_number (1..6)
                                $mediaUrl = $part1ContentImages[$qNum - 1] ?? null;
                                // Part 1 giữ nguyên stem rỗng nếu đề gốc chỉ có ảnh + số câu
                                if (preg_match('/^Look\s+at\s+the\s+picture/iu', $stem)) {
                                    $stem = '';
                                }
                            } elseif ($currentGroupKey !== null) {
                                $mediaUrl = $currentGroupMedia;
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
                                'media_url' => $mediaUrl,
                            ];
                            $activeQuestionNumber = $qNum;

                            continue;
                        }
                    }

                    // Nhiều lựa chọn trên cùng 1 dòng: (A) ... (B) ... (C) ... (D) ...
                    if ($activeQuestionNumber !== null && preg_match_all('/(?:^|\s)\(?([A-D])\)?[\.\)\:]\s*([^\(\n]+?)(?=\s+\(?[B-D]\)?[\.\)\:]|\s*$)/u', $line, $multiOpts, PREG_SET_ORDER) && count($multiOpts) >= 2) {
                        $partCode = $questions[$activeQuestionNumber]['part_code'];
                        $allowed = $partCode === 'PART2' ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'D'];
                        foreach ($multiOpts as $om) {
                            $lbl = strtoupper($om[1]);
                            if (in_array($lbl, $allowed, true)) {
                                $questions[$activeQuestionNumber]['options'][$lbl] = trim($om[2]);
                            }
                        }

                        continue;
                    }

                    // Từng lựa chọn riêng dòng: (A) ... / A. ...
                    if ($activeQuestionNumber !== null && preg_match('/^\(?([A-D])\)?[\.\)\:]\s*(.+)$/u', $line, $om)) {
                        $lbl = strtoupper($om[1]);
                        $partCode = $questions[$activeQuestionNumber]['part_code'];
                        $allowed = $partCode === 'PART2' ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'D'];
                        if (in_array($lbl, $allowed, true)) {
                            $questions[$activeQuestionNumber]['options'][$lbl] = trim($om[2]);
                        }

                        continue;
                    }

                    // Nối tiếp nội dung câu hỏi nếu chưa vào options
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

    private function resolveListeningPart(int $questionNumber): string
    {
        return match (true) {
            $questionNumber >= 1 && $questionNumber <= 6 => 'PART1',
            $questionNumber >= 7 && $questionNumber <= 31 => 'PART2',
            $questionNumber >= 32 && $questionNumber <= 70 => 'PART3',
            default => 'PART4',
        };
    }

    /**
     * Thu thập tuần tự các ảnh nội dung (>= 120x120px) ở vùng Part 1 để gắn cứng cho câu 1..6.
     * Bỏ qua tuyệt đối các trang Directions / Example / Statement (C).
     *
     * @param  array<string, mixed>  $extractedDocument
     * @return list<string>
     */
    private function collectPart1ContentImages(array $extractedDocument): array
    {
        $candidates = [];

        foreach ($extractedDocument['pages'] ?? [] as $page) {
            if (! empty($page['is_directions_or_example'])) {
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
            if (preg_match('/\bpart\s*1\b/iu', $pageText) === 1 && preg_match('/(?:question\s+[1-6]\b|\b[1-6]\s*[\.\)])/iu', $pageText) !== 1) {
                continue;
            }
            if (preg_match('/\bPART\s*[2-7]\b/iu', $pageText) === 1 && preg_match('/\bPART\s*1\b/iu', $pageText) !== 1) {
                break;
            }

            $pageNo = (int) ($page['page_no'] ?? 1);
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

        return array_slice(array_column($candidates, 'path'), 0, 6);
    }

    /**
     * Tìm ảnh chưa dùng có khoảng cách tọa độ Y gần nhất với block câu hỏi/nhóm.
     *
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
        return preg_match('/^(?:Directions\s*:|GO\s+ON\s+TO\s+THE\s+NEXT\s+PAGE|LISTENING\s+TEST|PART\s*[1-4]\b|Mark\s+your\s+answers\s+on\s+your\s+answer\s+sheet)/iu', $line) === 1;
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
