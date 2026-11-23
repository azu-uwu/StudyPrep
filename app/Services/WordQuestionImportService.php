<?php

namespace App\Services;

use App\Models\AnswerOption;
use App\Models\ExamSection;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\TestForm;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ZipArchive;

class WordQuestionImportService
{
    public const FIXED_PART_CODES = [
        'PART1', 'PART2', 'PART3', 'PART4',
        'PART5', 'PART6', 'PART7',
        'SPK_T1', 'SPK_T2', 'SPK_T3', 'SPK_T4', 'SPK_T5',
        'WRITING_T1', 'WRITING_T2', 'WRITING_T3',
    ];

    public const GROUPED_PART_CODES = ['PART3', 'PART4', 'PART6', 'PART7'];

    public const PRODUCTIVE_PART_CODES = [
        'SPK_T1', 'SPK_T2', 'SPK_T3', 'SPK_T4', 'SPK_T5',
        'WRITING_T1', 'WRITING_T2', 'WRITING_T3',
    ];

    public function __construct(
        protected AiToeicService $aiService
    ) {}

    /**
     * Đọc file .docx, .json, .csv hoặc .txt và trả về mảng câu hỏi chuẩn hóa cho cả 4 kỹ năng TOEIC.
     *
     * @return list<array<string, mixed>>
     */
    public function parseUploadedFile(UploadedFile $file, string $importScope = 'AUTO'): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $realPath = $file->getRealPath();
        $scope = strtoupper(trim($importScope)) ?: 'AUTO';

        if ($extension === 'docx') {
            return $this->parseDocxFile($realPath, $scope);
        }

        if ($extension === 'json') {
            return $this->applyScopeOverride($this->parseJsonFile($realPath), $scope);
        }

        if ($extension === 'csv') {
            return $this->applyScopeOverride($this->parseCsvFile($realPath), $scope);
        }

        $rawText = (string) file_get_contents($realPath);
        $lines = preg_split('/\R/u', $rawText) ?: [];

        return $this->parseLines($lines, $scope);
    }

    /**
     * Trích xuất văn bản + hình ảnh nhúng từ file Word (.docx) thành cấu trúc JSON chuẩn TOEIC 4 kỹ năng.
     *
     * @return array<string, mixed>
     */
    public function parseDocxToStructuredJson(string $filePath, ?string $examTitle = null, string $importScope = 'AUTO'): array
    {
        $scope = strtoupper(trim($importScope)) ?: 'AUTO';
        $extractedLines = $this->extractDocxLinesWithMediaPlaceholders($filePath);
        $rawText = implode("\n", $extractedLines);

        // 1. Ưu tiên gọi AI Parser (Gemini / OpenAI) nếu đã cấu hình API Key
        $aiStructured = $this->aiService->parseDocxTextWithAi($rawText, $scope);
        if ($aiStructured !== null) {
            return $aiStructured;
        }

        // 2. Deterministic Parser chuẩn 4 kỹ năng / 15 phần ETS (offline fallback)
        $flatQuestions = $this->parseLines($extractedLines, $scope);
        if (empty($flatQuestions)) {
            $flatQuestions = $this->parseFreeformLines($extractedLines, $scope);
        }

        return $this->convertFlatQuestionsToStructuredJson($flatQuestions, $examTitle);
    }

    /**
     * Đọc nội dung văn bản & trích xuất hình ảnh nhúng từ file Word (.docx).
     *
     * @return list<array<string, mixed>>
     */
    public function parseDocxFile(string $filePath, string $importScope = 'AUTO'): array
    {
        $structured = $this->parseDocxToStructuredJson($filePath, null, $importScope);

        return $this->flattenStructuredJson($structured);
    }

    /**
     * Mở file .docx, trích xuất toàn bộ hình ảnh/audio nhúng trong word/media/* ra thư mục public/storage/questions/imported/
     * và chèn placeholder [IMAGE_URL: storage/...] vào đúng vị trí đoạn văn tương ứng.
     *
     * @return list<string>
     */
    public function extractDocxLinesWithMediaPlaceholders(string $filePath): array
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            return [];
        }

        $relMap = [];
        $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
        if (is_string($relsXml) && $relsXml !== '') {
            $relsDom = new DOMDocument;
            @$relsDom->loadXML($relsXml);
            $batchDir = 'questions/imported/'.date('Ymd_His').'_'.Str::lower(Str::random(6));
            $publicTargetDir = public_path('storage/'.$batchDir);

            foreach ($relsDom->getElementsByTagName('Relationship') as $relNode) {
                if (! $relNode instanceof \DOMElement) {
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
                if (! in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'mp3', 'wav'], true)) {
                    continue;
                }

                if (! is_dir($publicTargetDir)) {
                    @mkdir($publicTargetDir, 0755, true);
                }

                $fileName = basename($zipMediaPath);
                @file_put_contents($publicTargetDir.DIRECTORY_SEPARATOR.$fileName, $mediaBinary);
                $publicUrl = 'storage/'.$batchDir.'/'.$fileName;

                $relMap[$rId] = [
                    'url' => $publicUrl,
                    'type' => in_array($ext, ['mp3', 'wav'], true) ? 'AUDIO' : 'IMAGE',
                ];
            }
        }

        $xmlContent = $zip->getFromName('word/document.xml');
        $zip->close();

        if (! is_string($xmlContent) || $xmlContent === '') {
            return [];
        }

        $dom = new DOMDocument;
        @$dom->loadXML($xmlContent);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        $xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $xpath->registerNamespace('v', 'urn:schemas-microsoft-com:vml');

        $lines = [];
        $bodyNodes = $xpath->query('/w:document/w:body/*');
        $autoNumCounter = 0;
        $hasEnglishContext = false;

        $extractParagraphMedia = function (\DOMNode $paragraph) use ($xpath, $relMap): array {
            $mediaTags = [];
            $blips = $xpath->query('.//a:blip/@r:embed | .//v:imagedata/@r:id', $paragraph);
            if ($blips !== false) {
                foreach ($blips as $attr) {
                    $rId = $attr->nodeValue;
                    if (isset($relMap[$rId])) {
                        $tag = $relMap[$rId]['type'] === 'AUDIO' ? 'AUDIO_URL' : 'IMAGE_URL';
                        $mediaTags[] = "[{$tag}: {$relMap[$rId]['url']}]";
                    }
                }
            }

            return $mediaTags;
        };

        $extractParagraphTextLines = function (\DOMNode $paragraph) use ($xpath): array {
            $result = [];
            $paragraphText = '';
            $nodes = $xpath->query('.//w:t | .//w:br | .//w:tab', $paragraph);
            if ($nodes !== false) {
                foreach ($nodes as $node) {
                    if ($node->nodeName === 'w:br') {
                        $t = trim($paragraphText);
                        if ($t !== '') {
                            $result[] = $t;
                        }
                        $paragraphText = '';
                    } elseif ($node->nodeName === 'w:tab') {
                        $paragraphText .= ' ';
                    } else {
                        $paragraphText .= $node->textContent;
                    }
                }
            }
            $trimmed = trim($paragraphText);
            if ($trimmed !== '') {
                $result[] = $trimmed;
            }

            return $result;
        };

        if ($bodyNodes !== false) {
            foreach ($bodyNodes as $bodyNode) {
                if ($bodyNode->nodeName === 'w:p') {
                    foreach ($extractParagraphMedia($bodyNode) as $mTag) {
                        $lines[] = $mTag;
                    }

                    $pTexts = $extractParagraphTextLines($bodyNode);
                    $hasNumPr = $xpath->query('./w:pPr/w:numPr', $bodyNode);

                    foreach ($pTexts as $pIdx => $trimmed) {
                        $isVietnamese = $this->isVietnameseText($trimmed);

                        if (
                            $pIdx === 0
                            && $hasNumPr !== false
                            && $hasNumPr->length > 0
                            && ! ($isVietnamese && $hasEnglishContext)
                            && ! preg_match('/^(?:[A-D][\.\)\:]|\([A-D]\)|→|->|•|\-|\–|➢|➤|Câu|Question|Part|Task|Listening|Reading|Speaking|Writing|Đáp\s*án|Answer|Giải\s*thích|Bài\s*mẫu|Dịch|Tạm\s*dịch|Đoạn\s*văn|Transcript|Hướng\s*dẫn|Directions|Đề\s*\d|\d+[\.\)])/iu', $trimmed)
                        ) {
                            $autoNumCounter++;
                            $trimmed = "Câu {$autoNumCounter}: ".$trimmed;
                        }

                        if (! $isVietnamese && preg_match('/[A-Za-z]{3,}/', $trimmed) === 1) {
                            $hasEnglishContext = true;
                        }

                        $lines[] = $trimmed;
                    }
                } elseif ($bodyNode->nodeName === 'w:tbl') {
                    $rows = $xpath->query('./w:tr', $bodyNode);
                    if ($rows->length === 0) {
                        continue;
                    }

                    $tableRowsData = [];
                    $hasMultiImageRow = false;
                    $isWritingTable = false;

                    foreach ($rows as $rowNode) {
                        $cells = $xpath->query('./w:tc', $rowNode);
                        if ($cells->length === 0) {
                            continue;
                        }
                        $rowCells = [];
                        $imageCountInRow = 0;

                        foreach ($cells as $cellNode) {
                            $cellMedia = [];
                            $cellTexts = [];
                            $cellParagraphs = $xpath->query('.//w:p', $cellNode);
                            if ($cellParagraphs !== false) {
                                foreach ($cellParagraphs as $cp) {
                                    foreach ($extractParagraphMedia($cp) as $mTag) {
                                        $cellMedia[] = $mTag;
                                        if (str_starts_with($mTag, '[IMAGE_URL:')) {
                                            $imageCountInRow++;
                                        }
                                    }
                                    foreach ($extractParagraphTextLines($cp) as $ct) {
                                        $cellTexts[] = $ct;
                                    }
                                }
                            }
                            $rowCells[] = [
                                'media' => $cellMedia,
                                'text' => trim(implode("\n", $cellTexts)),
                            ];
                        }

                        if ($imageCountInRow >= 2) {
                            $hasMultiImageRow = true;
                        }
                        if (count($rowCells) >= 2) {
                            $firstCellLower = mb_strtolower($rowCells[0]['text']);
                            $secondCellLower = mb_strtolower($rowCells[1]['text']);
                            if (
                                (str_contains($firstCellLower, 'question') && str_contains($secondCellLower, 'answer'))
                                || (count($rowCells) === 3 && ! empty($rowCells[1]['media']) && preg_match('/^\d{1,2}$/', trim($rowCells[0]['text'])) === 1)
                            ) {
                                $isWritingTable = true;
                            }
                        }
                        $tableRowsData[] = $rowCells;
                    }

                    // Loại 1: Bảng 3 cột Writing Task 1 (Câu | Hình + từ khóa | Câu trả lời mẫu)
                    if ($isWritingTable) {
                        foreach ($tableRowsData as $rowCells) {
                            $c0 = trim($rowCells[0]['text'] ?? '');
                            $c1 = trim($rowCells[1]['text'] ?? '');
                            $c1Media = $rowCells[1]['media'] ?? [];
                            $c2 = trim($rowCells[2]['text'] ?? '');

                            // Bỏ qua hàng tiêu đề Questions | Answers
                            if (preg_match('/^(?:Questions?|STT|Câu)$/iu', $c0) || preg_match('/^(?:Answers?|Sample)/iu', $c1)) {
                                continue;
                            }

                            if (count($rowCells) === 2) {
                                // Bảng 2 cột (Questions [ảnh + từ khóa] | Answers)
                                $qText = $c0 !== '' ? $c0 : 'Write a sentence based on the picture.';
                                $qMedia = $rowCells[0]['media'] ?? [];
                                if (preg_match('/^(?:Question|Câu)?\s*(\d{1,2})\s*[:.\-\)]?\s*(.*)$/iu', $qText, $qm)) {
                                    $lines[] = 'Question '.$qm[1].': '.($qm[2] !== '' ? $qm[2] : 'Write a sentence based on the picture.');
                                } else {
                                    $lines[] = 'Question: '.$qText;
                                }
                                foreach ($qMedia as $mTag) {
                                    $lines[] = $mTag;
                                }
                                if ($c1 !== '') {
                                    $lines[] = 'Bài mẫu tham khảo: '.$c1;
                                }
                            } else {
                                $qNum = preg_match('/\d+/', $c0, $nm) ? $nm[0] : '';
                                $lines[] = ($qNum !== '' ? "Question {$qNum}: " : 'Question: ').($c1 !== '' ? $c1 : 'Write a sentence based on the picture.');
                                foreach ($c1Media as $mTag) {
                                    $lines[] = $mTag;
                                }
                                if ($c2 !== '') {
                                    $lines[] = 'Bài mẫu tham khảo: '.$c2;
                                }
                            }
                        }

                        continue;
                    }

                    // Loại 2: Bảng nhiều ảnh ngang (Speaking Task 2: Cột 1 = Câu 3, Cột 2 = Câu 4)
                    if ($hasMultiImageRow) {
                        foreach ($tableRowsData as $rowCells) {
                            foreach ($rowCells as $cell) {
                                if (! empty($cell['media'])) {
                                    $lines[] = '[SPLIT_IMAGE_CELL]';
                                    foreach ($cell['media'] as $mTag) {
                                        $lines[] = $mTag;
                                    }
                                    if ($cell['text'] !== '') {
                                        $lines[] = $cell['text'];
                                    }
                                } elseif ($cell['text'] !== '') {
                                    $lines[] = $cell['text'];
                                }
                            }
                        }

                        continue;
                    }

                    // Loại 3: Bảng văn bản thông thường (Part 7 hóa đơn/lịch trình, Speaking Part 4 bảng thông tin)
                    foreach ($tableRowsData as $rowCells) {
                        foreach ($rowCells as $cell) {
                            foreach ($cell['media'] as $mTag) {
                                $lines[] = $mTag;
                            }
                        }
                        $textCols = array_values(array_filter(array_map(
                            static fn (array $c): string => preg_replace('/\s+/', ' ', $c['text']) ?? '',
                            $rowCells
                        ), static fn (string $s): bool => $s !== ''));
                        if (! empty($textCols)) {
                            $lines[] = implode(' | ', $textCols);
                        }
                    }
                }
            }
        }

        return $lines;
    }

    /**
     * Kết quả gắn câu hỏi vào đề thi của lần persist gần nhất (nếu có target_test_id).
     *
     * @var array{attached: int, skipped: int, rejected_skill: int, rejected_limit: int, activated: int}|null
     */
    private ?array $lastAttachResult = null;

    /**
     * @return array{attached: int, skipped: int, rejected_skill: int, rejected_limit: int, activated: int}|null
     */
    public function getLastAttachResult(): ?array
    {
        return $this->lastAttachResult;
    }

    /**
     * Nhận diện văn bản tiếng Việt dựa trên dấu thanh tiếng Việt hoặc tiền tố dịch nghĩa.
     * Bảo vệ chống nhận nhầm câu tiếng Anh có từ mượn (VD: "résumé", "café", "naïve").
     */
    public function isVietnameseText(string $text): bool
    {
        $clean = trim($text);
        if ($clean === '') {
            return false;
        }

        if (preg_match('/^(?:Dịch(?:\s+đề\s+bài|\s+câu\s+hỏi|\s+nghĩa|\s+phương\s+án)?|Tạm\s+dịch|Nghĩa)\s*[:\-]/iu', $clean) === 1) {
            return true;
        }

        // Các ký tự đặc trưng chỉ có trong tiếng Việt (đ, ă, ơ, ư, thanh nặng/hỏi/ngã, mũ+dấu)
        if (preg_match('/[đăơưạảãầấậẩẫằắặẳẵẹẻẽềếệểễịỉĩọỏõồốộổỗờớợởỡụủũừứựửữỳýỵỷỹ]/iu', $clean) === 1) {
            return true;
        }

        // Nếu chỉ có dấu Latin-1 (é, è, á, à, â, ê, ô...) vốn gặp trong từ mượn tiếng Anh như "résumé", "café",
        // yêu cầu có ít nhất 2 từ mang dấu khác nhau hoặc chứa từ chức năng tiếng Việt rõ ràng.
        if (preg_match_all('/\b\p{L}*[àáâèéêìíòóôùú]\p{L}*\b/iu', $clean, $matches) >= 2) {
            return true;
        }

        return preg_match('/\b(?:câu|đề|bài|và|của|là|người|không|một|các|này|trong|cho|hãy)\b/iu', $clean) === 1
            && preg_match('/[àáâèéêìíòóôùú]/iu', $clean) === 1;
    }

    /**
     * Phân tích danh sách dòng văn bản thành các Đơn vị Câu hỏi Hoàn chỉnh cho đủ 4 kỹ năng TOEIC (15 phần ETS):
     * - Hỗ trợ lựa chọn A., (A), A), mũi tên/gạch đầu dòng (→, ->, -, •)
     * - Gộp song ngữ Anh–Việt sạch vào explanation (không rò rỉ phương án tiếng Anh sang explanation)
     * - Gom nhóm QuestionSet chuẩn cho Part 3, 4, 6, 7 & stimulus cho Speaking/Writing
     * - Gắn ảnh forward đúng vị trí (không gắn ngược về câu cũ)
     *
     * @param  list<string>  $lines
     * @return list<array<string, mixed>>
     */
    public function parseLines(array $lines, string $importScope = 'AUTO'): array
    {
        $scope = strtoupper(trim($importScope)) ?: 'AUTO';
        $forcedPart = in_array($scope, self::FIXED_PART_CODES, true) ? $scope : null;
        $activeSkill = match ($scope) {
            'LISTENING', 'FULL_LR' => 'listening',
            'READING' => 'reading',
            'SPEAKING', 'FULL_SW' => 'speaking',
            'WRITING' => 'writing',
            default => $forcedPart !== null ? $this->skillOfPartCode($forcedPart) : 'auto',
        };
        $currentPart = $forcedPart ?? match ($activeSkill) {
            'listening' => 'PART1',
            'reading' => 'PART5',
            'speaking' => 'SPK_T1',
            'writing' => 'WRITING_T1',
            default => 'PART5',
        };
        $hasExplicitPartHeader = $forcedPart !== null;

        $questions = [];
        $answerKeyMap = [];
        $inAnswerKeySection = false;

        $currentStimulus = null;
        $capturingStimulus = false;
        $inReadingTranscriptBlock = false;
        $pendingReadingTranslation = null;
        $inDirectionsBlock = false;
        $currentGroupKey = null;
        $currentGroupTitle = null;
        $currentGroupRangeStart = null;
        $currentGroupRangeEnd = null;
        $currentGroupImageUrl = null;
        $pendingImageQueue = [];
        $pendingAudioUrl = null;
        $currentQuestion = null;
        $autoSpkCounter = 0;

        $shiftPendingImage = function () use (&$pendingImageQueue): ?string {
            if (empty($pendingImageQueue)) {
                return null;
            }

            return array_shift($pendingImageQueue);
        };

        $flushQuestion = function () use (&$questions, &$currentQuestion): void {
            if ($currentQuestion !== null && (trim((string) $currentQuestion['content']) !== '' || ! empty($currentQuestion['image_url']) || ! empty($currentQuestion['options']))) {
                $pCode = strtoupper((string) ($currentQuestion['part_code'] ?? 'PART5'));
                $isProductive = in_array($pCode, self::PRODUCTIVE_PART_CODES, true);

                if ($isProductive) {
                    $currentQuestion['options'] = [];
                    $currentQuestion['correct'] = null;
                } elseif ($pCode === 'PART2') {
                    unset($currentQuestion['options']['D'], $currentQuestion['_vn_options']['D']);
                    if (($currentQuestion['correct'] ?? 'A') === 'D') {
                        $currentQuestion['correct'] = 'A';
                    }
                }

                $vnLines = $currentQuestion['_vn_lines'] ?? [];
                $vnOptions = $isProductive ? [] : ($currentQuestion['_vn_options'] ?? []);
                if (! empty($vnLines) || ! empty($vnOptions)) {
                    $formattedVn = $this->formatVietnameseTranslation(implode(' ', $vnLines), $vnOptions, $pCode);
                    $currentQuestion['explanation'] = $this->appendExplanation($currentQuestion['explanation'] ?? null, $formattedVn);
                }

                if (! empty($currentQuestion['explanation'])) {
                    $currentQuestion['explanation'] = $this->cleanExplanationText((string) $currentQuestion['explanation']);
                }

                if (! $isProductive && empty($currentQuestion['_has_explicit_answer'])) {
                    $inferred = $this->inferCorrectOptionFromExplanation($currentQuestion);
                    if ($inferred !== null) {
                        $currentQuestion['correct'] = $inferred;
                    }
                }

                if ($pCode === 'PART6' && trim((string) ($currentQuestion['content'] ?? '')) === '') {
                    $qNum = (int) ($currentQuestion['question_number'] ?? 131);
                    $currentQuestion['content'] = "Blank {$qNum} (Complete the text)";
                }

                unset($currentQuestion['_vn_lines'], $currentQuestion['_vn_options'], $currentQuestion['_in_vn_block'], $currentQuestion['_in_explanation'], $currentQuestion['_has_explicit_answer']);

                $questions[] = $currentQuestion;
            }
            $currentQuestion = null;
        };

        foreach ($lines as $rawLine) {
            $line = trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $rawLine));
            if (
                $line === ''
                || str_starts_with($line, '//')
                || preg_match('/^={3,}$/', $line) === 1
                || preg_match('/^\d{1,3}\s*\/\s*\d{1,3}$/', $line) === 1
                || preg_match('/^Audio$/iu', $line) === 1
                || preg_match('/^(?:Audio\s+)?\d{2}:\d{2}\s*\/\s*\d{2}:\d{2}$/iu', $line) === 1
                || preg_match('/^(?:▶|►|⏸|⏹|🔊)$/u', $line) === 1
                || preg_match('/^Tốc\s*độ\s*:\s*[\d\.]+x$/iu', $line) === 1
                || preg_match('/^Xem\s+đáp\s+án$/iu', $line) === 1
            ) {
                continue;
            }

            // Nhận diện chuyển Đề mới ("Đề 1", "ĐỀ 2:", "Đề 3", "ACTUAL TEST 1...") -> reset bộ đếm nhóm & ảnh
            if (preg_match('/^(?:Đề(?:\s*số)?\s*\d+|ACTUAL\s*TEST\s*\d+(?:\s*[\-\–]\s*(?:READING|LISTENING|SPEAKING|WRITING))?)\s*[:\.]?$/iu', $line, $deckMatch)) {
                $flushQuestion();
                $capturingStimulus = false;
                $inReadingTranscriptBlock = false;
                $pendingReadingTranslation = null;
                $inDirectionsBlock = false;
                $currentStimulus = null;
                $currentGroupKey = null;
                $currentGroupTitle = null;
                $currentGroupRangeStart = null;
                $currentGroupRangeEnd = null;
                $currentGroupImageUrl = null;
                $pendingImageQueue = [];
                $pendingAudioUrl = null;
                $autoSpkCounter = 0;

                if ($forcedPart === null) {
                    if (stripos($line, 'READING') !== false) {
                        $activeSkill = 'reading';
                        $currentPart = 'PART5';
                    } elseif (stripos($line, 'LISTENING') !== false) {
                        $activeSkill = 'listening';
                        $currentPart = 'PART1';
                    } elseif (stripos($line, 'SPEAKING') !== false) {
                        $activeSkill = 'speaking';
                        $currentPart = 'SPK_T1';
                    } elseif (stripos($line, 'WRITING') !== false) {
                        $activeSkill = 'writing';
                        $currentPart = 'WRITING_T1';
                    }
                }

                continue;
            }

            // Nhận diện bảng Đáp án cuối tài liệu (Answer Key)
            if (preg_match('/^(?:ANSWER\s*KEY|BẢNG\s*ĐÁP\s*ÁN|ĐÁP\s*ÁN\s*CHI\s*TIẾT)\s*:?$/iu', $line)) {
                $flushQuestion();
                $capturingStimulus = false;
                $inReadingTranscriptBlock = false;
                $inDirectionsBlock = false;
                $inAnswerKeySection = true;

                continue;
            }

            if ($inAnswerKeySection) {
                if (preg_match_all('/(\d{1,3})\s*[\.\:\-\)]\s*\(?([A-D])\)?/i', $line, $keyMatches, PREG_SET_ORDER)) {
                    foreach ($keyMatches as $km) {
                        $answerKeyMap[(int) $km[1]] = strtoupper($km[2]);
                    }
                }

                continue;
            }

            // Nhận diện tách ô ảnh từ bảng 2 cột Speaking Task 2
            if ($line === '[SPLIT_IMAGE_CELL]') {
                $flushQuestion();
                $capturingStimulus = false;
                $inDirectionsBlock = false;
                if ($forcedPart === null && $activeSkill === 'speaking') {
                    $currentPart = 'SPK_T2';
                    $hasExplicitPartHeader = true;
                }
                $autoSpkCounter = $autoSpkCounter < 3 ? 3 : ($autoSpkCounter + 1);
                $currentQuestion = [
                    'question_number' => $autoSpkCounter,
                    'part_code' => $forcedPart ?? 'SPK_T2',
                    'group_key' => null,
                    'group_title' => null,
                    'stimulus_text' => null,
                    'audio_transcript' => null,
                    'image_url' => null,
                    'audio_url' => null,
                    'content' => 'Describe the picture on your screen in as much detail as you can.',
                    'options' => [],
                    'correct' => null,
                    'difficulty' => 'UNSET',
                    'explanation' => null,
                    '_vn_lines' => [],
                    '_vn_options' => [],
                    '_in_vn_block' => false,
                    '_in_explanation' => false,
                ];

                continue;
            }

            // Nhận diện Media Placeholders [IMAGE_URL: ...] & [AUDIO_URL: ...]
            if (preg_match('/^\[IMAGE_URL:\s*(.+?)\]$/i', $line, $m)) {
                $url = $this->normalizeRelativeMediaPath($m[1]);
                $canAttachToCurrent = $currentQuestion !== null
                    && empty($currentQuestion['image_url'])
                    && empty($currentQuestion['options'])
                    && empty($currentQuestion['_in_vn_block'])
                    && empty($currentQuestion['_in_explanation']);

                if ($canAttachToCurrent) {
                    $lastIdx = count($questions) - 1;
                    if (
                        $currentQuestion['part_code'] === 'WRITING_T1'
                        && $lastIdx >= 0
                        && ($questions[$lastIdx]['part_code'] ?? '') === 'WRITING_T1'
                        && empty($questions[$lastIdx]['image_url'])
                    ) {
                        $questions[$lastIdx]['image_url'] = $url;
                    } else {
                        $currentQuestion['image_url'] = $url;
                    }
                } else {
                    if ($currentGroupKey !== null && $currentGroupImageUrl === null && $currentQuestion === null) {
                        $currentGroupImageUrl = $url;
                    } else {
                        $pendingImageQueue[] = $url;
                    }
                }

                continue;
            }

            if (preg_match('/^\[AUDIO_URL:\s*(.+?)\]$/i', $line, $m)) {
                $url = $this->normalizeRelativeMediaPath($m[1]);
                if ($currentQuestion !== null && empty($currentQuestion['audio_url']) && empty($currentQuestion['options'])) {
                    $currentQuestion['audio_url'] = $url;
                } else {
                    $pendingAudioUrl = $url;
                }

                continue;
            }

            // 1. Nhận diện tiêu đề Kỹ năng / Part / Task
            if ($forcedPart === null) {
                $detectedSection = $this->detectPartOrSkillHeader($line, $activeSkill, $scope);
                if ($detectedSection !== null) {
                    $flushQuestion();
                    $capturingStimulus = false;
                    $inReadingTranscriptBlock = false;
                    $inDirectionsBlock = false;
                    if ($detectedSection['skill'] !== null) {
                        $activeSkill = $detectedSection['skill'];
                    }
                    if ($detectedSection['part_code'] !== null) {
                        $currentPart = $detectedSection['part_code'];
                        $hasExplicitPartHeader = true;
                    }
                    $currentStimulus = null;
                    $capturingStimulus = ($currentPart === 'SPK_T4');
                    $currentGroupKey = null;
                    $currentGroupTitle = null;
                    $currentGroupRangeStart = null;
                    $currentGroupRangeEnd = null;
                    $currentGroupImageUrl = null;
                    $pendingImageQueue = [];
                    $pendingAudioUrl = null;
                    $pendingReadingTranslation = null;

                    continue;
                }
            }

            // Bỏ qua các đoạn Hướng dẫn tiếng Việt chung ở đầu phần thi Speaking/Reading/Writing
            if (preg_match('/^Hướng\s*dẫn\s*[:\-]/iu', $line)) {
                $flushQuestion();
                $inDirectionsBlock = true;
                $capturingStimulus = false;

                continue;
            }

            // Bỏ qua dòng tiêu đề bảng 2 cột kiểu "Câu 3 | Câu 4" hoặc "Question 3 | Question 4"
            if (preg_match('/^(?:Câu|Question|Questions?)\s*\d+\s*\|\s*(?:Câu|Question|Questions?)\s*\d+\s*$/iu', $line)) {
                continue;
            }

            // Bỏ qua Directions chung ở đầu phần Writing/Speaking (trừ Directions: Respond to the email... thuộc Writing Task 2)
            if (preg_match('/^Directions\s*[:\-]\s*(.*)$/iu', $line, $dm)) {
                $dirBody = trim($dm[1]);
                if ($currentPart === 'WRITING_T2' && preg_match('/respond\s+to\s+the\s+e-?mail/iu', $dirBody)) {
                    if ($currentQuestion === null) {
                        $currentQuestion = [
                            'question_number' => 6,
                            'part_code' => 'WRITING_T2',
                            'group_key' => null,
                            'group_title' => null,
                            'stimulus_text' => $currentStimulus,
                            'audio_transcript' => null,
                            'image_url' => $shiftPendingImage(),
                            'audio_url' => null,
                            'content' => $dirBody,
                            'options' => [],
                            'correct' => null,
                            'difficulty' => 'UNSET',
                            'explanation' => null,
                            '_vn_lines' => [],
                            '_vn_options' => [],
                            '_in_vn_block' => false,
                            '_in_explanation' => false,
                        ];
                        $currentStimulus = null;
                        $capturingStimulus = ($currentPart === 'SPK_T4');
                    } else {
                        $currentQuestion['content'] = $dirBody;
                    }

                    continue;
                }

                $inDirectionsBlock = true;

                continue;
            }

            // Nhận diện khối Transcript ▲ (bản dịch tiếng Việt của bài đọc) nằm trên Questions X-Y ở Reading
            if ($activeSkill === 'reading' && preg_match('/^Transcript\s*(?:▲|\:)?\s*(.*)$/iu', $line, $tm)) {
                $flushQuestion();
                $capturingStimulus = false;
                $inDirectionsBlock = false;
                $inReadingTranscriptBlock = true;
                $firstBody = trim($tm[1]);
                $pendingReadingTranslation = $firstBody !== '' ? $firstBody : '';

                continue;
            }

            // 2. Nhận diện tiêu đề nhóm câu hỏi ("Questions 131-134...", "Q131-134", "Q149-150", "Q101-130")
            if (preg_match('/^(?:Questions|Câu|Q)\s*(\d{1,3})\s*[\-\–]\s*(\d{1,3})(?:\s+(?:refer\s+to|dựa\s+vào|liên\s+quan|sau\s+đây).*|:.*)?$/iu', $line, $m)) {
                $flushQuestion();
                $inReadingTranscriptBlock = false;
                $inDirectionsBlock = false;
                $rangeStart = (int) $m[1];
                $rangeEnd = (int) $m[2];

                // Dải Q101-130 là tiêu đề Part 5 câu đơn (không phải nhóm stimulus)
                if ($rangeStart === 101 && $rangeEnd === 130) {
                    if ($forcedPart === null) {
                        $activeSkill = 'reading';
                        $currentPart = 'PART5';
                        $hasExplicitPartHeader = true;
                    }
                    $currentGroupKey = null;
                    $currentGroupTitle = null;
                    $currentGroupRangeStart = null;
                    $currentGroupRangeEnd = null;
                    $currentStimulus = null;
                    $capturingStimulus = false;

                    continue;
                }

                if ($forcedPart === null && $activeSkill === 'speaking') {
                    $currentPart = $this->inferSpeakingPartByNumber($rangeStart);
                    $hasExplicitPartHeader = true;
                } elseif ($forcedPart === null && $activeSkill === 'writing') {
                    $currentPart = $this->inferWritingPartByNumber($rangeStart);
                    $hasExplicitPartHeader = true;
                } elseif ($forcedPart === null && ($activeSkill === 'reading' || $rangeStart >= 131)) {
                    $activeSkill = 'reading';
                    if ($rangeStart >= 131 && $rangeStart <= 146) {
                        $currentPart = 'PART6';
                    } elseif ($rangeStart >= 147) {
                        $currentPart = 'PART7';
                    }
                    $hasExplicitPartHeader = true;
                }

                $currentGroupRangeStart = $rangeStart;
                $currentGroupRangeEnd = $rangeEnd;
                $currentGroupKey = "range_{$rangeStart}_{$rangeEnd}";
                $currentGroupTitle = trim($line);
                $currentGroupImageUrl = $shiftPendingImage();
                $currentStimulus = null;
                $capturingStimulus = true;

                continue;
            }

            // Nếu đang thu thập khối dịch Transcript ▲ trước Questions X-Y ở Reading
            if ($inReadingTranscriptBlock) {
                $pendingReadingTranslation = ($pendingReadingTranslation === null || $pendingReadingTranslation === '')
                    ? $line
                    : ($pendingReadingTranslation."\n".$line);

                continue;
            }

            // 2b. Nhận diện Đoạn văn / Ngữ cảnh chung / Email / Lịch trình / Transcript (Listening/Speaking/Writing)
            if (preg_match('/^(?:Đoạn\s*văn|Ngữ\s*cảnh|Bài\s*đọc|Tình\s*huống|Thông\s*tin|Bảng\s*thông\s*tin|Lịch\s*trình|Passage|Stimulus|Transcript|Audio\s*Script|Text\s*to\s*read)\s*[:\.]?\s*(.*)$/iu', $line, $m)) {
                $flushQuestion();
                $inDirectionsBlock = false;
                $stimContent = trim($m[1]);
                $currentStimulus = $stimContent !== '' ? $stimContent : '';
                $capturingStimulus = true;
                if ($currentGroupKey === null && in_array($currentPart, array_merge(self::GROUPED_PART_CODES, ['SPK_T3', 'SPK_T4']), true)) {
                    $currentGroupKey = 'stim_'.md5($currentPart.'|'.$line.'|'.count($questions));
                }

                continue;
            }

            // Nhận diện dòng tình huống "Imagine that..." (Speaking Task 3) hoặc tiêu đề Email "From: ..." (Writing Task 2)
            if ($activeSkill === 'speaking' && $currentPart === 'SPK_T3' && $currentQuestion === null && preg_match('/^Imagine\s+that\b/iu', $line)) {
                $inDirectionsBlock = false;
                $currentStimulus = ($currentStimulus === null || $currentStimulus === '') ? $line : ($currentStimulus."\n".$line);
                $capturingStimulus = true;
                if ($currentGroupKey === null) {
                    $currentGroupKey = 'spk3_'.md5($line.'|'.count($questions));
                }

                continue;
            }

            if (($activeSkill === 'writing' || $currentPart === 'WRITING_T2') && preg_match('/^(?:From|To|Subject|Sent|Date)\s*:/iu', $line)) {
                $inDirectionsBlock = false;
                $capturingStimulus = true;
                $currentStimulus = ($currentStimulus === null || $currentStimulus === '') ? $line : ($currentStimulus."\n".$line);
                if ($currentQuestion !== null && $currentQuestion['part_code'] === 'WRITING_T2') {
                    $currentQuestion['stimulus_text'] = $currentStimulus;
                }

                continue;
            }

            // 3. Nhận diện đáp án đúng: "Đáp án: B", "Answer: B", "[ANS: B]"
            if ($currentQuestion !== null && preg_match('/^(?:Đáp\s*án(?:\s*đúng)?|Đ\/A|Answer|Key|Correct|\[ANS)\s*[:\-]\s*\(?([A-D])\)?\]?/iu', $line, $m)) {
                $currentQuestion['correct'] = strtoupper($m[1]);
                $currentQuestion['_has_explicit_answer'] = true;

                continue;
            }

            // 4. Nhận diện Độ khó
            if ($currentQuestion !== null && preg_match('/^(?:Độ\s*khó|Mức\s*độ|Difficulty)\s*[:\-]\s*(EASY|MEDIUM|HARD|DỄ|TRUNG\s*BÌNH|KHÓ)/iu', $line, $m)) {
                $rawDiff = mb_strtoupper(trim($m[1]));
                $currentQuestion['difficulty'] = match ($rawDiff) {
                    'EASY', 'DỄ' => 'EASY',
                    'HARD', 'KHÓ' => 'HARD',
                    default => 'MEDIUM',
                };

                continue;
            }

            // 5. Nhận diện Giải thích chi tiết ▲ / Bài mẫu / Answers (Writing) / ➢ Chọn ...
            if ($currentQuestion !== null && (
                preg_match('/^(?:Giải\s*thích(?:\s*chi\s*tiết)?|Explanation|Bài\s*mẫu(?:\s*tham\s*khảo)?|Bài\s*nói\s*mẫu|Bài\s*viết\s*mẫu|Gợi\s*ý(?:\s*trả\s*lời|\s*triển\s*khai)?|Dàn\s*ý|Sample\s*(?:Answer|Response|Essay)|Model\s*Answer|Answers?)\s*(?:▲|[:\-])?\s*(.*)$/iu', $line, $m)
                || preg_match('/^(?:➢|➤)\s*(.+)$/u', $line)
            )) {
                $currentQuestion['_in_explanation'] = true;
                $expBody = isset($m[1]) ? trim($m[1]) : trim($line);
                if ($expBody !== '' && $expBody !== '▲') {
                    $currentQuestion['explanation'] = $this->appendExplanation($currentQuestion['explanation'] ?? null, $expBody);
                }

                continue;
            }

            // Kiểm tra câu hiện tại có phải kỹ năng Tự luận (Speaking / Writing) hay không
            $currentIsProductive = $currentQuestion !== null && in_array($currentQuestion['part_code'], self::PRODUCTIVE_PART_CODES, true);
            $maxOptionsForCurrent = ($currentQuestion !== null && $currentQuestion['part_code'] === 'PART2') ? 3 : 4;

            // Nếu đang ở khu vực Part 1 mà gặp thẳng phương án A./(A)/→ khi chưa có dòng stem -> tự khởi tạo câu Part 1
            $looksLikeOptionLine = preg_match('/^(\*?)\(?([A-D])[\.\)\:]\s*(.+)$/u', $line) === 1
                || preg_match('/^(?:→|->|=>|•|\-|\–)\s*(.+)$/u', $line) === 1;
            if ($currentQuestion === null && $looksLikeOptionLine && ($currentPart === 'PART1' || $forcedPart === 'PART1' || ! empty($pendingImageQueue))) {
                $inlineImg = $shiftPendingImage();
                $inlineAud = $pendingAudioUrl;
                $pendingAudioUrl = null;

                $currentQuestion = [
                    'question_number' => count($questions) + 1,
                    'part_code' => 'PART1',
                    'group_key' => null,
                    'group_title' => null,
                    'stimulus_text' => null,
                    'audio_transcript' => null,
                    'image_url' => $inlineImg,
                    'audio_url' => $inlineAud,
                    'content' => 'Look at the picture and listen to the four statements.',
                    'options' => [],
                    'correct' => 'A',
                    'difficulty' => 'UNSET',
                    'explanation' => null,
                    '_vn_lines' => [],
                    '_vn_options' => [],
                    '_in_vn_block' => false,
                    '_in_explanation' => false,
                ];
                $currentIsProductive = false;
                $maxOptionsForCurrent = 4;
            }

            // 6. Nhiều phương án trên cùng 1 dòng (chỉ áp dụng cho L&R)
            if (! $currentIsProductive && $currentQuestion !== null && preg_match_all('/(?:^|\s)\*?(\(?[A-D]\)?[\.\)\:])\s*([^\(\n]+?)(?=\s+\*?\(?[B-D]\)?[\.\)\:]|\s*$)/u', $line, $matches, PREG_SET_ORDER) && count($matches) >= 2) {
                $this->assignMultipleOptionsFromMatches($currentQuestion, $matches, $maxOptionsForCurrent);

                continue;
            }

            // 7. Từng phương án riêng dòng dạng A., (A), A), A: (chỉ áp dụng cho L&R)
            if (! $currentIsProductive && $currentQuestion !== null && preg_match('/^(\*?)\(?([A-D])[\.\)\:]\s*(.+)$/u', $line, $m)) {
                $label = strtoupper($m[2]);
                $optText = trim($m[3]);
                $hasStar = $m[1] === '*';
                $this->assignSingleLetterOption($currentQuestion, $label, $optText, $hasStar, $maxOptionsForCurrent);

                continue;
            }

            // 7b. Từng phương án riêng dòng dạng mũi tên / gạch đầu dòng: →, ->, =>, •, -, – (chỉ áp dụng cho L&R)
            if (! $currentIsProductive && $currentQuestion !== null && preg_match('/^(?:→|->|=>|•|\-|\–)\s*(.+)$/u', $line, $m)) {
                $itemText = trim($m[1]);
                if (preg_match('/^(\*?)\(?([A-D])[\.\)\:]\s*(.+)$/u', $itemText, $subMatch)) {
                    $this->assignSingleLetterOption(
                        $currentQuestion,
                        strtoupper($subMatch[2]),
                        trim($subMatch[3]),
                        $subMatch[1] === '*',
                        $maxOptionsForCurrent
                    );
                } else {
                    $this->assignArrowOrBulletOption($currentQuestion, $itemText, $maxOptionsForCurrent);
                }

                continue;
            }

            // 8. Dòng bắt đầu Câu hỏi mới ("Question 31 : 131 ...", "Question 5 : 105.", "Câu 1:", "Question 99:", "99.", "Q1:")
            $isDualQuestion = preg_match('/^(?:Câu(?:\s*hỏi)?|Question|Q)\s*\d{1,3}\s*[:\-]\s*(\d{3})\s*[:.\-\)]?\s*(.*)$/iu', $line, $dm) === 1;
            $isStandardQuestion = ! $isDualQuestion && preg_match('/^(?:(?:Câu(?:\s*hỏi)?|Question|Q)\s*(\d{1,3})\s*[:.\-\)]?|(\d{1,3})[\.\)])\s*(.*)$/iu', $line, $m) === 1;

            // Bảo vệ chống nhận nhầm danh sách đánh số nhỏ (1., 2., 3.) bên trong đoạn văn/bảng của Part 3, 4, 6, 7
            if (
                $isStandardQuestion
                && $capturingStimulus
                && $currentQuestion === null
                && $currentGroupRangeStart !== null
                && $currentGroupRangeStart >= 32
                && preg_match('/^(?:Câu(?:\s*hỏi)?|Question|Q)\b/iu', $line) !== 1
            ) {
                $bulletNum = (int) ($m[1] !== '' ? $m[1] : $m[2]);
                if ($bulletNum < $currentGroupRangeStart) {
                    $isStandardQuestion = false;
                }
            }

            if ($isDualQuestion || $isStandardQuestion) {
                if ($isDualQuestion) {
                    $qNum = (int) $dm[1];
                    $stem = trim($dm[2]);
                } else {
                    $qNum = (int) ($m[1] !== '' ? $m[1] : $m[2]);
                    $stem = trim($m[3]);
                    if (preg_match('/^(\d{1,3})[\.\)]\s*(.+)$/u', $stem, $nestedNum)) {
                        $qNum = (int) $nestedNum[1];
                        $stem = trim($nestedNum[2]);
                    }
                }

                $isBarePart1Header = $stem === '' && ($currentPart === 'PART1' || $forcedPart === 'PART1' || ! empty($pendingImageQueue));
                $isBareReadingDual = $stem === '' && ($isDualQuestion || $activeSkill === 'reading');
                $isBareWritingTask = $stem === '' && $activeSkill === 'writing';
                $isExplicitBareHeader = $stem === '' && preg_match('/^(?:Câu(?:\s*hỏi)?|Question|Q)\s*\d{1,3}\s*[:.\-\)]?$/iu', $line) === 1;

                if ($stem === '' && ! $isBarePart1Header && ! $isBareReadingDual && ! $isBareWritingTask && ! $isExplicitBareHeader) {
                    continue;
                }
                if ($isBarePart1Header) {
                    $stem = 'Look at the picture and listen to the four statements.';
                }

                // Nếu ngay sau câu tiếng Anh là dòng đánh số chứa bản dịch tiếng Việt -> gộp inline!
                if (
                    $currentQuestion !== null
                    && ! $this->isVietnameseText((string) $currentQuestion['content'])
                    && $stem !== ''
                    && $this->isVietnameseText($stem)
                    && (
                        $qNum === (int) ($currentQuestion['question_number'] ?? -1)
                        || $currentIsProductive
                        || count($currentQuestion['options']) >= $maxOptionsForCurrent
                    )
                ) {
                    $currentQuestion['_in_vn_block'] = true;
                    $currentQuestion['_vn_lines'][] = $stem;

                    continue;
                }

                $wasWritingT2Question = $currentQuestion !== null && ($currentQuestion['part_code'] ?? '') === 'WRITING_T2';
                $flushQuestion();
                $capturingStimulus = false;
                $inDirectionsBlock = false;
                $inReadingTranscriptBlock = false;

                $inlineImageUrl = null;
                if (preg_match('/\[IMAGE_URL:\s*(.+?)\]/i', $stem, $im)) {
                    $inlineImageUrl = $this->normalizeRelativeMediaPath($im[1]);
                    $stem = trim(str_replace($im[0], '', $stem));
                } else {
                    $inlineImageUrl = $shiftPendingImage();
                }

                $inlineAudioUrl = $pendingAudioUrl;
                if (preg_match('/\[AUDIO_URL:\s*(.+?)\]/i', $stem, $am)) {
                    $inlineAudioUrl = $this->normalizeRelativeMediaPath($am[1]);
                    $stem = trim(str_replace($am[0], '', $stem));
                }
                $pendingAudioUrl = null;

                // Nếu câu mới nằm ngoài phạm vi range nhóm cũ, hoặc chuyển sang câu mới trong WRITING_T2 (Question 6 -> Question 7), reset ngữ cảnh cũ
                $shouldResetGroup = ($currentGroupRangeStart !== null && $currentGroupRangeEnd !== null && ($qNum < $currentGroupRangeStart || $qNum > $currentGroupRangeEnd))
                    || $wasWritingT2Question;
                if ($shouldResetGroup) {
                    $currentGroupKey = null;
                    $currentGroupTitle = null;
                    $currentGroupRangeStart = null;
                    $currentGroupRangeEnd = null;
                    $currentGroupImageUrl = null;
                    $currentStimulus = null;
                    $capturingStimulus = ($currentPart === 'SPK_T4');
                    $pendingReadingTranslation = null;
                }

                $inferredPart = $this->resolvePartCodeForQuestion($qNum, $currentPart, $activeSkill, $forcedPart, $hasExplicitPartHeader);
                if ($scope === 'FULL_LR' && $qNum >= 101 && $qNum <= 200) {
                    $activeSkill = 'reading';
                }
                $isListening = in_array($inferredPart, ['PART1', 'PART2', 'PART3', 'PART4'], true);
                $isProductive = in_array($inferredPart, self::PRODUCTIVE_PART_CODES, true);
                $effectiveImageUrl = $inlineImageUrl ?: $currentGroupImageUrl;
                $cleanStimulus = ($currentStimulus !== null && trim($currentStimulus) !== '') ? trim($currentStimulus) : null;
                $readingTranslation = (! $isListening && $pendingReadingTranslation !== null && trim($pendingReadingTranslation) !== '')
                    ? trim($pendingReadingTranslation)
                    : null;

                $currentQuestion = [
                    'question_number' => $qNum,
                    'part_code' => $inferredPart,
                    'group_key' => in_array($inferredPart, array_merge(self::GROUPED_PART_CODES, ['SPK_T3', 'SPK_T4']), true) ? $currentGroupKey : null,
                    'group_title' => $currentGroupTitle,
                    'stimulus_text' => $isListening ? null : $cleanStimulus,
                    'audio_transcript' => $isListening ? $cleanStimulus : $readingTranslation,
                    'image_url' => $effectiveImageUrl,
                    'audio_url' => $inlineAudioUrl,
                    'content' => $stem,
                    'options' => [],
                    'correct' => $isProductive ? null : 'A',
                    'difficulty' => 'UNSET',
                    'explanation' => null,
                    '_vn_lines' => [],
                    '_vn_options' => [],
                    '_in_vn_block' => false,
                    '_in_explanation' => false,
                ];

                continue;
            }

            // Thoát khối Directions/Hướng dẫn chung khi gặp dòng nội dung tiếng Anh
            if (
                $inDirectionsBlock
                && $currentQuestion === null
                && ! $this->isVietnameseText($line)
                && ! preg_match('/^Directions?\s*[:\-]/i', $line)
            ) {
                $inDirectionsBlock = false;
                if (in_array($currentPart, ['SPK_T3', 'SPK_T4', 'WRITING_T2'], true)) {
                    $capturingStimulus = true;
                    $currentStimulus = ($currentStimulus === null || $currentStimulus === '')
                        ? $line
                        : ($currentStimulus."\n".$line);

                    continue;
                }
            }

            // 8b. Speaking Task 4 (SPK_T4 - Câu 8, 9, 10 không đánh số): các câu hỏi tiếng Anh độc lập
            if (
                $currentPart === 'SPK_T4'
                && ! $inDirectionsBlock
                && ($currentStimulus !== null || $currentGroupImageUrl !== null || ! empty($pendingImageQueue) || $currentQuestion !== null)
                && ! $this->isVietnameseText($line)
                && (
                    preg_match('/\?\s*$/u', $line) === 1
                    || preg_match('/^(?:Please\s+tell\s+me|Could\s+you|Can\s+you|Would\s+you|I\s+would\s+like\s+to\s+know)/i', $line) === 1
                )
                && ($currentQuestion === null || empty($currentQuestion['_in_explanation']))
            ) {
                $flushQuestion();
                $capturingStimulus = false;
                if ($currentGroupImageUrl === null) {
                    $currentGroupImageUrl = $shiftPendingImage();
                }
                if ($currentGroupKey === null) {
                    $currentGroupKey = 'spk4_'.md5((string) ($currentStimulus ?? $currentGroupImageUrl ?? 'spk4'));
                }
                $autoSpkCounter = $autoSpkCounter < 8 ? 8 : ($autoSpkCounter + 1);
                $currentQuestion = [
                    'question_number' => $autoSpkCounter,
                    'part_code' => 'SPK_T4',
                    'group_key' => $currentGroupKey,
                    'group_title' => $currentGroupTitle ?? 'Speaking Task 4 Information',
                    'stimulus_text' => $currentStimulus !== null ? trim($currentStimulus) : null,
                    'audio_transcript' => null,
                    'image_url' => $currentGroupImageUrl,
                    'audio_url' => null,
                    'content' => $line,
                    'options' => [],
                    'correct' => null,
                    'difficulty' => 'UNSET',
                    'explanation' => null,
                    '_vn_lines' => [],
                    '_vn_options' => [],
                    '_in_vn_block' => false,
                    '_in_explanation' => false,
                ];

                continue;
            }

            // 8c. Writing Task 3 (Opinion Essay) khi đoạn đề bài luận xuất hiện ngay sau tiêu đề Opinion Essay (không ghi Question 8:)
            if (
                $currentPart === 'WRITING_T3'
                && $currentQuestion === null
                && ! $inDirectionsBlock
                && ! $this->isVietnameseText($line)
                && mb_strlen($line) >= 25
            ) {
                $currentQuestion = [
                    'question_number' => 8,
                    'part_code' => 'WRITING_T3',
                    'group_key' => null,
                    'group_title' => null,
                    'stimulus_text' => null,
                    'audio_transcript' => null,
                    'image_url' => null,
                    'audio_url' => null,
                    'content' => $line,
                    'options' => [],
                    'correct' => null,
                    'difficulty' => 'UNSET',
                    'explanation' => null,
                    '_vn_lines' => [],
                    '_vn_options' => [],
                    '_in_vn_block' => false,
                    '_in_explanation' => false,
                ];

                continue;
            }

            // 9. Đang thu thập nhiều dòng của Đoạn văn / Email / Bảng biểu chung trước khi vào câu hỏi
            if ($capturingStimulus && $currentQuestion === null) {
                $currentStimulus = ($currentStimulus === null || $currentStimulus === '')
                    ? $line
                    : ($currentStimulus."\n".$line);

                continue;
            }

            // Nếu đang ở khối Directions/Hướng dẫn chung -> bỏ qua nếu chưa vào câu hỏi
            if ($inDirectionsBlock && $currentQuestion === null) {
                continue;
            }

            // 10. Đang nối tiếp phần giải thích / bài mẫu nhiều dòng
            if ($currentQuestion !== null && ! empty($currentQuestion['_in_explanation'])) {
                $currentQuestion['explanation'] = $this->appendExplanation($currentQuestion['explanation'] ?? null, $line);

                continue;
            }

            // 11. Dòng dịch tiếng Việt không đánh số nằm ngay dưới câu tiếng Anh đã đọc xong phương án (hoặc dưới đề Speaking/Writing)
            if (
                $currentQuestion !== null
                && ! $this->isVietnameseText((string) $currentQuestion['content'])
                && $this->isVietnameseText($line)
                && ($currentIsProductive || count($currentQuestion['options']) >= $maxOptionsForCurrent || ! empty($currentQuestion['_in_vn_block']))
            ) {
                $currentQuestion['_in_vn_block'] = true;
                $cleanVn = preg_replace('/^(?:Dịch(?:\s+đề\s+bài|\s+câu\s+hỏi|\s+nghĩa)?|Tạm\s+dịch)\s*[:\-]\s*/iu', '', $line);
                $currentQuestion['_vn_lines'][] = trim((string) $cleanVn);

                continue;
            }

            // 12. Nối tiếp nội dung đề bài nhiều dòng (chưa vào options và chưa vào phần dịch)
            if ($currentQuestion !== null && empty($currentQuestion['options']) && $currentQuestion['explanation'] === null && empty($currentQuestion['_in_vn_block'])) {
                $currentQuestion['content'] = trim($currentQuestion['content']) === ''
                    ? $line
                    : ($currentQuestion['content']."\n".$line);
            }
        }

        $flushQuestion();

        // Hậu xử lý gộp cặp câu song ngữ Anh – Việt nếu vẫn còn cặp tách rời
        $questions = $this->mergeBilingualQuestions($questions);

        // Map bảng Answer Key cuối file (nếu có) vào đúng question_number cho L&R
        if (! empty($answerKeyMap)) {
            foreach ($questions as $idx => $q) {
                if (in_array($q['part_code'] ?? '', self::PRODUCTIVE_PART_CODES, true)) {
                    continue;
                }
                $qNum = (int) ($q['question_number'] ?? ($idx + 1));
                if (isset($answerKeyMap[$qNum])) {
                    $questions[$idx]['correct'] = $answerKeyMap[$qNum];
                }
            }
        }

        return $questions;
    }

    /**
     * Bộ hậu xử lý gộp câu hỏi song ngữ Anh – Việt:
     * Nếu câu liền sau là bản dịch tiếng Việt của câu tiếng Anh ngay trước đó,
     * gộp toàn bộ nội dung dịch + phương án dịch vào cột explanation của câu tiếng Anh.
     *
     * @param  list<array<string, mixed>>  $questions
     * @return list<array<string, mixed>>
     */
    public function mergeBilingualQuestions(array $questions): array
    {
        $merged = [];
        $count = count($questions);

        for ($i = 0; $i < $count; $i++) {
            $curr = $questions[$i];

            if (! empty($merged)) {
                $prevIndex = count($merged) - 1;
                $prev = $merged[$prevIndex];

                $prevIsEnglish = ! $this->isVietnameseText((string) ($prev['content'] ?? ''));
                $currIsVietnamese = $this->isVietnameseText((string) ($curr['content'] ?? ''));

                if ($prevIsEnglish && $currIsVietnamese) {
                    $prevOpts = is_array($prev['options'] ?? null) ? $prev['options'] : [];
                    $currOpts = is_array($curr['options'] ?? null) ? $curr['options'] : [];
                    $sameQuestionNumber = isset($prev['question_number'], $curr['question_number'])
                        && (int) $prev['question_number'] === (int) $curr['question_number'];
                    $matchingOptionsCount = ! empty($prevOpts) && count($prevOpts) === count($currOpts);

                    if ($sameQuestionNumber || $matchingOptionsCount || empty($currOpts)) {
                        $partCode = strtoupper((string) ($prev['part_code'] ?? 'PART5'));
                        $formattedVn = $this->formatVietnameseTranslation((string) ($curr['content'] ?? ''), $currOpts, $partCode);
                        $merged[$prevIndex]['explanation'] = $this->appendExplanation($prev['explanation'] ?? null, $formattedVn);

                        // Nếu bản dịch tiếng Việt vô tình giữ ảnh của câu graphic tiếp theo, chuyển ảnh đúng chỗ
                        if (! empty($curr['image_url'])) {
                            $next = $questions[$i + 1] ?? null;
                            if ($next !== null && empty($next['image_url']) && $this->mentionsVisualOrGraphic((string) ($next['content'] ?? ''))) {
                                $questions[$i + 1]['image_url'] = $curr['image_url'];
                            } elseif (empty($merged[$prevIndex]['image_url'])) {
                                $merged[$prevIndex]['image_url'] = $curr['image_url'];
                            }
                        }

                        continue;
                    }
                }
            }

            $merged[] = $curr;
        }

        return $merged;
    }

    /**
     * Alias lưu câu hỏi từ danh sách phẳng (dùng bởi AiToeicController).
     *
     * @param  list<array<string, mixed>>  $flatQuestions
     * @return array{created: int, question_ids: list<int>, attached: int}
     */
    public function importQuestions(
        array $flatQuestions,
        string $defaultPartCode = 'PART5',
        string $publishStatus = 'ACTIVE',
        ?int $targetTestId = null
    ): array {
        app(TestFormService::class)->ensureStandardSectionsExist();
        $defaultSectionId = ExamSection::query()->where('code', strtoupper($defaultPartCode))->value('section_id');

        $ids = $this->persistQuestionsAndReturnIds(
            $flatQuestions,
            $publishStatus,
            $defaultSectionId ? (int) $defaultSectionId : null,
            $targetTestId
        );

        return [
            'created' => count($ids),
            'question_ids' => $ids,
            'attached' => $targetTestId !== null ? count($ids) : 0,
        ];
    }

    /**
     * Lưu danh sách câu hỏi đã parse vào CSDL (QuestionSet + Question + AnswerOption cho L&R, bỏ AnswerOption cho Speaking/Writing).
     *
     * @param  list<array<string, mixed>>  $parsedQuestions
     * @return list<int>
     */
    public function persistQuestionsAndReturnIds(
        array $parsedQuestions,
        string $publishStatus = 'ACTIVE',
        ?int $defaultSectionId = null,
        ?int $targetTestId = null
    ): array {
        if (empty($parsedQuestions)) {
            return [];
        }

        app(TestFormService::class)->ensureStandardSectionsExist();

        $sectionsByCode = ExamSection::query()->pluck('section_id', 'code');
        $fallbackSectionId = $defaultSectionId
            ?: ($sectionsByCode['PART5'] ?? ExamSection::query()->value('section_id') ?? 1);

        $importedQuestionIds = [];

        DB::transaction(function () use (
            $parsedQuestions,
            $publishStatus,
            $sectionsByCode,
            $fallbackSectionId,
            $targetTestId,
            &$importedQuestionIds
        ): void {
            $stimulusSets = [];

            foreach ($parsedQuestions as $item) {
                $partCode = strtoupper((string) ($item['part_code'] ?? 'PART5'));
                $sectionId = (int) ($sectionsByCode[$partCode] ?? $fallbackSectionId);
                $questionSetId = null;

                $stimulusText = isset($item['stimulus_text']) ? trim((string) $item['stimulus_text']) : '';
                $imageUrl = isset($item['image_url']) ? $this->normalizeRelativeMediaPath((string) $item['image_url']) : '';
                $audioUrl = isset($item['audio_url']) ? $this->normalizeRelativeMediaPath((string) $item['audio_url']) : '';
                $transcript = isset($item['audio_transcript']) ? trim((string) $item['audio_transcript']) : '';
                $groupKey = isset($item['group_key']) ? trim((string) $item['group_key']) : '';
                $groupTitle = isset($item['group_title']) ? trim((string) $item['group_title']) : '';

                $isGroupedPart = in_array($partCode, self::GROUPED_PART_CODES, true);
                $isProductive = in_array($partCode, self::PRODUCTIVE_PART_CODES, true);
                $hasSharedContext = $stimulusText !== '' || $imageUrl !== '' || $audioUrl !== '' || $transcript !== '' || ($isGroupedPart && $groupKey !== '');

                if ($hasSharedContext) {
                    if ($isGroupedPart && $groupKey !== '') {
                        $hash = md5($partCode.'|group|'.$groupKey);
                    } elseif ($isGroupedPart) {
                        $hash = md5($partCode.'|'.$stimulusText.'|'.$audioUrl.'|'.$transcript.'|'.$imageUrl);
                    } else {
                        $hash = md5($partCode.'|'.uniqid('single_', true));
                    }

                    $mediaUrl = $imageUrl !== '' ? $imageUrl : null;
                    $savedAudioUrl = $audioUrl !== '' ? $audioUrl : null;
                    $mediaType = $imageUrl !== '' ? 'IMAGE' : ($savedAudioUrl !== null || $transcript !== '' ? 'AUDIO' : 'READING_PASSAGE');

                    if (! isset($stimulusSets[$hash])) {
                        $set = QuestionSet::query()->create([
                            'title' => $groupTitle !== '' ? Str::limit($groupTitle, 250, '') : 'Ngữ cảnh '.$partCode,
                            'stimulus_text' => $stimulusText !== '' ? $stimulusText : null,
                            'media_url' => $mediaUrl,
                            'audio_url' => $savedAudioUrl,
                            'media_type' => $mediaType,
                            'transcript' => $transcript !== '' ? $transcript : null,
                        ]);
                        $stimulusSets[$hash] = $set->question_set_id;
                    } elseif ($mediaUrl !== null || $savedAudioUrl !== null) {
                        $existingSet = QuestionSet::query()->find($stimulusSets[$hash]);
                        if ($existingSet) {
                            $updates = [];
                            if (empty($existingSet->media_url) && $mediaUrl !== null) {
                                $updates['media_url'] = $mediaUrl;
                                $updates['media_type'] = $mediaType;
                            }
                            if (empty($existingSet->audio_url) && $savedAudioUrl !== null) {
                                $updates['audio_url'] = $savedAudioUrl;
                            }
                            if (! empty($updates)) {
                                $existingSet->update($updates);
                            }
                        }
                    }
                    $questionSetId = $stimulusSets[$hash];
                }

                $contentText = trim((string) ($item['content'] ?? ''));
                if ($contentText === '') {
                    $contentText = $partCode === 'PART1'
                        ? 'Look at the picture and choose the statement that best describes what you see.'
                        : 'Listen to the audio and select the best response.';
                }

                $difficulty = strtoupper((string) ($item['difficulty'] ?? 'UNSET'));
                if (! in_array($difficulty, ['EASY', 'MEDIUM', 'HARD'], true) || $difficulty === 'UNSET') {
                    $difficulty = AiToeicService::assessToeicDifficulty(
                        $item['skill'] ?? null,
                        $partCode,
                        $contentText,
                        $item['stimulus'] ?? ($item['passage_text'] ?? null),
                        is_array($item['options'] ?? null) ? $item['options'] : []
                    );
                }

                $question = Question::query()->create([
                    'section_id' => $sectionId,
                    'question_type_id' => Question::resolveQuestionTypeIdForPart($partCode),
                    'question_set_id' => $questionSetId,
                    'content' => $contentText,
                    'difficulty' => $difficulty,
                    'points' => 1.00,
                    'status' => $publishStatus,
                    'explanation' => $item['explanation'] ?? null,
                    'created_at' => now(),
                ]);

                // Tuyệt đối không tạo AnswerOption (A/B/C/D) cho Speaking & Writing
                if (! $isProductive) {
                    $correctLabel = strtoupper((string) ($item['correct'] ?? 'A'));
                    $options = is_array($item['options'] ?? null) ? $item['options'] : [];
                    $allowedLetters = $partCode === 'PART2' ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'D'];

                    foreach ($allowedLetters as $cleanLabel) {
                        if (! array_key_exists($cleanLabel, $options) || $options[$cleanLabel] === null) {
                            if ($partCode === 'PART1') {
                                AnswerOption::query()->create([
                                    'question_id' => $question->question_id,
                                    'label' => $cleanLabel,
                                    'content' => "Option {$cleanLabel}",
                                    'is_correct' => $cleanLabel === $correctLabel,
                                ]);
                            }

                            continue;
                        }

                        $cleanText = trim((string) $options[$cleanLabel]);
                        if ($cleanText === '' && $partCode !== 'PART1') {
                            continue;
                        }

                        AnswerOption::query()->create([
                            'question_id' => $question->question_id,
                            'label' => $cleanLabel,
                            'content' => $cleanText !== '' ? $cleanText : "Option {$cleanLabel}",
                            'is_correct' => $cleanLabel === $correctLabel,
                        ]);
                    }
                }

                $importedQuestionIds[] = $question->question_id;
            }

            $this->lastAttachResult = null;
            if ($targetTestId !== null && ! empty($importedQuestionIds)) {
                $testForm = TestForm::query()->find($targetTestId);
                if ($testForm !== null) {
                    $this->lastAttachResult = app(TestFormService::class)->attachQuestions($testForm, $importedQuestionIds);
                }
            }
        });

        return $importedQuestionIds;
    }

    /**
     * @param  list<array<string, mixed>>  $parsedQuestions
     */
    public function persistQuestions(
        array $parsedQuestions,
        string $publishStatus = 'ACTIVE',
        ?int $defaultSectionId = null,
        ?int $targetTestId = null
    ): int {
        return count($this->persistQuestionsAndReturnIds(
            $parsedQuestions,
            $publishStatus,
            $defaultSectionId,
            $targetTestId
        ));
    }

    /**
     * Chuyển danh sách câu hỏi phẳng sang JSON Schema chuẩn 4 kỹ năng của AI Parser.
     *
     * @param  list<array<string, mixed>>  $flatQuestions
     * @return array<string, mixed>
     */
    public function convertFlatQuestionsToStructuredJson(array $flatQuestions, ?string $examTitle = null): array
    {
        $partsMap = [];
        $detectedParts = [];

        foreach ($flatQuestions as $idx => $q) {
            $partCode = strtoupper((string) ($q['part_code'] ?? 'PART5'));
            $skill = $this->skillOfPartCode($partCode);
            $partNum = $this->partNumberOfCode($partCode);
            $isGrouped = in_array($partCode, self::GROUPED_PART_CODES, true);
            $isProductive = in_array($partCode, self::PRODUCTIVE_PART_CODES, true);

            if (! in_array($partCode, $detectedParts, true)) {
                $detectedParts[] = $partCode;
            }

            if (! isset($partsMap[$partCode])) {
                $partsMap[$partCode] = [
                    'skill' => $skill,
                    'part_code' => $partCode,
                    'part_number' => $partNum,
                    'groups' => [],
                ];
            }

            $explicitGroupKey = trim((string) ($q['group_key'] ?? ''));
            if ($isGrouped && $explicitGroupKey !== '') {
                $groupKey = $partCode.'|'.$explicitGroupKey;
            } elseif ($isGrouped && (! empty($q['stimulus_text']) || ! empty($q['audio_url']) || ! empty($q['audio_transcript']))) {
                $groupKey = md5($partCode.'|'.($q['stimulus_text'] ?? '').'|'.($q['audio_url'] ?? '').'|'.($q['audio_transcript'] ?? ''));
            } else {
                $groupKey = 'single_'.$idx;
            }

            if (! isset($partsMap[$partCode]['groups'][$groupKey])) {
                $groupIndex = count($partsMap[$partCode]['groups']) + 1;
                $partsMap[$partCode]['groups'][$groupKey] = [
                    'group_id' => $explicitGroupKey !== '' ? $explicitGroupKey : "g_{$partCode}_{$groupIndex}",
                    'group_title' => $q['group_title'] ?? null,
                    'is_grouped' => $isGrouped,
                    'passage_text' => $q['stimulus_text'] ?? null,
                    'passage_images' => ($isGrouped && ! empty($q['image_url'])) ? [$q['image_url']] : [],
                    'audio_url' => $q['audio_url'] ?? null,
                    'audio_transcript' => $q['audio_transcript'] ?? null,
                    'questions' => [],
                ];
            } elseif ($isGrouped && ! empty($q['image_url']) && ! in_array($q['image_url'], $partsMap[$partCode]['groups'][$groupKey]['passage_images'], true)) {
                $partsMap[$partCode]['groups'][$groupKey]['passage_images'][] = $q['image_url'];
            }

            $opts = is_array($q['options'] ?? null) ? $q['options'] : [];
            $formattedOptions = null;
            if (! $isProductive) {
                $formattedOptions = [
                    'A' => (string) ($opts['A'] ?? ''),
                    'B' => (string) ($opts['B'] ?? ''),
                    'C' => (string) ($opts['C'] ?? ''),
                    'D' => $partCode === 'PART2' ? null : (isset($opts['D']) ? (string) $opts['D'] : ''),
                ];
            }

            $partsMap[$partCode]['groups'][$groupKey]['questions'][] = [
                'question_number' => (int) ($q['question_number'] ?? ($idx + 1)),
                'question_text' => $q['content'] ?: null,
                'image_url' => ! $isGrouped ? ($q['image_url'] ?? null) : null,
                'options' => $formattedOptions,
                'correct_answer' => $isProductive ? null : ($q['correct'] ?? null),
                'explanation' => $q['explanation'] ?? null,
                'difficulty' => $q['difficulty'] ?? 'MEDIUM',
            ];
        }

        $parts = [];
        foreach ($partsMap as $partData) {
            $partData['groups'] = array_values($partData['groups']);
            $parts[] = $partData;
        }

        return [
            'exam_metadata' => [
                'title' => $examTitle,
                'detected_parts' => $detectedParts,
                'total_questions' => count($flatQuestions),
            ],
            'parts' => $parts,
        ];
    }

    /**
     * Chuyển từ Structured JSON Schema của AI Parser về mảng phẳng (bảo toàn đủ 15 part_code của 4 kỹ năng).
     *
     * @param  array<string, mixed>  $structured
     * @return list<array<string, mixed>>
     */
    public function flattenStructuredJson(array $structured): array
    {
        $flat = [];
        foreach (($structured['parts'] ?? []) as $part) {
            $partCode = strtoupper(trim((string) ($part['part_code'] ?? '')));
            if ($partCode === '' || ! in_array($partCode, self::FIXED_PART_CODES, true)) {
                $skill = strtolower((string) ($part['skill'] ?? 'reading'));
                $partNumber = (int) ($part['part_number'] ?? 5);
                if ($skill === 'speaking') {
                    $partCode = 'SPK_T'.max(1, min(5, $partNumber));
                } elseif ($skill === 'writing') {
                    $partCode = 'WRITING_T'.max(1, min(3, $partNumber));
                } else {
                    $partCode = 'PART'.max(1, min(7, $partNumber));
                }
            }

            $isProductive = in_array($partCode, self::PRODUCTIVE_PART_CODES, true);

            foreach (($part['groups'] ?? []) as $group) {
                $groupId = ! empty($group['is_grouped']) ? (string) ($group['group_id'] ?? '') : '';
                $groupTitle = $group['group_title'] ?? null;
                $passageText = $group['passage_text'] ?? null;
                $passageImages = is_array($group['passage_images'] ?? null) ? $group['passage_images'] : [];
                $groupAudioUrl = $group['audio_url'] ?? null;
                $groupTranscript = $group['audio_transcript'] ?? null;

                foreach (($group['questions'] ?? []) as $q) {
                    $cleanOpts = [];
                    if (! $isProductive && is_array($q['options'] ?? null)) {
                        $allowedLetters = $partCode === 'PART2' ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'D'];
                        foreach ($allowedLetters as $letter) {
                            if (array_key_exists($letter, $q['options']) && $q['options'][$letter] !== null) {
                                $cleanOpts[$letter] = (string) $q['options'][$letter];
                            }
                        }
                    }

                    $flat[] = [
                        'question_number' => $q['question_number'] ?? null,
                        'part_code' => $partCode,
                        'group_key' => $groupId !== '' ? $groupId : null,
                        'group_title' => $groupTitle,
                        'stimulus_text' => $passageText,
                        'image_url' => $q['image_url'] ?? ($passageImages[0] ?? null),
                        'audio_url' => $groupAudioUrl,
                        'audio_transcript' => $groupTranscript,
                        'content' => (string) ($q['question_text'] ?? ''),
                        'options' => $cleanOpts,
                        'correct' => $isProductive ? null : strtoupper((string) ($q['correct_answer'] ?? 'A')),
                        'difficulty' => strtoupper((string) ($q['difficulty'] ?? 'MEDIUM')),
                        'explanation' => $q['explanation'] ?? null,
                    ];
                }
            }
        }

        return $flat;
    }

    /**
     * Sinh nội dung nhị phân của file Word (.docx) mẫu chuẩn 4 kỹ năng TOEIC ETS (all / listening / reading / speaking / writing).
     */
    public function generateSampleDocxBinary(?string $skill = 'all'): string
    {
        $skillKey = strtolower(trim((string) $skill));

        $listeningLines = [
            '[Listening]',
            '[Part 2]',
            'Câu 7: Where did you leave the keys to the conference room?',
            '(A) Yes, at two o clock.',
            '(B) On the reception desk.',
            '(C) No, I have not seen him.',
            'Bạn đã để chìa khóa phòng họp ở đâu?',
            '→ Đúng vậy, lúc hai giờ.',
            '→ Ở trên bàn lễ tân.',
            '→ Không, tôi chưa gặp anh ấy.',
            'Đáp án: B',
            '[Part 4]',
            'Questions 98-100 refer to the following talk.',
            'Transcript: Thank you for attending today\'s gardening workshop. Before you leave, please sign up for our monthly mailing list at the front desk.',
            '98. What is the topic of today\'s lecture?',
            'A. When to harvest crops',
            'B. Where to plant trees',
            'C. How to grow vegetables',
            'D. Which flowers need more sun',
            'Chủ đề của bài giảng hôm nay là gì?',
            'A. Khi nào thu hoạch cây trồng',
            'B. Nơi trồng cây',
            'C. Cách trồng rau',
            'D. Loài hoa nào cần nhiều nắng hơn',
            'Đáp án: C',
            '99. What does the speaker encourage the listeners to do?',
            'A. Turn off mobile phones',
            'B. Have some refreshments',
            'C. Purchase some seeds',
            'D. Sign up for a mailing list',
            'Người nói khuyến khích người nghe làm gì?',
            'A. Tắt điện thoại di động',
            'B. Dùng đồ giải khát',
            'C. Mua hạt giống',
            'D. Đăng ký danh sách gửi thư',
            'Đáp án: D',
        ];

        $readingLines = [
            '[Reading]',
            '[Part 5]',
            'Câu 101: Ms. Tanaka requested that all department heads submit their budget proposals ------- Friday.',
            'A. until',
            'B. before',
            'C. since',
            'D. toward',
            'Đáp án: B',
            'Độ khó: MEDIUM',
            'Giải thích: Vị trí cần điền đứng trước mốc thời gian Friday, chọn giới từ "before".',
            '[Part 7]',
            'Questions 147-148 refer to the following notice.',
            'Đoạn văn: To: All Staff Members',
            'Subject: Scheduled Office Renovations',
            'Please be advised that the fifth-floor meeting rooms will undergo routine painting and carpet replacement next Monday.',
            '147. What is the purpose of the notice?',
            'A. To announce a company holiday',
            'B. To inform staff about scheduled maintenance',
            'C. To welcome new employees',
            'D. To request budget proposals',
            'Đáp án: B',
            '148. Which area will be affected next Monday?',
            'A. The main lobby',
            'B. The fifth-floor meeting rooms',
            'C. The employee cafeteria',
            'D. The parking garage',
            'Đáp án: B',
        ];

        $speakingLines = [
            '[Speaking]',
            '[Speaking Task 1: Read a Text Aloud]',
            'Đoạn văn: Welcome aboard Flight 408 with nonstop service to Seattle. Please fasten your seat belts and place all carry-on bags under the seat in front of you.',
            'Question 1: Read the flight announcement aloud clearly with natural intonation and stress.',
            'Đọc to thông báo chuyến bay rõ ràng với ngữ điệu và trọng âm tự nhiên.',
            'Bài mẫu: Chú ý phát âm rõ âm cuối (-s, -ed) và ngắt nghỉ nhẹ sau "Seattle".',
            '[Speaking Task 5: Express an Opinion]',
            'Question 11: Do you agree or disagree that companies should allow remote work twice a week? Give specific reasons and examples.',
            'Bạn đồng ý hay không đồng ý việc công ty cho phép làm việc từ xa hai ngày mỗi tuần?',
            'Bài mẫu: I strongly agree because remote work reduces commuting stress and improves work-life balance.',
        ];

        $writingLines = [
            '[Writing]',
            '[Writing Task 2: Respond to a Written Request]',
            'Email: From: Mark Chen (Event Coordinator)',
            'Subject: Workshop Refreshments',
            'We expect 45 attendees for Thursday\'s workshop. Could you confirm delivery time and vegetarian options?',
            'Question 6: Respond as the Catering Manager. Confirm the delivery time and explain two vegetarian options.',
            'Phản hồi với vai trò Quản lý tiệc. Xác nhận giờ giao hàng và nêu 2 món chay.',
            'Bài mẫu: Dear Mark, Thank you for your email. We will deliver refreshments at 8:30 AM and provide vegan wraps and fruit salads.',
            '[Writing Task 3: Write an Opinion Essay]',
            'Question 8: Some people prefer working for a large corporation, while others prefer a small company. Which do you prefer? (Minimum 300 words)',
            'Bài mẫu: Dàn ý 4 đoạn: Mở bài nêu quan điểm -> Thân bài 1 (cơ hội đào tạo) -> Thân bài 2 (môi trường linh hoạt) -> Kết luận.',
        ];

        $header = ['MẪU NHẬP CÂU HỎI TOEIC 4 KỸ NĂNG CHUẨN ETS - STUDYPREP'];
        $paragraphs = match ($skillKey) {
            'listening' => array_merge($header, $listeningLines),
            'reading' => array_merge($header, $readingLines),
            'speaking' => array_merge($header, $speakingLines),
            'writing' => array_merge($header, $writingLines),
            default => array_merge($header, $listeningLines, $readingLines, $speakingLines, $writingLines),
        };

        return $this->buildDocxFromParagraphs($paragraphs);
    }

    /**
     * @param  list<string>  $paragraphs
     */
    public function buildDocxFromParagraphs(array $paragraphs): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'studyprep_docx_');
        $zip = new ZipArchive;
        $zip->open($tempFile, ZipArchive::OVERWRITE);

        $contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            .'</Types>';

        $relsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            .'</Relationships>';

        $bodyXml = '';
        foreach ($paragraphs as $text) {
            $escaped = htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $bodyXml .= '<w:p><w:r><w:t xml:space="preserve">'.$escaped.'</w:t></w:r></w:p>';
        }

        $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body>'.$bodyXml.'</w:body>'
            .'</w:document>';

        $zip->addFromString('[Content_Types].xml', $contentTypesXml);
        $zip->addFromString('_rels/.rels', $relsXml);
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->close();

        $binary = (string) file_get_contents($tempFile);
        @unlink($tempFile);

        return $binary;
    }

    /**
     * @param  array<string, mixed>  $currentQuestion
     * @param  list<array<int, string>>  $matches
     */
    private function assignMultipleOptionsFromMatches(array &$currentQuestion, array $matches, int $maxOptions): void
    {
        foreach ($matches as $match) {
            $label = strtoupper(preg_replace('/[^A-D]/i', '', $match[1]));
            $optText = trim($match[2]);
            $hasStar = str_contains($match[0], '*') || str_ends_with($optText, '*');
            $this->assignSingleLetterOption($currentQuestion, $label, $optText, $hasStar, $maxOptions);
        }
    }

    /**
     * Gán phương án dạng A./(A)/A) và bảo vệ tuyệt đối không cho phương án tiếng Anh (B)/(C)/(D) lọt vào explanation.
     *
     * @param  array<string, mixed>  $currentQuestion
     */
    private function assignSingleLetterOption(
        array &$currentQuestion,
        string $label,
        string $optText,
        bool $hasStar,
        int $maxOptions
    ): void {
        if ($maxOptions === 3 && $label === 'D') {
            return;
        }

        $isVnBlock = ! empty($currentQuestion['_in_vn_block']);
        $optIsVn = $this->isVietnameseText($optText);
        $stemIsEn = ! $this->isVietnameseText((string) ($currentQuestion['content'] ?? ''));

        // Nếu đang ở block dịch nhưng phương án (B)/(C)/(D) lại là tiếng Anh và câu gốc chưa có phương án này -> cứu về options tiếng Anh!
        if ($isVnBlock && ! $optIsVn && $stemIsEn && ! isset($currentQuestion['options'][$label])) {
            $isVnBlock = false;
        }

        // Nếu câu tiếng Anh đã có option này rồi và dòng mới là tiếng Việt -> chuyển sang dịch phương án!
        if (! $isVnBlock && $stemIsEn && isset($currentQuestion['options'][$label]) && $optIsVn) {
            $currentQuestion['_in_vn_block'] = true;
            $isVnBlock = true;
        }

        if ($isVnBlock) {
            $currentQuestion['_vn_options'][$label] = trim($optText);

            return;
        }

        if ($hasStar || str_ends_with($optText, '*') || str_contains($optText, '[Đúng]') || str_contains($optText, '[CORRECT:')) {
            $optText = trim((string) preg_replace('/\*|\[Đúng\]|\[đúng\]|\[CORRECT:\s*[A-D]\]/iu', '', $optText));
            $currentQuestion['correct'] = $label;
        }

        $currentQuestion['options'][$label] = $optText;
    }

    /**
     * Gán phương án viết dưới dạng mũi tên / gạch đầu dòng (→, ->, •, -) tuần tự theo A, B, C, D.
     *
     * @param  array<string, mixed>  $currentQuestion
     */
    private function assignArrowOrBulletOption(array &$currentQuestion, string $itemText, int $maxOptions): void
    {
        $letters = $maxOptions === 3 ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'D'];
        $optIsVn = $this->isVietnameseText($itemText);
        $stemIsEn = ! $this->isVietnameseText((string) ($currentQuestion['content'] ?? ''));
        $isVnBlock = ! empty($currentQuestion['_in_vn_block']);

        if (! $isVnBlock && $stemIsEn && (count($currentQuestion['options']) >= $maxOptions || $optIsVn)) {
            $currentQuestion['_in_vn_block'] = true;
            $isVnBlock = true;
        }

        if ($isVnBlock) {
            foreach ($letters as $letter) {
                if (! isset($currentQuestion['_vn_options'][$letter])) {
                    $currentQuestion['_vn_options'][$letter] = trim($itemText);

                    return;
                }
            }

            return;
        }

        $hasStar = str_starts_with($itemText, '*') || str_ends_with($itemText, '*') || str_contains($itemText, '[Đúng]');
        $cleanText = trim((string) preg_replace('/^\*|\*$|\[Đúng\]|\[đúng\]|\[CORRECT:\s*[A-D]\]/iu', '', $itemText));

        foreach ($letters as $letter) {
            if (! isset($currentQuestion['options'][$letter])) {
                $currentQuestion['options'][$letter] = $cleanText;
                if ($hasStar) {
                    $currentQuestion['correct'] = $letter;
                }

                return;
            }
        }
    }

    /**
     * Nhận diện tiêu đề Kỹ năng (Listening / Reading / Speaking / Writing) hoặc tiêu đề Part/Task chuẩn ETS.
     *
     * @return array{skill: ?string, part_code: ?string}|null
     */
    private function detectPartOrSkillHeader(string $line, string $activeSkill, string $importScope = 'AUTO'): ?array
    {
        $detected = $this->detectRawPartOrSkillHeader($line, $activeSkill);
        if ($detected === null) {
            return null;
        }

        if ($importScope === 'FULL_LR' && ! in_array($detected['skill'], ['listening', 'reading'], true)) {
            return null;
        }

        if ($importScope === 'FULL_SW' && ! in_array($detected['skill'], ['speaking', 'writing'], true)) {
            return null;
        }

        return $detected;
    }

    /**
     * @return array{skill: string|null, part_code: string|null}|null
     */
    private function detectRawPartOrSkillHeader(string $line, string $activeSkill): ?array
    {
        $clean = trim($line, " \t[]=-_*#");

        // 0. Nếu đang trong ngữ cảnh READING hoặc WRITING, tiêu đề "Part 1", "Part 2", "Part 3" phải map đúng sang Part của kỹ năng đó
        if ($activeSkill === 'reading' && preg_match('/^Part\s*([1-3])(?:\s*[:\-\(].*)?$/iu', $clean, $rm)) {
            $map = [1 => 'PART5', 2 => 'PART6', 3 => 'PART7'];

            return ['skill' => 'reading', 'part_code' => $map[(int) $rm[1]]];
        }
        if ($activeSkill === 'writing' && preg_match('/^Part\s*([1-3])(?:\s*[:\-\(].*)?$/iu', $clean, $wm)) {
            return ['skill' => 'writing', 'part_code' => 'WRITING_T'.$wm[1]];
        }

        // 1. Tiêu đề Part 1..7 rõ ràng
        if (preg_match('/^(?:(?:Listening|Reading)\s*[\-\:\•]\s*)?Part\s*([1-7])(?:\s*[:;\-\(].*)?$/iu', $clean, $m)) {
            $num = (int) $m[1];

            return [
                'skill' => $num <= 4 ? 'listening' : 'reading',
                'part_code' => 'PART'.$num,
            ];
        }

        // 2. Tiêu đề Speaking Task 1..5 hoặc tên nhiệm vụ ETS / "TOEIC Speaking Test câu 1, 2" / "(câu 1, 2)"
        if (preg_match('/^(?:Speaking\s*(?:Task|Part)|SPK_T)\s*([1-5])(?:\s*[:\-\(].*)?$/iu', $clean, $m)) {
            return ['skill' => 'speaking', 'part_code' => 'SPK_T'.$m[1]];
        }
        if (
            mb_strlen($clean) <= 130
            && (
                preg_match('/read\s+a\s+text\s+aloud|đọc\s+to\s+văn\s+bản/iu', $clean)
                || preg_match('/(?:speaking|đề|test|\()\s*.*câu\s*1\s*[,–\-]\s*2\b/iu', $clean)
                || preg_match('/^(?:questions?|câu)\s*1\s*[,–\-]\s*2\s*[:\.]?$/iu', $clean)
            )
        ) {
            return ['skill' => 'speaking', 'part_code' => 'SPK_T1'];
        }
        if (
            mb_strlen($clean) <= 130
            && ! preg_match('/write\s+a\s+sentence|viết\s+câu/iu', $clean)
            && (
                preg_match('/describe\s+a\s+picture|mô\s+tả\s+tranh/iu', $clean)
                || preg_match('/(?:speaking|đề|test|\()\s*.*câu\s*3\s*[,–\-]\s*4\b/iu', $clean)
                || preg_match('/^(?:questions?|câu)\s*3\s*[,–\-]\s*4\s*[:\.]?$/iu', $clean)
            )
        ) {
            return ['skill' => 'speaking', 'part_code' => 'SPK_T2'];
        }
        if (
            mb_strlen($clean) <= 140
            && (
                preg_match('/respond\s+to\s+questions\s+using\s+information|using\s+information\s+provided|trả\s+lời.*thông\s+tin/iu', $clean)
                || preg_match('/(?:speaking|đề|test|\()\s*.*câu\s*8\s*,\s*9\s*,\s*10\b/iu', $clean)
                || preg_match('/(?:speaking|đề|test|\()\s*.*câu\s*8\s*[–\-]\s*10\b/iu', $clean)
                || preg_match('/^(?:questions?|câu)\s*8\s*[–\-]\s*10\s*[:\.]?$/iu', $clean)
            )
        ) {
            return ['skill' => 'speaking', 'part_code' => 'SPK_T4'];
        }
        if (
            mb_strlen($clean) <= 130
            && (
                preg_match('/respond\s+to\s+questions|trả\s+lời\s+câu\s+hỏi/iu', $clean)
                || preg_match('/(?:speaking|đề|test|\()\s*.*câu\s*5\s*,\s*6\s*,\s*7\b/iu', $clean)
                || preg_match('/(?:speaking|đề|test|\()\s*.*câu\s*5\s*[–\-]\s*7\b/iu', $clean)
                || preg_match('/^(?:questions?|câu)\s*5\s*[–\-]\s*7\s*[:\.]?$/iu', $clean)
            )
        ) {
            return ['skill' => 'speaking', 'part_code' => 'SPK_T3'];
        }
        if (
            mb_strlen($clean) <= 130
            && (
                preg_match('/express\s+an\s+opinion|trình\s+bày\s+quan\s+điểm/iu', $clean)
                || preg_match('/(?:speaking|đề|test|\()\s*.*câu\s*11\b/iu', $clean)
            )
        ) {
            return ['skill' => 'speaking', 'part_code' => 'SPK_T5'];
        }

        // 3. Tiêu đề Writing Task 1..3 hoặc tên nhiệm vụ ETS
        if (preg_match('/^(?:Writing\s*(?:Task|Part)|WRITING_T)\s*([1-3])(?:\s*[:\-\(].*)?$/iu', $clean, $m)) {
            return ['skill' => 'writing', 'part_code' => 'WRITING_T'.$m[1]];
        }
        if (preg_match('/write\s+a\s+sentence(?:\s+based\s+on\s+a\s+picture)?|viết\s+câu\s+(?:dựa\s+trên|theo)\s+tranh/iu', $clean) && mb_strlen($clean) <= 130) {
            return ['skill' => 'writing', 'part_code' => 'WRITING_T1'];
        }
        if (preg_match('/respond\s+to\s+a\s+written\s+request|respond\s+to\s+email|written\s+requests|viết\s+email\s+phản\s+hồi/iu', $clean) && mb_strlen($clean) <= 130) {
            return ['skill' => 'writing', 'part_code' => 'WRITING_T2'];
        }
        if (preg_match('/(?:question\s*8\s*[:\-]\s*)?opinion\s+essay|write\s+an\s+opinion\s+essay|viết\s+bài\s+luận/iu', $clean) && mb_strlen($clean) <= 130) {
            return ['skill' => 'writing', 'part_code' => 'WRITING_T3'];
        }

        // 4. Tiêu đề Kỹ năng tổng
        if (preg_match('/^(?:TOEIC\s+|PHẦN\s+|KỸ\s+NĂNG\s+)?LISTENING(?:\s+TEST|\s+SECTION|\s+COMPREHENSION)?$/iu', $clean)) {
            return ['skill' => 'listening', 'part_code' => 'PART1'];
        }
        if (preg_match('/^(?:TOEIC\s+|PHẦN\s+|KỸ\s+NĂNG\s+)?READING(?:\s+TEST|\s+SECTION|\s+COMPREHENSION)?$/iu', $clean)) {
            return ['skill' => 'reading', 'part_code' => 'PART5'];
        }
        if (preg_match('/^(?:TOEIC\s+|PHẦN\s+|KỸ\s+NĂNG\s+)?SPEAKING(?:\s+TEST|\s+SECTION)?$/iu', $clean)) {
            return ['skill' => 'speaking', 'part_code' => 'SPK_T1'];
        }
        if (preg_match('/^(?:TOEIC\s+|PHẦN\s+|KỸ\s+NĂNG\s+)?WRITING(?:\s+TEST|\s+SECTION)?$/iu', $clean)) {
            return ['skill' => 'writing', 'part_code' => 'WRITING_T1'];
        }

        return null;
    }

    private function resolvePartCodeForQuestion(
        int $qNum,
        string $currentPart,
        string $activeSkill,
        ?string $forcedPart,
        bool $hasExplicitPartHeader
    ): string {
        if ($forcedPart !== null) {
            return $forcedPart;
        }

        // Ép chuẩn dải số câu Reading TOEIC (101–200) kể cả khi file ghi nhầm PART 1 / PART 2 / PART 3
        if ($qNum >= 101 && $qNum <= 200 && in_array($activeSkill, ['reading', 'auto', 'listening'], true)) {
            if ($qNum <= 130) {
                return 'PART5';
            }
            if ($qNum <= 146) {
                return 'PART6';
            }

            return 'PART7';
        }

        if ($activeSkill === 'speaking') {
            return $hasExplicitPartHeader ? $currentPart : $this->inferSpeakingPartByNumber($qNum);
        }

        if ($activeSkill === 'writing') {
            if (! $hasExplicitPartHeader) {
                return $this->inferWritingPartByNumber($qNum);
            }
            if ($currentPart === 'WRITING_T1' && $qNum >= 6 && $qNum <= 7) {
                return 'WRITING_T2';
            }
            if (in_array($currentPart, ['WRITING_T1', 'WRITING_T2'], true) && $qNum === 8) {
                return 'WRITING_T3';
            }

            return $currentPart;
        }

        if ($activeSkill === 'listening') {
            if ($hasExplicitPartHeader && in_array($currentPart, ['PART1', 'PART2', 'PART3', 'PART4'], true)) {
                return $currentPart;
            }
            if ($qNum >= 1 && $qNum <= 6) {
                return 'PART1';
            }
            if ($qNum >= 7 && $qNum <= 31) {
                return 'PART2';
            }
            if ($qNum >= 32 && $qNum <= 70) {
                return 'PART3';
            }

            return 'PART4';
        }

        if ($activeSkill === 'reading') {
            if ($hasExplicitPartHeader && in_array($currentPart, ['PART5', 'PART6', 'PART7'], true)) {
                return $currentPart;
            }
            if ($qNum >= 131 && $qNum <= 146) {
                return 'PART6';
            }
            if ($qNum >= 147 && $qNum <= 200) {
                return 'PART7';
            }

            return 'PART5';
        }

        // AUTO mode: ưu tiên Part header hiện tại nếu có, fallback theo dải số câu ETS L&R
        if ($hasExplicitPartHeader) {
            return $currentPart;
        }

        if ($qNum >= 7 && $qNum <= 31) {
            return 'PART2';
        }
        if ($qNum >= 32 && $qNum <= 70) {
            return 'PART3';
        }
        if ($qNum >= 71 && $qNum <= 100) {
            return 'PART4';
        }
        if ($qNum >= 101 && $qNum <= 130) {
            return 'PART5';
        }
        if ($qNum >= 131 && $qNum <= 146) {
            return 'PART6';
        }
        if ($qNum >= 147 && $qNum <= 200) {
            return 'PART7';
        }

        return $currentPart;
    }

    private function inferSpeakingPartByNumber(int $qNum): string
    {
        if ($qNum <= 2) {
            return 'SPK_T1';
        }
        if ($qNum <= 4) {
            return 'SPK_T2';
        }
        if ($qNum <= 7) {
            return 'SPK_T3';
        }
        if ($qNum <= 10) {
            return 'SPK_T4';
        }

        return 'SPK_T5';
    }

    private function inferWritingPartByNumber(int $qNum): string
    {
        if ($qNum <= 5) {
            return 'WRITING_T1';
        }
        if ($qNum <= 7) {
            return 'WRITING_T2';
        }

        return 'WRITING_T3';
    }

    private function skillOfPartCode(string $partCode): string
    {
        $code = strtoupper($partCode);
        if (in_array($code, ['PART1', 'PART2', 'PART3', 'PART4'], true)) {
            return 'listening';
        }
        if (str_starts_with($code, 'SPK_')) {
            return 'speaking';
        }
        if (str_starts_with($code, 'WRITING_')) {
            return 'writing';
        }

        return 'reading';
    }

    private function partNumberOfCode(string $partCode): int
    {
        if (preg_match('/(\d+)$/', strtoupper($partCode), $m)) {
            return (int) $m[1];
        }

        return 5;
    }

    /**
     * @param  list<array<string, mixed>>  $questions
     * @return list<array<string, mixed>>
     */
    private function applyScopeOverride(array $questions, string $scope): array
    {
        if (! in_array($scope, self::FIXED_PART_CODES, true)) {
            return $questions;
        }

        foreach ($questions as $idx => $q) {
            $questions[$idx]['part_code'] = $scope;
        }

        return $questions;
    }

    /**
     * @param  array<string, string>  $vnOptions
     */
    private function formatVietnameseTranslation(string $vnStem, array $vnOptions, string $partCode = 'PART5'): string
    {
        $parts = [];
        $cleanStem = trim($vnStem);
        if ($cleanStem !== '') {
            $parts[] = 'Dịch đề bài: '.$cleanStem;
        }

        if (! empty($vnOptions) && ! in_array($partCode, self::PRODUCTIVE_PART_CODES, true)) {
            $allowedLetters = $partCode === 'PART2' ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'D'];
            $optLines = ['Dịch phương án:'];
            foreach ($allowedLetters as $letter) {
                if (isset($vnOptions[$letter]) && trim((string) $vnOptions[$letter]) !== '') {
                    $optLines[] = "({$letter}) ".trim((string) $vnOptions[$letter]);
                }
            }
            if (count($optLines) > 1) {
                $parts[] = implode("\n", $optLines);
            }
        }

        return implode("\n", $parts);
    }

    private function appendExplanation(?string $existing, string $addition): ?string
    {
        $a = $this->cleanExplanationText((string) $existing);
        $b = $this->cleanExplanationText($addition);
        if ($a === '' && $b === '') {
            return null;
        }
        if ($a === '') {
            return $b;
        }
        if ($b === '' || str_contains($a, $b)) {
            return $a;
        }

        return $a."\n\n".$b;
    }

    /**
     * Làm sạch ký tự phân cách bảng `|` và các chữ giao diện web thừa trong phần giải thích.
     */
    private function cleanExplanationText(string $text): string
    {
        $clean = str_replace(['Giải thích chi tiết ▲', 'Giải thích chi tiết ▼', 'Giải thích chi tiết', 'Xem đáp án'], '', $text);
        $lines = preg_split('/\R/u', $clean) ?: [];
        $result = [];

        foreach ($lines as $raw) {
            $trimmed = trim($raw);
            if ($trimmed === '') {
                continue;
            }

            if (str_contains($trimmed, '|')) {
                $segments = array_values(array_filter(
                    array_map(static fn (string $s): string => trim($s), explode('|', $trimmed)),
                    static fn (string $s): bool => $s !== ''
                ));
                foreach ($segments as $seg) {
                    $result[] = $seg;
                }
            } else {
                $result[] = $trimmed;
            }
        }

        return implode("\n", $result);
    }

    /**
     * Suy luận đáp án đúng A/B/C/D từ nội dung Giải thích khi file gốc không ghi dòng "Đáp án: X".
     *
     * @param  array<string, mixed>  $question
     */
    private function inferCorrectOptionFromExplanation(array $question): ?string
    {
        $exp = trim((string) ($question['explanation'] ?? ''));
        $options = is_array($question['options'] ?? null) ? $question['options'] : [];
        if ($exp === '' || empty($options)) {
            return null;
        }

        // 1. Dấu hiệu rõ ràng trong giải thích: "Đáp án đúng: A", "Chọn đáp án C", "✅", "→ A"
        if (preg_match('/(?:Đáp\s*án(?:\s*đúng)?|Chọn(?:\s*đáp\s*án)?|Answer)\s*[:\-]?\s*\(?([A-D])\)?(?:\b|[\.\s✅])/iu', $exp, $m)) {
            return strtoupper($m[1]);
        }
        if (preg_match('/\(?([A-D])\)?[^\n]{0,80}✅/u', $exp, $m) || preg_match('/✅[^\n]{0,20}\(?([A-D])\)?/u', $exp, $m)) {
            return strtoupper($m[1]);
        }
        if (preg_match('/(?:→|➡)\s*(?:Đáp\s*án(?:\s*đúng)?\s*[:\-]?\s*)?\(?([A-D])\)?\b/iu', $exp, $m)) {
            return strtoupper($m[1]);
        }

        // 2. Khớp nguyên văn phương án sau dấu "=" (phổ biến ở Part 7: "= Show an original store receipt")
        $expLower = mb_strtolower((string) preg_replace('/\s+/u', ' ', $exp));
        foreach ($options as $letter => $optContent) {
            $optNorm = mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', (string) $optContent)));
            if (mb_strlen($optNorm) >= 4 && preg_match('/=\s*'.preg_quote($optNorm, '/').'/u', $expLower) === 1) {
                return strtoupper((string) $letter);
            }
        }

        // 3. Part 5/6: Khớp từ khóa trước/sau chỗ trống (------- hoặc [131] hoặc 131) với câu ví dụ đầy đủ trong giải thích
        $stem = (string) ($question['content'] ?? '');
        $stimulus = (string) ($question['stimulus_text'] ?? '');
        $contextSource = $stem !== '' ? $stem : $stimulus;
        $qNum = (int) ($question['question_number'] ?? 0);

        if (preg_match('/(?:([A-Za-z0-9\-\'’]+)\s+)?(?:-{3,}(?:\s*\[?'.$qNum.'\]?)?|\[?'.$qNum.'\]?)(?:\s+([A-Za-z0-9\-\'’]+))?/u', $contextSource, $bm)) {
            $before = trim((string) ($bm[1] ?? ''));
            $after = trim((string) ($bm[2] ?? ''));
            if ($before !== '' || $after !== '') {
                foreach ($options as $letter => $optContent) {
                    $optClean = trim((string) $optContent);
                    if ($optClean === '') {
                        continue;
                    }
                    $phrase = trim(($before !== '' ? $before.' ' : '').$optClean.($after !== '' ? ' '.$after : ''));
                    if (mb_strlen($phrase) >= 5 && stripos($exp, $phrase) !== false) {
                        return strtoupper((string) $letter);
                    }
                }
            }
        }

        // 4. Fallback: Phương án có độ dài >= 6 ký tự xuất hiện nguyên văn trong dòng giải thích đầu tiên
        $firstExpLine = trim(explode("\n", $exp)[0]);
        if (preg_match('/^[A-D]\.\s/u', $firstExpLine) !== 1) {
            $firstLower = mb_strtolower((string) preg_replace('/\s+/u', ' ', $firstExpLine));
            foreach ($options as $letter => $optContent) {
                $optNorm = mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', (string) $optContent)));
                if (mb_strlen($optNorm) >= 6 && str_contains($firstLower, $optNorm)) {
                    return strtoupper((string) $letter);
                }
            }
        }

        return null;
    }

    private function mentionsVisualOrGraphic(string $text): bool
    {
        return preg_match('/\b(graphic|chart|table|map|diagram|schedule|picture|image|figure|notice)\b|nhìn vào ảnh|biểu đồ|bảng/iu', $text) === 1;
    }

    private function normalizeRelativeMediaPath(string $url): string
    {
        $clean = trim($url);
        if ($clean === '') {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $clean) === 1) {
            return $clean;
        }

        return ltrim($clean, '/');
    }

    /**
     * @param  list<string>  $lines
     * @return list<array<string, mixed>>
     */
    public function parseFreeformLines(array $lines, string $importScope = 'AUTO'): array
    {
        $questions = [];
        $forcedPart = in_array($importScope, self::FIXED_PART_CODES, true) ? $importScope : null;
        $currentPart = $forcedPart ?? 'PART5';
        $buffer = [];

        foreach ($lines as $raw) {
            $line = trim($raw);
            if ($line === '' || str_starts_with($line, '[IMAGE_URL:') || str_starts_with($line, '[AUDIO_URL:')) {
                continue;
            }
            if ($forcedPart === null && preg_match('/Part\s*([1-7])/iu', $line, $m)) {
                $currentPart = 'PART'.$m[1];

                continue;
            }
            $buffer[] = $line;
        }

        $isProductive = in_array($currentPart, self::PRODUCTIVE_PART_CODES, true);
        if ($isProductive) {
            foreach ($buffer as $stem) {
                if (mb_strlen($stem) < 5) {
                    continue;
                }
                $questions[] = [
                    'part_code' => $currentPart,
                    'stimulus_text' => null,
                    'image_url' => null,
                    'audio_url' => null,
                    'audio_transcript' => null,
                    'content' => $stem,
                    'options' => [],
                    'correct' => null,
                    'difficulty' => 'MEDIUM',
                    'explanation' => null,
                ];
            }

            return $questions;
        }

        $chunkSize = $currentPart === 'PART2' ? 4 : 5;
        foreach (array_chunk($buffer, $chunkSize) as $chunk) {
            $stem = $chunk[0] ?? '';
            if (mb_strlen($stem) < 5) {
                continue;
            }
            $opts = [
                'A' => preg_replace('/^\(?[A-D][\.\)\:]\s*/iu', '', $chunk[1] ?? 'Option A'),
                'B' => preg_replace('/^\(?[A-D][\.\)\:]\s*/iu', '', $chunk[2] ?? 'Option B'),
                'C' => preg_replace('/^\(?[A-D][\.\)\:]\s*/iu', '', $chunk[3] ?? 'Option C'),
            ];
            if ($currentPart !== 'PART2') {
                $opts['D'] = preg_replace('/^\(?[A-D][\.\)\:]\s*/iu', '', $chunk[4] ?? 'Option D');
            }
            $questions[] = [
                'part_code' => $currentPart,
                'stimulus_text' => null,
                'image_url' => null,
                'audio_url' => null,
                'audio_transcript' => null,
                'content' => $stem,
                'options' => $opts,
                'correct' => 'A',
                'difficulty' => 'MEDIUM',
                'explanation' => null,
            ];
        }

        return $questions;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseJsonFile(string $filePath): array
    {
        $decoded = json_decode((string) file_get_contents($filePath), true);
        if (! is_array($decoded)) {
            return [];
        }

        if (isset($decoded['parts']) && is_array($decoded['parts'])) {
            return $this->flattenStructuredJson($decoded);
        }

        $questions = [];
        foreach ($decoded as $row) {
            if (! is_array($row) || empty($row['content'])) {
                continue;
            }

            $options = [];
            if (is_array($row['options'] ?? null)) {
                foreach ($row['options'] as $k => $opt) {
                    if (is_array($opt) && isset($opt['label'], $opt['content'])) {
                        $options[strtoupper((string) $opt['label'])] = (string) $opt['content'];
                        if (! empty($opt['is_correct'])) {
                            $row['correct'] = strtoupper((string) $opt['label']);
                        }
                    } elseif (is_string($k)) {
                        $options[strtoupper($k)] = (string) $opt;
                    }
                }
            }

            $partCode = strtoupper((string) ($row['part_code'] ?? $row['part'] ?? 'PART5'));
            $isProductive = in_array($partCode, self::PRODUCTIVE_PART_CODES, true);

            $questions[] = [
                'part_code' => $partCode,
                'stimulus_text' => $row['stimulus_text'] ?? null,
                'image_url' => $row['image_url'] ?? null,
                'audio_url' => $row['audio_url'] ?? null,
                'audio_transcript' => $row['audio_transcript'] ?? null,
                'content' => (string) $row['content'],
                'options' => $isProductive ? [] : $options,
                'correct' => $isProductive ? null : strtoupper((string) ($row['correct'] ?? $row['correct_option'] ?? 'A')),
                'difficulty' => strtoupper((string) ($row['difficulty'] ?? 'MEDIUM')),
                'explanation' => $row['explanation'] ?? null,
            ];
        }

        return $questions;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseCsvFile(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [];
        }

        $questions = [];
        $rowIndex = 0;

        while (($row = fgetcsv($handle, 4096, ',')) !== false) {
            $rowIndex++;
            $firstCol = trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) ($row[0] ?? '')));
            if ($firstCol === '') {
                continue;
            }

            if ($rowIndex === 1 && (str_contains(mb_strtolower($firstCol), 'part') || str_contains(mb_strtolower($firstCol), 'nội dung') || str_contains(mb_strtolower($firstCol), 'content'))) {
                continue;
            }

            $partRaw = strtoupper(trim((string) ($row[0] ?? 'PART5')));
            $hasPartCol = in_array($partRaw, self::FIXED_PART_CODES, true) || str_starts_with($partRaw, 'PART') || str_starts_with($partRaw, 'WRITING') || str_starts_with($partRaw, 'SPK_');
            $partCode = $hasPartCol ? $partRaw : 'PART5';
            $content = $hasPartCol ? trim((string) ($row[1] ?? '')) : $firstCol;

            if ($content === '') {
                continue;
            }

            $offset = $hasPartCol ? 1 : 0;
            $isProductive = in_array($partCode, self::PRODUCTIVE_PART_CODES, true);

            $questions[] = [
                'part_code' => $partCode,
                'stimulus_text' => null,
                'image_url' => null,
                'audio_url' => null,
                'audio_transcript' => null,
                'content' => $content,
                'options' => $isProductive ? [] : array_filter([
                    'A' => trim((string) ($row[$offset + 1] ?? '')),
                    'B' => trim((string) ($row[$offset + 2] ?? '')),
                    'C' => trim((string) ($row[$offset + 3] ?? '')),
                    'D' => $partCode === 'PART2' ? '' : trim((string) ($row[$offset + 4] ?? '')),
                ]),
                'correct' => $isProductive ? null : strtoupper(trim((string) ($row[$offset + 5] ?? 'A'))),
                'difficulty' => strtoupper(trim((string) ($row[$offset + 6] ?? 'MEDIUM'))),
                'explanation' => trim((string) ($row[$offset + 7] ?? '')) ?: null,
            ];
        }

        fclose($handle);

        return $questions;
    }
}
