<?php

declare(strict_types=1);

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class DocxToStandardXmlService
{
    public const CACHE_PREFIX = 'import_staging_';

    public const CACHE_TTL_SECONDS = 7200;

    /**
     * Bộ khung Directions chuẩn ETS (dùng làm fallback khi file Word/PDF thiếu hoặc là trang scan).
     */
    public const DEFAULT_PART_DIRECTIONS = [
        'PART1' => 'Directions: For each question in this part, you will hear four statements about a picture in your test book. When you hear the statements, you must select the one statement that best describes what you see in the picture. Then find the number of the question on your answer sheet and mark your answer. The statements will not be printed in your test book and will be spoken only one time.',
        'PART2' => 'Directions: You will hear a question or statement and three responses spoken in English. They will not be printed in your test book and will be spoken only one time. Select the best response to the question or statement and mark the letter (A), (B), or (C) on your answer sheet.',
        'PART3' => 'Directions: You will hear some conversations between two or more people. You will be asked to answer three questions about what the speakers say in each conversation. Select the best response to each question and mark the letter (A), (B), (C), or (D) on your answer sheet. The conversations will not be printed in your test book and will be spoken only one time.',
        'PART4' => 'Directions: You will hear some talks given by a single speaker. You will be asked to answer three questions about what the speaker says in each talk. Select the best response to each question and mark the letter (A), (B), (C), or (D) on your answer sheet. The talks will not be printed in your test book and will be spoken only one time.',
    ];

    public function __construct(
        protected WordQuestionImportService $wordParser
    ) {}

    /**
     * Phân tích file .docx theo thuật toán thuần (DOM + Regex, không gọi LLM),
     * trích xuất ảnh vào storage/app/public/staging/import_{batch_id}/,
     * chuyển đổi sang cấu trúc QuestionSet chuẩn StudyPrep và lưu vào Cache 2 giờ.
     *
     * @return array<string, mixed>
     */
    public function stageDocxImport(
        UploadedFile $file,
        string $importScope = 'AUTO',
        ?int $targetTestId = null,
        string $publishStatus = 'ACTIVE'
    ): array {
        $batchId = date('Ymd_His').'_'.Str::lower(Str::random(6));
        $extracted = $this->extractSequentialDocxContent($file->getRealPath(), $batchId);
        $cleanLines = $this->stripWebUiNoiseLines($extracted['parser_lines']);

        $effectiveScope = strtoupper(trim($importScope)) ?: 'AUTO';
        if ($effectiveScope === 'AUTO' && $this->isReadingContext($file->getClientOriginalName(), $cleanLines)) {
            $effectiveScope = 'READING';
        }

        $parsedQuestions = $this->wordParser->parseLines($cleanLines, $effectiveScope);
        if (empty($parsedQuestions)) {
            $parsedQuestions = $this->wordParser->parseFreeformLines($cleanLines, $effectiveScope);
        }

        // Ép chuẩn 3 Part Reading (101-130 -> PART5, 131-146 -> PART6, 147-200 -> PART7) & gom nhóm Part 6 mỗi 4 câu
        if ($effectiveScope === 'READING' || $this->hasReadingNumberRange($parsedQuestions)) {
            $parsedQuestions = $this->enforceReadingPartsAndGrouping($parsedQuestions);
        }

        // Áp dụng nhận diện đáp án in đậm <w:b/> nếu câu chưa có đáp án đúng rõ ràng
        $boldHints = $extracted['bold_option_hints'];
        foreach ($parsedQuestions as $idx => $q) {
            if (! empty($boldHints)) {
                $qNorm = $this->normalizeText((string) ($q['content'] ?? ''));
                foreach ($boldHints as $hint) {
                    if ($hint['question_norm'] !== '' && str_contains($qNorm, $hint['question_norm'])) {
                        $parsedQuestions[$idx]['correct'] = $hint['label'];
                        break;
                    }
                }
            }
        }

        $sets = $this->buildStandardSets($parsedQuestions, $extracted['blocks']);
        $sets = $this->validateSets($sets);
        $summary = $this->summarizeSets($sets);
        $partCodesInBatch = collect($sets)->pluck('part')->unique()->values()->all();
        $partDirections = $this->extractPartDirectionsFromLines($cleanLines, $partCodesInBatch);

        $payload = [
            'batch_id' => $batchId,
            'file_name' => $file->getClientOriginalName(),
            'import_scope' => strtoupper(trim($importScope)) ?: 'AUTO',
            'target_test_id' => $targetTestId,
            'publish_status' => strtoupper($publishStatus) === 'DRAFT' ? 'DRAFT' : 'ACTIVE',
            'staging_dir' => 'storage/staging/import_'.$batchId,
            'created_at' => now()->toIso8601String(),
            'expires_at' => now()->addSeconds(self::CACHE_TTL_SECONDS)->timestamp,
            'sets' => $sets,
            'part_directions' => $partDirections,
            'summary' => $summary,
        ];

        $this->saveStagingPayload($batchId, $payload);

        return $payload;
    }

    /**
     * Lưu dữ liệu staging vào file JSON trong thư mục tạm của batch (không phụ thuộc bảng cache trong DB).
     *
     * @param  array<string, mixed>  $payload
     */
    public function saveStagingPayload(string $batchId, array $payload): void
    {
        $dir = storage_path('app/public/staging/import_'.$batchId);
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        File::put(
            $dir.DIRECTORY_SEPARATOR.'staging.json',
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '{}'
        );
    }

    /**
     * Đọc dữ liệu staging của batch (kiểm tra hạn 2 giờ).
     *
     * @return array<string, mixed>|null
     */
    public function getStagingPayload(string $batchId): ?array
    {
        $safeBatchId = preg_replace('/[^A-Za-z0-9_\-]/', '', $batchId);
        if ($safeBatchId === '' || $safeBatchId !== $batchId) {
            return null;
        }

        $filePath = storage_path('app/public/staging/import_'.$safeBatchId.'/staging.json');
        if (! File::isFile($filePath)) {
            return null;
        }

        $decoded = json_decode((string) File::get($filePath), true);
        if (! is_array($decoded)) {
            return null;
        }

        $expiresAt = (int) ($decoded['expires_at'] ?? 0);
        if ($expiresAt > 0 && time() > $expiresAt) {
            $this->cleanupStagingDirectory($safeBatchId);

            return null;
        }

        return $decoded;
    }

    /**
     * Xóa dữ liệu staging của batch sau khi duyệt lưu hoặc hủy.
     */
    public function forgetStagingPayload(string $batchId): void
    {
        $this->cleanupStagingDirectory($batchId);
    }

    /**
     * Đọc tuần tự <w:p> và <w:tbl> trong word/document.xml, giải nén ảnh từ word/media/
     * vào storage/app/public/staging/import_{batch_id}/.
     *
     * @return array{
     *     parser_lines: list<string>,
     *     blocks: list<array{type: string, text: string, rows?: list<list<string>>, images: list<string>}>,
     *     bold_option_hints: list<array{question_norm: string, label: string}>
     * }
     */
    public function extractSequentialDocxContent(string $filePath, string $batchId): array
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            return ['parser_lines' => [], 'blocks' => [], 'bold_option_hints' => []];
        }

        $relMap = [];
        $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
        if (is_string($relsXml) && $relsXml !== '') {
            $relsDom = new DOMDocument;
            @$relsDom->loadXML($relsXml);
            $stagingRelDir = 'staging/import_'.$batchId;
            $stagingDiskDir = storage_path('app/public/'.$stagingRelDir);

            foreach ($relsDom->getElementsByTagName('Relationship') as $relNode) {
                if (! $relNode instanceof DOMElement) {
                    continue;
                }
                $rId = $relNode->getAttribute('Id');
                $target = $relNode->getAttribute('Target');
                if ($rId === '' || ! str_contains(strtolower($target), 'media/')) {
                    continue;
                }

                $zipMediaPath = 'word/'.ltrim(str_replace('\\', '/', $target), '/');
                $mediaBinary = $zip->getFromName($zipMediaPath);
                if (! is_string($mediaBinary) || $mediaBinary === '') {
                    continue;
                }

                $ext = strtolower(pathinfo($zipMediaPath, PATHINFO_EXTENSION));
                if (! in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'], true)) {
                    continue;
                }

                if (! File::isDirectory($stagingDiskDir)) {
                    File::makeDirectory($stagingDiskDir, 0755, true, true);
                }

                $fileName = basename($zipMediaPath);
                File::put($stagingDiskDir.DIRECTORY_SEPARATOR.$fileName, $mediaBinary);
                $relMap[$rId] = 'storage/'.$stagingRelDir.'/'.$fileName;
            }
        }

        $xmlContent = $zip->getFromName('word/document.xml');
        $zip->close();

        if (! is_string($xmlContent) || $xmlContent === '') {
            return ['parser_lines' => [], 'blocks' => [], 'bold_option_hints' => []];
        }

        $dom = new DOMDocument;
        @$dom->loadXML($xmlContent);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        $xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $xpath->registerNamespace('v', 'urn:schemas-microsoft-com:vml');

        $extractImagesFromNode = static function (\DOMNode $node) use ($xpath, $relMap): array {
            $found = [];
            foreach ($xpath->query('.//a:blip/@r:embed', $node) as $attr) {
                $rId = $attr->nodeValue;
                if ($rId && isset($relMap[$rId])) {
                    $found[] = $relMap[$rId];
                }
            }
            foreach ($xpath->query('.//v:imagedata/@r:id', $node) as $attr) {
                $rId = $attr->nodeValue;
                if ($rId && isset($relMap[$rId])) {
                    $found[] = $relMap[$rId];
                }
            }

            return array_values(array_unique($found));
        };

        $extractParagraphTextAndBold = static function (\DOMNode $pNode) use ($xpath): array {
            $fullText = '';
            $boldSegments = [];

            foreach ($xpath->query('.//w:r', $pNode) as $run) {
                $runText = '';
                foreach ($xpath->query('./w:t | ./w:tab | ./w:br', $run) as $tNode) {
                    $runText .= match ($tNode->localName) {
                        'tab' => ' ',
                        'br' => "\n",
                        default => $tNode->textContent,
                    };
                }
                if ($runText === '') {
                    continue;
                }
                $fullText .= $runText;

                $isBold = $xpath->query('./w:rPr/w:b[not(@w:val="0") and not(@w:val="false")]', $run)->length > 0;
                if ($isBold) {
                    $boldSegments[] = trim($runText);
                }
            }

            return [
                'text' => trim($fullText),
                'bold_segments' => array_values(array_filter($boldSegments, static fn (string $s): bool => $s !== '')),
            ];
        };

        $parserLines = [];
        $blocks = [];
        $boldOptionHints = [];
        $lastQuestionNorm = '';

        $bodyNodes = $xpath->query('/w:document/w:body/*');
        if ($bodyNodes->length === 0) {
            return ['parser_lines' => [], 'blocks' => [], 'bold_option_hints' => []];
        }

        foreach ($bodyNodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            if ($node->localName === 'p') {
                $pData = $extractParagraphTextAndBold($node);
                $images = $extractImagesFromNode($node);
                $text = $pData['text'];

                foreach ($images as $imgUrl) {
                    $parserLines[] = '[IMAGE_URL: '.$imgUrl.']';
                }

                if ($text !== '') {
                    foreach (preg_split('/\R/u', $text) ?: [] as $subLine) {
                        $trimmed = trim($subLine);
                        if ($trimmed === '') {
                            continue;
                        }
                        $parserLines[] = $trimmed;

                        if (preg_match('/^(?:Question|Câu|Q)?\s*\d{1,3}\s*[:\.\)]\s*(.+)$/iu', $trimmed, $qm)) {
                            $lastQuestionNorm = $this->normalizeText($qm[1]);
                        }
                    }

                    // Phát hiện đáp án in đậm dạng "(B) ..." hoặc "B. ..." khi không phải cả dòng đều in đậm
                    foreach ($pData['bold_segments'] as $bSeg) {
                        if (preg_match('/^\(?([A-D])[\.\)\:]\s*/iu', $bSeg, $bm) && mb_strlen($bSeg) < mb_strlen($text)) {
                            $boldOptionHints[] = [
                                'question_norm' => $lastQuestionNorm,
                                'label' => strtoupper($bm[1]),
                            ];
                        }
                    }
                }

                if ($text !== '' || ! empty($images)) {
                    $blocks[] = [
                        'type' => 'paragraph',
                        'text' => $text,
                        'images' => $images,
                    ];
                }
            } elseif ($node->localName === 'tbl') {
                $tableRows = [];
                $tableImages = [];

                foreach ($xpath->query('./w:tr', $node) as $rowNode) {
                    $rowCellsText = [];
                    $rowCellsImages = [];

                    foreach ($xpath->query('./w:tc', $rowNode) as $cellNode) {
                        $cellLines = [];
                        foreach ($xpath->query('.//w:p', $cellNode) as $cellP) {
                            $pData = $extractParagraphTextAndBold($cellP);
                            if ($pData['text'] !== '') {
                                $cellLines[] = $pData['text'];
                            }
                        }
                        $cellImgs = $extractImagesFromNode($cellNode);
                        $cellText = trim(implode("\n", $cellLines));
                        $rowCellsText[] = $cellText;
                        $rowCellsImages[] = $cellImgs;
                        foreach ($cellImgs as $ci) {
                            $tableImages[] = $ci;
                        }
                    }

                    $tableRows[] = $rowCellsText;

                    // Kiểm tra bảng 2 cột chỉ chứa ảnh (Speaking Task 2 / Writing Task 1)
                    $allCellsEmptyText = count(array_filter($rowCellsText, static fn (string $t): bool => $t !== '')) === 0;
                    $flattenedImgs = [];
                    foreach ($rowCellsImages as $cImgs) {
                        foreach ($cImgs as $ci) {
                            $flattenedImgs[] = $ci;
                        }
                    }

                    if ($allCellsEmptyText && ! empty($flattenedImgs)) {
                        foreach ($flattenedImgs as $img) {
                            $parserLines[] = '[SPLIT_IMAGE_CELL]';
                            $parserLines[] = '[IMAGE_URL: '.$img.']';
                        }
                    } else {
                        foreach ($flattenedImgs as $img) {
                            $parserLines[] = '[IMAGE_URL: '.$img.']';
                        }
                        $nonEmptyCells = array_values(array_filter($rowCellsText, static fn (string $t): bool => trim($t) !== ''));
                        if (! empty($nonEmptyCells)) {
                            $parserLines[] = implode(' | ', array_map(
                                static fn (string $c): string => preg_replace('/\s+/u', ' ', $c) ?? $c,
                                $nonEmptyCells
                            ));
                        }
                    }
                }

                if (! empty($tableRows) || ! empty($tableImages)) {
                    $blocks[] = [
                        'type' => 'table',
                        'text' => implode("\n", array_map(static fn (array $r): string => implode(' | ', array_filter($r)), $tableRows)),
                        'rows' => $tableRows,
                        'images' => array_values(array_unique($tableImages)),
                    ];
                }
            }
        }

        return [
            'parser_lines' => $parserLines,
            'blocks' => $blocks,
            'bold_option_hints' => $boldOptionHints,
        ];
    }

    /**
     * Gom danh sách câu hỏi đã parse thành các QuestionSet chuẩn hóa,
     * đồng thời gán block gốc (raw_source) cho giao diện đối chiếu 2 cột.
     *
     * @param  list<array<string, mixed>>  $flatQuestions
     * @param  list<array<string, mixed>>  $rawBlocks
     * @return list<array<string, mixed>>
     */
    public function buildStandardSets(array $flatQuestions, array $rawBlocks = []): array
    {
        $sets = [];
        $groupKeyMap = [];

        foreach ($flatQuestions as $idx => $q) {
            $partCode = strtoupper((string) ($q['part_code'] ?? 'PART5'));
            $isGrouped = in_array($partCode, ['PART3', 'PART4', 'PART6', 'PART7', 'SPK_T3', 'SPK_T4'], true);
            $rawGroupKey = trim((string) ($q['group_key'] ?? ''));

            if ($isGrouped && $rawGroupKey !== '') {
                $setKey = $partCode.'_'.$rawGroupKey;
            } elseif ($isGrouped && (! empty($q['stimulus_text']) || ! empty($q['audio_transcript']))) {
                $setKey = $partCode.'_'.md5((string) ($q['stimulus_text'] ?? '').'|'.(string) ($q['audio_transcript'] ?? ''));
            } else {
                $setKey = 'single_'.$idx;
            }

            $qMediaUrl = $q['image_url'] ?? null;
            $qTranscript = $this->cleanSpeechTranscript((string) ($q['audio_transcript'] ?? ''), $partCode, $q);

            if (! isset($groupKeyMap[$setKey])) {
                $groupKeyMap[$setKey] = count($sets);
                $sets[] = [
                    'set_key' => $setKey,
                    'part' => $partCode,
                    'title' => $q['group_title'] ?? ('Cụm '.$partCode.' #'.(count($sets) + 1)),
                    'media_url' => $qMediaUrl,
                    'audio_url' => $q['audio_url'] ?? null,
                    'transcript' => $qTranscript,
                    'stimulus' => $q['stimulus_text'] ?? null,
                    'raw_source' => '',
                    'raw_images' => $qMediaUrl ? [$qMediaUrl] : [],
                    'questions' => [],
                    'errors' => [],
                ];
            } else {
                $setIdx = $groupKeyMap[$setKey];
                if (empty($sets[$setIdx]['media_url']) && ! empty($qMediaUrl)) {
                    $sets[$setIdx]['media_url'] = $qMediaUrl;
                }
                if (! empty($qMediaUrl) && ! in_array($qMediaUrl, $sets[$setIdx]['raw_images'], true)) {
                    $sets[$setIdx]['raw_images'][] = $qMediaUrl;
                }
            }

            $setIdx = $groupKeyMap[$setKey];
            $options = is_array($q['options'] ?? null) ? $q['options'] : [];
            $seq = (int) ($q['question_number'] ?? ($idx + 1));
            $qContent = trim((string) ($q['content'] ?? ''));
            if ($partCode === 'PART6' && $qContent === '') {
                $qContent = "Blank {$seq} (Complete the text)";
            }

            $sets[$setIdx]['questions'][] = [
                'seq' => $seq,
                'content' => $qContent,
                'options' => $options,
                'correct' => $q['correct'] ?? null,
                'explanation' => $q['explanation'] ?? null,
                'media_url' => $qMediaUrl,
                'difficulty' => strtoupper((string) ($q['difficulty'] ?? 'UNSET')),
                'errors' => [],
            ];
        }

        // Gắn đoạn raw_source tương ứng từ các block gốc của file Word cho cột đối chiếu bên trái
        foreach ($sets as $i => $set) {
            $matchedChunks = [];
            if (! empty($set['stimulus'])) {
                $matchedChunks[] = trim((string) $set['stimulus']);
            }
            if (! empty($set['transcript'])) {
                $matchedChunks[] = 'Transcript: '.trim((string) $set['transcript']);
            }
            foreach ($set['questions'] as $sq) {
                $qLine = 'Question '.$sq['seq'].': '.$sq['content'];
                if (! empty($sq['options'])) {
                    $optLines = [];
                    foreach ($sq['options'] as $k => $v) {
                        if ($v !== null && $v !== '') {
                            $prefix = ($sq['correct'] === $k) ? '*' : '';
                            $optLines[] = "{$prefix}({$k}) {$v}";
                        }
                    }
                    if (! empty($optLines)) {
                        $qLine .= "\n".implode('  ', $optLines);
                    }
                }
                if (! empty($sq['explanation'])) {
                    $qLine .= "\nGiải thích: ".$sq['explanation'];
                }
                $matchedChunks[] = $qLine;
            }

            // Tìm thêm block bảng biểu gốc nếu có từ khóa trùng khớp
            foreach ($rawBlocks as $block) {
                if (($block['type'] ?? '') === 'table' && ! empty($block['text'])) {
                    $firstCell = $this->normalizeText(mb_substr($block['text'], 0, 40));
                    if ($firstCell !== '' && str_contains($this->normalizeText((string) ($set['stimulus'] ?? '')), $firstCell)) {
                        $matchedChunks[] = "[Bảng gốc trong Word]\n".$block['text'];
                        break;
                    }
                }
            }

            $sets[$i]['raw_source'] = implode("\n\n", array_filter($matchedChunks));
        }

        return $this->deduplicateSetsBySeq($sets);
    }

    /**
     * Khử trùng lặp câu hỏi theo số thứ tự seq trong cùng batch trước khi xuất XML/Staging.
     *
     * @param  list<array<string, mixed>>  $sets
     * @return list<array<string, mixed>>
     */
    private function deduplicateSetsBySeq(array $sets): array
    {
        $seenByKey = [];

        foreach ($sets as $sIdx => $set) {
            $part = strtoupper((string) ($set['part'] ?? 'PART5'));
            $keptQuestions = [];

            foreach ($set['questions'] ?? [] as $q) {
                $seq = (int) ($q['seq'] ?? 0);
                if ($seq <= 0) {
                    $keptQuestions[] = $q;

                    continue;
                }

                $dedupKey = $part.'#'.$seq;
                if (! isset($seenByKey[$dedupKey])) {
                    $seenByKey[$dedupKey] = [
                        'sIdx' => $sIdx,
                        'qIdx' => count($keptQuestions),
                    ];
                    $keptQuestions[] = $q;

                    continue;
                }

                // Hợp nhất vào câu đã tồn tại (giữ options đầy đủ hơn, giữ explanation/transcript từ bản có giải thích)
                $targetLoc = $seenByKey[$dedupKey];
                $targetSetIdx = $targetLoc['sIdx'];
                $targetQIdx = $targetLoc['qIdx'];
                $existingQ = $targetSetIdx === $sIdx
                    ? $keptQuestions[$targetQIdx]
                    : $sets[$targetSetIdx]['questions'][$targetQIdx];

                $existingOpts = is_array($existingQ['options'] ?? null) ? $existingQ['options'] : [];
                $newOpts = is_array($q['options'] ?? null) ? $q['options'] : [];
                foreach ($newOpts as $letter => $optVal) {
                    if (trim((string) ($existingOpts[$letter] ?? '')) === '' && trim((string) $optVal) !== '') {
                        $existingOpts[$letter] = $optVal;
                    }
                }
                $existingQ['options'] = $existingOpts;

                if (trim((string) ($existingQ['content'] ?? '')) === '' && trim((string) ($q['content'] ?? '')) !== '') {
                    $existingQ['content'] = $q['content'];
                }
                if (empty($existingQ['correct']) && ! empty($q['correct'])) {
                    $existingQ['correct'] = $q['correct'];
                }
                if (empty($existingQ['media_url']) && ! empty($q['media_url'])) {
                    $existingQ['media_url'] = $q['media_url'];
                }

                $oldExp = trim((string) ($existingQ['explanation'] ?? ''));
                $newExp = trim((string) ($q['explanation'] ?? ''));
                if ($newExp !== '') {
                    if ($oldExp === '') {
                        $existingQ['explanation'] = $newExp;
                    } elseif (! str_contains($oldExp, $newExp)) {
                        $existingQ['explanation'] = $oldExp."\n".$newExp;
                    }
                }

                if ($targetSetIdx === $sIdx) {
                    $keptQuestions[$targetQIdx] = $existingQ;
                } else {
                    $sets[$targetSetIdx]['questions'][$targetQIdx] = $existingQ;
                    if (empty($sets[$targetSetIdx]['transcript']) && ! empty($set['transcript'])) {
                        $sets[$targetSetIdx]['transcript'] = $set['transcript'];
                    }
                    if (empty($sets[$targetSetIdx]['media_url']) && ! empty($set['media_url'])) {
                        $sets[$targetSetIdx]['media_url'] = $set['media_url'];
                    }
                }
            }

            $sets[$sIdx]['questions'] = $keptQuestions;
        }

        return array_values(array_filter($sets, static fn (array $s): bool => ! empty($s['questions'])));
    }

    /**
     * Trích xuất đoạn Directions: đầu mỗi Part từ các dòng text gốc (Word hoặc PDF),
     * tự động fallback về bộ Directions chuẩn ETS nếu file nguồn thiếu hoặc là trang scan.
     *
     * @param  list<string>  $lines
     * @param  list<string>  $partCodes
     * @return array<string, string>
     */
    public function extractPartDirectionsFromLines(array $lines, array $partCodes = []): array
    {
        $extracted = [];
        $currentPart = null;
        $capturingDir = false;
        $buffer = [];

        $flushDir = static function () use (&$extracted, &$currentPart, &$capturingDir, &$buffer): void {
            if ($currentPart !== null && ! empty($buffer) && empty($extracted[$currentPart])) {
                $extracted[$currentPart] = trim(implode(' ', $buffer));
            }
            $capturingDir = false;
            $buffer = [];
        };

        foreach ($lines as $rawLine) {
            $line = trim((string) preg_replace('/\s+/u', ' ', $rawLine));
            if ($line === '') {
                continue;
            }

            if (preg_match('/^(?:PART|Part)\s*([1-7])\b/u', $line, $pm)) {
                $flushDir();
                $currentPart = 'PART'.$pm[1];

                continue;
            }

            // Dừng thu thập Directions ngay khi chạm câu hỏi đầu tiên hoặc Example
            if (preg_match('/^(?:Question\s+\d+|Câu\s+\d+|Questions\s+\d+|\d{1,3}\s*[\.\):]|Example\s*:|Statement\s*\(C\))/iu', $line) === 1) {
                $flushDir();

                continue;
            }

            if ($currentPart !== null && preg_match('/^(?:Directions?|Hướng\s*dẫn)\s*[:\-]\s*(.+)$/iu', $line, $dm)) {
                $capturingDir = true;
                $buffer = ['Directions: '.trim($dm[1])];

                continue;
            }

            if ($capturingDir) {
                $buffer[] = $line;
            }
        }
        $flushDir();

        foreach ($partCodes as $code) {
            $upper = strtoupper(trim((string) $code));
            if ($upper !== '' && empty($extracted[$upper]) && isset(self::DEFAULT_PART_DIRECTIONS[$upper])) {
                $extracted[$upper] = self::DEFAULT_PART_DIRECTIONS[$upper];
            }
        }

        return $extracted;
    }

    /**
     * Kiểm tra tính hợp lệ từng cụm và từng câu hỏi (viền đỏ + lý do lỗi).
     *
     * @param  list<array<string, mixed>>  $sets
     * @return list<array<string, mixed>>
     */
    public function validateSets(array $sets): array
    {
        foreach ($sets as $sIdx => $set) {
            $part = strtoupper((string) ($set['part'] ?? 'PART5'));
            $setErrors = [];

            if ($part === 'PART1' && empty($set['media_url'])) {
                $setErrors[] = 'Part 1 thiếu hình ảnh minh họa (media_url).';
            }
            if (in_array($part, ['PART1', 'PART2', 'PART3', 'PART4'], true) && trim((string) ($set['transcript'] ?? '')) === '') {
                $setErrors[] = "{$part} chưa có Transcript để sinh Audio TTS.";
            }
            if (in_array($part, ['PART6', 'PART7'], true) && trim((string) ($set['stimulus'] ?? '')) === '' && empty($set['media_url'])) {
                $setErrors[] = "{$part} thiếu đoạn văn đọc hiểu (stimulus).";
            }

            foreach ($set['questions'] as $qIdx => $q) {
                $qErrors = [];
                $hasPart6Stimulus = $part === 'PART6' && trim((string) ($set['stimulus'] ?? '')) !== '';
                $isPart1 = $part === 'PART1';
                if (trim((string) ($q['content'] ?? '')) === '' && ! $hasPart6Stimulus && ! $isPart1) {
                    $qErrors[] = 'Nội dung câu hỏi đang trống.';
                }

                $isProductive = in_array($part, WordQuestionImportService::PRODUCTIVE_PART_CODES, true);
                if (! $isProductive) {
                    $opts = is_array($q['options'] ?? null) ? $q['options'] : [];
                    $expectedLetters = $part === 'PART2' ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'D'];
                    $filledCount = 0;
                    foreach ($expectedLetters as $letter) {
                        if (isset($opts[$letter]) && trim((string) $opts[$letter]) !== '') {
                            $filledCount++;
                        }
                    }
                    if ($filledCount < count($expectedLetters)) {
                        $qErrors[] = 'Thiếu phương án trả lời (cần đủ '.count($expectedLetters).' lựa chọn).';
                    }
                    if (empty($q['correct'])) {
                        $qErrors[] = 'Chưa xác định đáp án đúng.';
                    } elseif (! in_array(strtoupper((string) $q['correct']), $expectedLetters, true)) {
                        $badAns = strtoupper((string) $q['correct']);
                        $qErrors[] = $part === 'PART2'
                            ? "Đáp án gốc là {$badAns} nhưng Part 2 chỉ có A, B, C — vui lòng chọn lại đáp án đúng."
                            : "Đáp án gốc là {$badAns} không hợp lệ — vui lòng chọn lại đáp án đúng.";
                    }
                } else {
                    if (in_array($part, ['SPK_T2', 'WRITING_T1'], true) && empty($q['media_url']) && empty($set['media_url'])) {
                        $qErrors[] = "{$part} thiếu hình ảnh mô tả.";
                    }
                }

                $sets[$sIdx]['questions'][$qIdx]['errors'] = $qErrors;
                if (! empty($qErrors)) {
                    $setErrors = array_merge($setErrors, $qErrors);
                }
            }

            $sets[$sIdx]['errors'] = array_values(array_unique($setErrors));
        }

        return $sets;
    }

    /**
     * Thống kê tổng quan bộ staging cho Sticky Header.
     *
     * @param  list<array<string, mixed>>  $sets
     * @return array{total_questions: int, valid_questions: int, error_questions: int, image_count: int, transcript_count: int}
     */
    public function summarizeSets(array $sets): array
    {
        $total = 0;
        $valid = 0;
        $error = 0;
        $images = [];
        $transcripts = 0;

        foreach ($sets as $set) {
            if (! empty($set['media_url'])) {
                $images[$set['media_url']] = true;
            }
            if (trim((string) ($set['transcript'] ?? '')) !== '') {
                $transcripts++;
            }

            $setLevelHasError = ! empty(array_filter($set['errors'] ?? [], static fn (string $e): bool => str_contains($e, 'Part 1') || str_contains($e, 'Transcript') || str_contains($e, 'đoạn văn')));
            foreach ($set['questions'] as $q) {
                $total++;
                if (! empty($q['media_url'])) {
                    $images[$q['media_url']] = true;
                }
                if (! empty($q['errors']) || $setLevelHasError) {
                    $error++;
                } else {
                    $valid++;
                }
            }
        }

        return [
            'total_questions' => $total,
            'valid_questions' => $valid,
            'error_questions' => $error,
            'image_count' => count($images),
            'transcript_count' => $transcripts,
        ];
    }

    /**
     * Dọn dẹp các thư mục staging/import_* đã quá hạn (mặc định 24 giờ) để tránh phình dung lượng ổ đĩa.
     */
    public function cleanupExpiredStagingDirectories(int $maxAgeHours = 24): int
    {
        $stagingRoot = storage_path('app/public/staging');
        if (! File::isDirectory($stagingRoot)) {
            return 0;
        }

        $cutoff = time() - (max(1, $maxAgeHours) * 3600);
        $deleted = 0;

        foreach (glob($stagingRoot.'/import_*', GLOB_ONLYDIR) ?: [] as $dir) {
            $mtime = @filemtime($dir);
            if ($mtime !== false && $mtime < $cutoff && File::deleteDirectory($dir)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Lọc ký tự điều khiển không hợp lệ trong XML 1.0 trước khi xuất.
     */
    private function sanitizeXmlText(string $text): string
    {
        $cleaned = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $text);

        return $cleaned ?? '';
    }

    /**
     * Tạo XML element có nội dung text đã lọc control chars và escape XML 1.0 an toàn (&, <, >, ").
     */
    private function appendEscapedXmlElement(DOMDocument $dom, DOMElement $parent, string $tagName, string $text): DOMElement
    {
        $el = $dom->createElement($tagName);
        $escaped = htmlspecialchars($this->sanitizeXmlText($text), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $fragment = $dom->createDocumentFragment();
        if ($escaped !== '' && @$fragment->appendXML($escaped)) {
            $el->appendChild($fragment);
        } else {
            $el->appendChild($dom->createTextNode($this->sanitizeXmlText($text)));
        }
        $parent->appendChild($el);

        return $el;
    }

    /**
     * Xuất tài liệu XML chuẩn StudyPrep từ danh sách QuestionSet.
     *
     * @param  list<array<string, mixed>>  $parsedSets
     */
    public function toStandardXml(array $parsedSets): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('studyprep_exam');
        $dom->appendChild($root);

        foreach ($parsedSets as $set) {
            $setEl = $dom->createElement('question_set');
            $setEl->setAttribute('part', $this->sanitizeXmlText((string) ($set['part'] ?? 'PART5')));
            if (! empty($set['media_url'])) {
                $setEl->setAttribute('media_url', $this->sanitizeXmlText((string) $set['media_url']));
            }
            if (! empty($set['audio_url'])) {
                $setEl->setAttribute('audio_url', $this->sanitizeXmlText((string) $set['audio_url']));
            }

            if (trim((string) ($set['transcript'] ?? '')) !== '') {
                $this->appendEscapedXmlElement($dom, $setEl, 'transcript', (string) $set['transcript']);
            }

            if (trim((string) ($set['stimulus'] ?? '')) !== '') {
                $this->appendEscapedXmlElement($dom, $setEl, 'stimulus', (string) $set['stimulus']);
            }

            foreach ($set['questions'] ?? [] as $q) {
                $qEl = $dom->createElement('question');
                $qEl->setAttribute('seq', $this->sanitizeXmlText((string) ($q['seq'] ?? 1)));
                if (! empty($q['correct'])) {
                    $qEl->setAttribute('correct', $this->sanitizeXmlText((string) $q['correct']));
                }

                $this->appendEscapedXmlElement($dom, $qEl, 'content', (string) ($q['content'] ?? ''));

                foreach (($q['options'] ?? []) as $key => $optText) {
                    if ($optText === null || $optText === '') {
                        continue;
                    }
                    $optEl = $this->appendEscapedXmlElement($dom, $qEl, 'option', (string) $optText);
                    $optEl->setAttribute('key', $this->sanitizeXmlText((string) $key));
                }

                if (trim((string) ($q['explanation'] ?? '')) !== '') {
                    $this->appendEscapedXmlElement($dom, $qEl, 'explanation', (string) $q['explanation']);
                }

                $setEl->appendChild($qEl);
            }

            $root->appendChild($setEl);
        }

        return (string) $dom->saveXML();
    }

    /**
     * Di chuyển ảnh được giữ lại từ thư mục staging sang storage/app/public/questions/images/.
     */
    public function promoteStagingImage(?string $mediaUrl, string $batchId): ?string
    {
        if ($mediaUrl === null || trim($mediaUrl) === '') {
            return null;
        }

        $clean = ltrim(str_replace('\\', '/', trim($mediaUrl)), '/');
        $stagingPrefix = 'storage/staging/import_'.$batchId.'/';
        if (! str_starts_with($clean, $stagingPrefix)) {
            if (preg_match('/^(?:https?:\/\/|data:|\/storage\/)/i', trim($mediaUrl)) === 1) {
                return trim($mediaUrl);
            }
            if (str_starts_with($clean, 'storage/')) {
                return '/'.$clean;
            }

            return '/storage/'.$clean;
        }

        $relativeInBatch = substr($clean, strlen($stagingPrefix));
        $fileName = basename($clean);
        $sourcePath = storage_path('app/public/staging/import_'.$batchId.'/'.$relativeInBatch);
        if (! File::isFile($sourcePath)) {
            $fallbackPath = storage_path('app/public/staging/import_'.$batchId.'/'.$fileName);
            if (! File::isFile($fallbackPath)) {
                return '/'.$clean;
            }
            $sourcePath = $fallbackPath;
        }

        $targetDir = storage_path('app/public/questions/images');
        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true, true);
        }

        $newFileName = 'imp_'.$batchId.'_'.$fileName;
        $targetPath = $targetDir.DIRECTORY_SEPARATOR.$newFileName;
        File::move($sourcePath, $targetPath);

        return '/storage/questions/images/'.$newFileName;
    }

    /**
     * Dọn dẹp thư mục tạm staging của batch sau khi duyệt lưu.
     */
    public function cleanupStagingDirectory(string $batchId): void
    {
        $stagingDir = storage_path('app/public/staging/import_'.$batchId);
        if (File::isDirectory($stagingDir)) {
            File::deleteDirectory($stagingDir);
        }
    }

    /**
     * Làm sạch lời thoại Listening / Speaking để sẵn sàng cho Text-to-Speech.
     *
     * @param  array<string, mixed>  $question
     */
    private function cleanSpeechTranscript(string $rawTranscript, string $partCode, array $question): string
    {
        $clean = trim($rawTranscript);
        if ($clean !== '') {
            $clean = (string) preg_replace('/^(?:Transcript|Audio\s*Script)\s*:\s*/iu', '', $clean);
            $clean = (string) preg_replace('/^\s*(?:M-Au|W-Am|W-Br|Narrator|Speaker\s*\d*)\s*:\s*/imu', '', $clean);

            return trim($clean);
        }

        // Part 1 & Part 2 nếu chưa có transcript riêng -> tự ghép từ câu hỏi + phương án A/B/C(/D)
        if (in_array($partCode, ['PART1', 'PART2'], true)) {
            $parts = [];
            if ($partCode === 'PART2' && ! empty($question['content'])) {
                $parts[] = trim((string) $question['content']);
            }
            $opts = is_array($question['options'] ?? null) ? $question['options'] : [];
            foreach (['A', 'B', 'C', 'D'] as $letter) {
                if (! empty($opts[$letter])) {
                    $parts[] = "({$letter}) ".trim((string) $opts[$letter]);
                }
            }

            return implode('. ', $parts);
        }

        if ($partCode === 'SPK_T3' && ! empty($question['stimulus_text'])) {
            return trim((string) $question['stimulus_text']);
        }

        return '';
    }

    private function normalizeText(string $text): string
    {
        return mb_strtolower((string) preg_replace('/\s+/u', ' ', trim($text)));
    }

    /**
     * Loại bỏ các dòng nhiễu copy từ giao diện web (0/100, Audio 00:00 / 00:00, ▶, Tốc độ: 1x, Giải thích chi tiết ▲).
     *
     * @param  list<string>  $lines
     * @return list<string>
     */
    private function stripWebUiNoiseLines(array $lines): array
    {
        $cleaned = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }
            if (
                preg_match('/^\d{1,3}\s*\/\s*\d{1,3}$/', $trimmed) === 1
                || preg_match('/^Audio\s+\d{2}:\d{2}\s*\/\s*\d{2}:\d{2}$/iu', $trimmed) === 1
                || preg_match('/^(?:▶|►|⏸|⏹|🔊)$/u', $trimmed) === 1
                || preg_match('/^Tốc\s*độ\s*:\s*[\d\.]+x$/iu', $trimmed) === 1
                || preg_match('/^Giải\s*thích\s*chi\s*tiết\s*[▲▼]?$/iu', $trimmed) === 1
            ) {
                continue;
            }
            $cleaned[] = $trimmed;
        }

        return $cleaned;
    }

    /**
     * Nhận diện ngữ cảnh Reading qua tên file (R, Reading, RC) hoặc dải số câu 101-200.
     *
     * @param  list<string>  $lines
     */
    private function isReadingContext(string $fileName, array $lines): bool
    {
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        if (preg_match('/(?:reading|\bRC\b|\bR\d*\b)/iu', $baseName) === 1) {
            return true;
        }

        $readingHits = 0;
        foreach ($lines as $line) {
            if (preg_match('/^(?:Questions\s+1(?:0[1-9]|[1-9]\d)|(?:Question\s+)?1(?:0[1-9]|[1-9]\d)[\.\:]|200[\.\:])/iu', $line) === 1) {
                $readingHits++;
                if ($readingHits >= 2) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  list<array<string, mixed>>  $questions
     */
    private function hasReadingNumberRange(array $questions): bool
    {
        foreach ($questions as $q) {
            $num = (int) ($q['question_number'] ?? 0);
            if ($num >= 101 && $num <= 200) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ép chuẩn 3 Part Reading theo dải số câu TOEIC (101-130 -> PART5, 131-146 -> PART6, 147-200 -> PART7)
     * và tự gom nhóm mỗi 4 câu cho Part 6 nếu chưa có group_key.
     *
     * @param  list<array<string, mixed>>  $questions
     * @return list<array<string, mixed>>
     */
    private function enforceReadingPartsAndGrouping(array $questions): array
    {
        foreach ($questions as $idx => $q) {
            $num = (int) ($q['question_number'] ?? 0);
            if ($num >= 101 && $num <= 130) {
                $questions[$idx]['part_code'] = 'PART5';
                $questions[$idx]['group_key'] = null;
            } elseif ($num >= 131 && $num <= 146) {
                $questions[$idx]['part_code'] = 'PART6';
                if (empty($questions[$idx]['group_key'])) {
                    $blockIndex = intdiv($num - 131, 4);
                    $start = 131 + ($blockIndex * 4);
                    $end = $start + 3;
                    $questions[$idx]['group_key'] = "range_{$start}_{$end}";
                    $questions[$idx]['group_title'] = "Questions {$start}-{$end}";
                }
            } elseif ($num >= 147 && $num <= 200) {
                $questions[$idx]['part_code'] = 'PART7';
            }
        }

        return $questions;
    }
}
