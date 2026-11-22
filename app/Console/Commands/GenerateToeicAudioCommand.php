<?php

namespace App\Console\Commands;

use App\Models\QuestionSet;
use App\Services\TextToSpeechService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateToeicAudioCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'toeic:generate-audio {--test=1 : ID de thi TOEIC can sinh audio} {--force : Ghi de file am thanh da ton tai}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinh file am thanh MP3 thuc te cho cac bo cau hoi TOEIC Listening Test 1 va luu vao storage';

    /**
     * Execute the console command.
     */
    public function handle(TextToSpeechService $ttsService): int
    {
        $testNum = (int) $this->option('test');
        $force = (bool) $this->option('force');

        $this->info("=== BAT DAU SINH AUDIO MP3 CHO TOEIC ACTUAL TEST {$testNum} ===");

        // Thu muc dich: storage/app/public/audio/listening/test1/
        $targetFolder = "audio/listening/test{$testNum}";
        $fullTargetDir = storage_path('app/public/'.$targetFolder);

        if (! File::isDirectory($fullTargetDir)) {
            File::makeDirectory($fullTargetDir, 0755, true, true);
        }

        // Lay tat ca QuestionSet thuoc test1
        $sets = QuestionSet::query()
            ->where('media_url', 'LIKE', "%/test{$testNum}/%")
            ->orWhere('title', 'LIKE', "%ACTUAL TEST {$testNum}%")
            ->orderBy('question_set_id')
            ->get();

        if ($sets->isEmpty()) {
            $this->warn("Khong tim thay QuestionSet nao thuoc Test {$testNum}. Dang quet tat ca QuestionSet Listening...");
            $sets = QuestionSet::query()
                ->where('media_type', 'AUDIO')
                ->orWhereNotNull('audio_url')
                ->orWhere('media_url', 'LIKE', '%.mp3%')
                ->orderBy('question_set_id')
                ->get();
        }

        $this->info("Tim thay {$sets->count()} question sets can kiem tra audio.");

        $succeeded = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($sets as $set) {
            // Trich xuat ten file mong muon tu media_url (VD: /storage/audio/listening/test1/part1_q1.mp3 -> part1_q1)
            $customFilename = null;
            if (! empty($set->media_url) && preg_match('/\/([a-zA-Z0-9_\-]+)\.mp3$/i', $set->media_url, $matches)) {
                $customFilename = $matches[1];
            } else {
                $customFilename = 'set_'.$set->question_set_id;
            }

            $expectedFilePath = $fullTargetDir.'/'.$customFilename.'.mp3';

            if (! $force && File::exists($expectedFilePath) && File::size($expectedFilePath) > 1000) {
                $this->line(" [SKIP] ID {$set->question_set_id} ({$customFilename}.mp3) da ton tai tren dia.");
                $skipped++;

                continue;
            }

            // Xay dung kich ban doc
            $script = $ttsService->buildToeicListeningScript($set);
            if (empty(trim($script))) {
                $this->warn(" [WARN] ID {$set->question_set_id}: Khong tim thay script/transcript.");
                $failed++;

                continue;
            }

            $this->line(" [GEN] ID {$set->question_set_id} ({$customFilename}) - Script: ".mb_substr($script, 0, 50).'...');

            try {
                $res = $ttsService->generateAudio(
                    text: $script,
                    partNumber: 1,
                    customFilename: $customFilename,
                    lang: 'en',
                    customFolder: $targetFolder
                );

                // Cap nhat audio_url trong CSDL neu can
                $relativePath = 'storage/'.$targetFolder.'/'.$customFilename.'.mp3';
                $set->audio_url = $relativePath;
                if (empty($set->media_url)) {
                    $set->media_url = '/'.$relativePath;
                }
                $set->save();

                $this->info("   -> THANH CONG: {$customFilename}.mp3 ({$res['file_size']} bytes, ~{$res['duration_estimate']}s)");
                $succeeded++;

                // Delay nhe 300ms giua cac request de bao ve ket noi
                usleep(300000);
            } catch (\Throwable $e) {
                $this->error("   -> THAT BAI ID {$set->question_set_id}: ".$e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("HOAN TAT: {$succeeded} file thanh cong, {$skipped} bo qua da co, {$failed} that bai.");

        return Command::SUCCESS;
    }
}
