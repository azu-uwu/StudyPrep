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
        foreach ($chunks as $chunk) {
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

        // URL công khai truy cập file audio
        $audioUrl = '/storage/'.$folderRelative.'/'.$filename;
        $fileSize = strlen($combinedMp3Data);

        // Ước lượng thời lượng (MP3 ~ 16-24kbps hoặc dựa trên độ dài từ: trung bình 130 từ/phút)
        $wordCount = str_word_count($cleanedText);
        $durationSeconds = max(2, (int) round(($wordCount / 130) * 60));

        return [
            'audio_url' => $audioUrl,
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
            $question = Question::find($questionId);
            if ($question) {
                $part = $partNumber ?? $question->part_number ?? $this->detectPartFromSection($question->section_id) ?? 1;
                if (! $textToRead) {
                    $textToRead = $question->content;
                }
                $filenamePrefix = 'part'.$part.'_q'.$questionId;
            }
        }

        $questionSet = null;
        if ($questionSetId || ($question && $question->question_set_id)) {
            $setId = $questionSetId ?? $question->question_set_id;
            $questionSet = QuestionSet::find($setId);
            if ($questionSet && ! $textToRead) {
                $textToRead = $questionSet->transcript ?: $questionSet->stimulus_text;
            }
            if ($questionSet && ! $filenamePrefix) {
                $filenamePrefix = 'part'.$part.'_set'.$setId;
            }
        }

        if (! $textToRead) {
            throw new \InvalidArgumentException('Không tìm thấy nội dung văn bản/transcript để tạo audio.');
        }

        $result = $this->generateAudio($textToRead, $part, $filenamePrefix);
        $audioUrl = $result['audio_url'];

        // Cập nhật CSDL
        if ($question) {
            $question->audio_url = $audioUrl;
            if ($part && empty($question->part_number)) {
                $question->part_number = $part;
            }
            $question->save();
        }

        if ($questionSet) {
            $questionSet->media_url = $audioUrl;
            $questionSet->media_type = 'AUDIO';
            $questionSet->save();
        }

        return array_merge($result, [
            'question_id' => $questionId,
            'question_set_id' => $questionSetId ?? ($question ? $question->question_set_id : null),
            'part_number' => $part,
        ]);
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
