<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionSet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TextToSpeechService
{
    /**
     * URL Endpoint Google Translate TTS (Miễn phí, ổn định, hỗ trợ tiếng Anh US / UK)
     */
    protected string $googleTtsBaseUrl = 'https://translate.google.com/translate_tts';

    /**
     * Giới hạn ký tự tối đa cho mỗi chunk request TTS của Google
     */
    protected int $maxChunkLength = 180;

    /**
     * Chuyển văn bản thành file audio MP3 và lưu vào storage theo Part
     *
     * @param  string  $text  Nội dung cần đọc
     * @param  int  $partNumber  Part TOEIC (1, 2, 3, 4)
     * @param  string|null  $customFilename  Tên file tùy chọn (không có đuôi .mp3)
     * @param  string  $lang  Mã ngôn ngữ (en-US, en, en-GB)
     * @return array{audio_url: string, file_path: string, file_size: int, duration_estimate: int}
     *
     * @throws \RuntimeException
     */
    public function generateAudio(
        string $text,
        int $partNumber = 1,
        ?string $customFilename = null,
        string $lang = 'en'
    ): array {
        $cleanedText = $this->cleanTranscriptForSpeech($text);

        if (trim($cleanedText) === '') {
            throw new \InvalidArgumentException('Nội dung văn bản để tạo audio không được rỗng.');
        }

        // Tách văn bản dài thành các đoạn nhỏ dưới 180 ký tự để Google TTS không bị lỗi
        $chunks = $this->splitTextIntoChunks($cleanedText);

        $combinedMp3Data = '';
        foreach ($chunks as $idx => $chunk) {
            if ($idx > 0) {
                usleep(300000);
            }
            $chunkAudio = $this->fetchTtsAudioChunk($chunk, $lang);
            if ($chunkAudio !== '') {
                $combinedMp3Data .= $chunkAudio;
            }
        }

        if ($combinedMp3Data === '') {
            throw new \RuntimeException('Không thể tạo file audio từ dịch vụ TTS. Vui lòng thử lại.');
        }

        // Thư mục lưu: storage/app/public/audio/listening/part_{part_number}/
        $folderRelative = 'audio/listening/part_'.$partNumber;
        $dirPath = storage_path('app/public/'.$folderRelative);

        if (! File::isDirectory($dirPath)) {
            File::makeDirectory($dirPath, 0755, true, true);
        }

        $filename = ($customFilename ? Str::slug($customFilename, '_') : 'tts_'.$partNumber.'_'.time().'_'.Str::random(6)).'.mp3';
        $fullFilePath = $dirPath.'/'.$filename;

        File::put($fullFilePath, $combinedMp3Data);

        // Đường dẫn tương đối lưu DB (không có dấu / đầu để tương thích thư mục con)
        $relativePath = 'storage/'.$folderRelative.'/'.$filename;
        $audioUrl = asset($relativePath);
        $fileSize = strlen($combinedMp3Data);

        // Ước lượng thời lượng (MP3 ~ 16-24kbps hoặc dựa trên độ dài từ: trung bình 130 từ/phút)
        $wordCount = str_word_count($cleanedText);
        $durationSeconds = max(2, (int) round(($wordCount / 130) * 60));

        return [
            'audio_url' => $audioUrl,
            'relative_path' => $relativePath,
            'file_path' => $fullFilePath,
            'file_size' => $fileSize,
            'duration_estimate' => $durationSeconds,
            'cleaned_text' => $cleanedText,
        ];
    }

    /**
     * Sinh audio và cập nhật trực tiếp vào CSDL cho câu hỏi hoặc nhóm câu hỏi
     *
     * @param  int|null  $questionId  ID câu hỏi trong bảng questions
     * @param  int|null  $questionSetId  ID nhóm trong bảng question_set
     * @param  string|null  $textText  Văn bản cần sinh (nếu null, tự động lấy từ CSDL)
     * @param  int|null  $partNumber  Part (1, 2, 3, 4)
     */
    public function generateAndSaveToDatabase(
        ?int $questionId = null,
        ?int $questionSetId = null,
        ?string $textText = null,
        ?int $partNumber = null
    ): array {
        $textToRead = $textText;
        $part = $partNumber ?? 1;
        $filenamePrefix = '';

        $question = null;
        if ($questionId) {
            $question = Question::with(['section', 'options', 'questionSet.questions.options', 'questionSet.questions.section'])->find($questionId);
            if ($question) {
                $part = $partNumber ?? $question->part_number ?? $this->detectPartFromSection($question->section_id) ?? 1;
                $filenamePrefix = 'part'.$part.'_q'.$questionId;
            }
        }

        $questionSet = null;
        if ($questionSetId || ($question && $question->question_set_id)) {
            $setId = $questionSetId ?? $question->question_set_id;
            $questionSet = ($question && $question->questionSet && (int) $question->questionSet->question_set_id === (int) $setId)
                ? $question->questionSet
                : QuestionSet::with(['questions.options', 'questions.section'])->find($setId);
            if ($questionSet && ! $filenamePrefix) {
                $filenamePrefix = 'part'.$part.'_set'.$setId;
            }
        }

        if (! $textToRead) {
            if ($questionSet) {
                $textToRead = $this->buildToeicListeningScript($questionSet);
            } elseif ($question) {
                $textToRead = $this->buildScriptFromSingleQuestion($question, $part);
            }
        }

        if (! $textToRead) {
            throw new \InvalidArgumentException('Không tìm thấy nội dung văn bản/transcript để tạo audio.');
        }

        $result = $this->generateAudio($textToRead, $part, $filenamePrefix);
        $relativePath = $result['relative_path'];

        // Lưu đường dẫn audio MP3 vào cột riêng audio_url (không ghi đè ảnh media_url)
        if ($questionSet) {
            $questionSet->audio_url = $relativePath;
            if (empty($questionSet->media_url) && empty($questionSet->media_type)) {
                $questionSet->media_type = 'AUDIO';
            }
            if (empty($questionSet->transcript)) {
                $questionSet->transcript = $textToRead;
            }
            $questionSet->save();
        } elseif ($question) {
            $newSet = QuestionSet::query()->create([
                'title' => 'Audio TTS #Q-'.$question->question_id,
                'audio_url' => $relativePath,
                'media_type' => 'AUDIO',
                'transcript' => $textToRead,
            ]);
            $question->question_set_id = $newSet->question_set_id;
            $question->save();
            $questionSet = $newSet;
        }

        return array_merge($result, [
            'question_id' => $questionId,
            'question_set_id' => $questionSet?->question_set_id ?? $questionSetId,
            'part_number' => $part,
        ]);
    }

    /**
     * Ghép kịch bản đọc chuẩn TOEIC cho một QuestionSet theo từng Part (1, 2, 3, 4).
     */
    public function buildToeicListeningScript(QuestionSet $set): string
    {
        $set->loadMissing(['questions.options', 'questions.section']);
        $questions = $set->questions->sortBy('question_id')->values();
        $firstQuestion = $questions->first();

        $partCode = strtoupper((string) ($firstQuestion?->section?->code ?? ''));
        $partNumber = $this->detectPartFromSection($firstQuestion?->section_id) ?? 0;

        if ($partCode === 'PART1' || $partNumber === 1) {
            if ($firstQuestion) {
                $script = $this->buildScriptFromSingleQuestion($firstQuestion, 1);
                if ($script !== '') {
                    return $script;
                }
            }
        }

        if ($partCode === 'PART2' || $partNumber === 2) {
            if ($firstQuestion) {
                $script = $this->buildScriptFromSingleQuestion($firstQuestion, 2);
                if ($script !== '') {
                    return $script;
                }
            }
        }

        $rawTranscript = trim((string) ($set->transcript ?: $set->stimulus_text ?: ''));
        if ($rawTranscript !== '') {
            return $this->cleanTranscriptForSpeech($rawTranscript);
        }

        if ($firstQuestion) {
            return $this->cleanTranscriptForSpeech((string) $firstQuestion->content);
        }

        return '';
    }

    /**
     * Sinh kịch bản đọc chuẩn cho 1 câu hỏi đơn lẻ (Part 1 hoặc Part 2).
     */
    protected function buildScriptFromSingleQuestion(Question $question, int $partNumber): string
    {
        $question->loadMissing('options');
        $options = $question->options->sortBy('label')->values();

        if ($partNumber === 1) {
            $lines = [];
            foreach ($options as $opt) {
                $optText = trim((string) $opt->content);
                if ($optText !== '' && preg_match('/^Option\s+[A-D]$/i', $optText) !== 1) {
                    $lines[] = "({$opt->label}) {$optText}.";
                }
            }
            if (! empty($lines)) {
                return $this->cleanTranscriptForSpeech(implode(' ', $lines));
            }
        }

        if ($partNumber === 2) {
            $parts = [];
            $stem = trim((string) $question->content);
            if ($stem !== '') {
                $parts[] = rtrim($stem, '.?!').(str_ends_with($stem, '?') ? '' : '?');
            }
            foreach ($options as $opt) {
                $optText = trim((string) $opt->content);
                if ($optText !== '' && preg_match('/^Option\s+[A-D]$/i', $optText) !== 1) {
                    $parts[] = "({$opt->label}) {$optText}.";
                }
            }
            if (! empty($parts)) {
                return $this->cleanTranscriptForSpeech(implode(' ', $parts));
            }
        }

        return $this->cleanTranscriptForSpeech((string) $question->content);
    }

    /**
     * Sinh audio MP3 theo lô an toàn (tối đa $maxGroups nhóm mỗi lần gọi, nghỉ 300ms giữa các request tránh HTTP 429).
     *
     * @param  array<int>  $questionIds
     * @return array{processed: int, succeeded: int, failed: int, results: array<int, array<string, mixed>>}
     */
    public function generateBatchForQuestions(array $questionIds, int $maxGroups = 5): array
    {
        $cleanIds = array_values(array_unique(array_filter(array_map('intval', $questionIds), static fn (int $id): bool => $id > 0)));
        if (empty($cleanIds)) {
            return ['processed' => 0, 'succeeded' => 0, 'failed' => 0, 'results' => []];
        }

        $questions = Question::with(['section', 'options', 'questionSet.questions.options', 'questionSet.questions.section'])
            ->whereIn('question_id', $cleanIds)
            ->orderBy('question_id')
            ->get();

        // Gom nhóm theo question_set_id để tránh sinh trùng nhiều lần cho cùng 1 đoạn hội thoại/bài nói Part 3/4
        $groups = [];
        foreach ($questions as $q) {
            $groupKey = $q->question_set_id ? 'set_'.$q->question_set_id : 'q_'.$q->question_id;
            if (! isset($groups[$groupKey])) {
                $groups[$groupKey] = $q;
            }
        }

        $targetGroups = array_slice(array_values($groups), 0, max(1, $maxGroups));
        $succeeded = 0;
        $failed = 0;
        $results = [];

        foreach ($targetGroups as $idx => $q) {
            if ($idx > 0) {
                usleep(300000);
            }

            $partNumber = $this->detectPartFromSection($q->section_id) ?? 1;
            if ($partNumber < 1 || $partNumber > 4) {
                $partNumber = 1;
            }

            try {
                $res = $this->generateAndSaveToDatabase(
                    $q->question_id,
                    $q->question_set_id ? (int) $q->question_set_id : null,
                    null,
                    $partNumber
                );
                $succeeded++;
                $results[] = [
                    'question_id' => $q->question_id,
                    'question_set_id' => $res['question_set_id'],
                    'success' => true,
                    'audio_url' => $res['audio_url'],
                    'file_size' => $res['file_size'],
                ];
            } catch (\Throwable $e) {
                $failed++;
                $results[] = [
                    'question_id' => $q->question_id,
                    'question_set_id' => $q->question_set_id,
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'processed' => count($targetGroups),
            'succeeded' => $succeeded,
            'failed' => $failed,
            'results' => $results,
        ];
    }

    /**
     * Tách văn bản dài thành các chunk tự nhiên theo dấu câu
     *
     * @return array<string>
     */
    protected function splitTextIntoChunks(string $text): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if (mb_strlen($text) <= $this->maxChunkLength) {
            return [$text];
        }

        $sentences = preg_split('/(?<=[.?!;,\n])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $chunks = [];
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if ($sentence === '') {
                continue;
            }

            if (mb_strlen($currentChunk.' '.$sentence) <= $this->maxChunkLength) {
                $currentChunk = trim($currentChunk.' '.$sentence);
            } else {
                if ($currentChunk !== '') {
                    $chunks[] = $currentChunk;
                    $currentChunk = '';
                }

                // Nếu riêng câu này đã dài hơn giới hạn, cắt theo từ
                if (mb_strlen($sentence) > $this->maxChunkLength) {
                    $words = explode(' ', $sentence);
                    $temp = '';
                    foreach ($words as $w) {
                        if (mb_strlen($temp.' '.$w) <= $this->maxChunkLength) {
                            $temp = trim($temp.' '.$w);
                        } else {
                            if ($temp !== '') {
                                $chunks[] = $temp;
                            }
                            $temp = $w;
                        }
                    }
                    if ($temp !== '') {
                        $currentChunk = $temp;
                    }
                } else {
                    $currentChunk = $sentence;
                }
            }
        }

        if ($currentChunk !== '') {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    /**
     * Gửi request lấy audio MP3 cho từng chunk
     */
    protected function fetchTtsAudioChunk(string $text, string $lang = 'en'): string
    {
        try {
            $url = $this->googleTtsBaseUrl.'?'.http_build_query([
                'ie' => 'UTF-8',
                'client' => 'tw-ob',
                'tl' => $lang,
                'q' => $text,
            ]);

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                'Accept' => '*/*',
                'Referer' => 'https://translate.google.com/',
            ])->timeout(12)->get($url);

            if ($response->successful()) {
                return $response->body();
            }

            Log::warning('Google TTS error HTTP status: '.$response->status().' for text: '.mb_substr($text, 0, 30));
        } catch (\Throwable $e) {
            Log::error('TextToSpeechService Exception: '.$e->getMessage());
        }

        return '';
    }

    /**
     * Làm sạch transcript trước khi đọc:
     * - Loại bỏ phần dịch tiếng Việt: [Dịch Tiếng Việt] ... hoặc chú thích trong ngoặc đơn tiếng Việt
     * - Loại bỏ ký hiệu người nói W: / M: giữ giọng đọc tự nhiên
     * - Loại bỏ đường kẻ ngang ---
     */
    public function cleanTranscriptForSpeech(string $rawText): string
    {
        // 1. Tách và bỏ toàn bộ phần sau '---' hoặc '[Dịch Tiếng Việt]'
        if (str_contains($rawText, '---')) {
            $parts = explode('---', $rawText);
            $rawText = $parts[0];
        }
        if (str_contains($rawText, '[Dịch Tiếng Việt]')) {
            $parts = explode('[Dịch Tiếng Việt]', $rawText);
            $rawText = $parts[0];
        }
        if (str_contains($rawText, '[Dịch nghĩa]')) {
            $parts = explode('[Dịch nghĩa]', $rawText);
            $rawText = $parts[0];
        }

        // 2. Bỏ chú thích tiếng Việt trong ngoặc đơn, ví dụ: (Cô ấy đang ăn trong khu picnic.)
        // Phát hiện các cụm ngoặc chứa ký tự tiếng Việt có dấu
        $cleaned = preg_replace('/\([^\)]*[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđĐ][^\)]*\)/u', '', $rawText);

        // 3. Chuẩn hóa nhãn người nói: 'W:' -> 'Woman: ', 'M:' -> 'Man: '
        $cleaned = preg_replace('/^\s*W:\s*/mi', '', $cleaned);
        $cleaned = preg_replace('/^\s*M:\s*/mi', '', $cleaned);

        // 4. Bỏ các ký tự đặc biệt thừa
        $cleaned = str_replace(["\r\n", "\r"], "\n", $cleaned);
        $cleaned = trim($cleaned);

        return $cleaned;
    }

    /**
     * Dự đoán Part từ section_id
     */
    protected function detectPartFromSection(?int $sectionId): ?int
    {
        if (! $sectionId) {
            return null;
        }

        $section = DB::table('exam_section')->where('section_id', $sectionId)->first();
        if ($section) {
            if (preg_match('/PART\s*(\d)/i', $section->code, $m) || preg_match('/Part\s*(\d)/i', $section->name, $m)) {
                return (int) $m[1];
            }
        }

        return null;
    }
}
