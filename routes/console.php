<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('audio:generate-tts {--part= : Chỉ sinh cho Part cụ thể (1, 2, 3, 4)} {--limit=0 : Giới hạn số câu (0 = tất cả)} {--force : Ghi đè file nếu đã tồn tại}', function (\App\Services\TextToSpeechService $tts) {
    $part = $this->option('part') ? (int) $this->option('part') : null;
    $limit = (int) $this->option('limit');
    $force = (bool) $this->option('force');

    $this->info('=== BẮT ĐẦU SINH FILE AUDIO TTS CHO TOEIC LISTENING ===');

    $query = \Illuminate\Support\Facades\DB::table('questions')
        ->join('question_set', 'questions.question_set_id', '=', 'question_set.question_set_id')
        ->select([
            'questions.question_id',
            'questions.part_number',
            'questions.content as q_content',
            'questions.audio_url as q_audio_url',
            'question_set.question_set_id',
            'question_set.title as set_title',
            'question_set.transcript',
            'question_set.stimulus_text',
            'question_set.media_url as set_media_url',
        ])
        ->whereNotNull('questions.part_number');

    if ($part) {
        $query->where('questions.part_number', $part);
    }

    if (!$force) {
        $query->where(function ($q) {
            $q->whereNull('questions.audio_url')
              ->orWhere('questions.audio_url', '');
        });
    }

    if ($limit > 0) {
        $query->limit($limit);
    }

    $questions = $query->get();
    $total = $questions->count();

    if ($total === 0) {
        $this->warn('Không có câu hỏi nào cần sinh audio (hoặc tất cả đã có file, hãy dùng thêm --force nếu muốn tạo lại).');
        return 0;
    }

    $this->info("Tìm thấy {$total} câu hỏi cần tạo audio.");
    $bar = $this->output->createProgressBar($total);
    $bar->start();

    $successCount = 0;
    foreach ($questions as $q) {
        try {
            $text = $q->transcript ?: $q->stimulus_text ?: $q->q_content;
            $res = $tts->generateAndSaveToDatabase($q->question_id, $q->question_set_id, $text, $q->part_number);
            $successCount++;
        } catch (\Throwable $e) {
            $this->error("\nLỗi câu #{$q->question_id}: " . $e->getMessage());
        }
        $bar->advance();
    }

    $bar->finish();
    $this->newLine();
    $this->info("Hoàn tất: Đã sinh thành công {$successCount}/{$total} file audio MP3!");
    return 0;
})->purpose('Tự động sinh file audio MP3 cho các câu hỏi Listening TOEIC từ transcript');
