<?php

namespace Tests\Feature;

use App\Models\AnswerOption;
use App\Models\ExamSection;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\QuestionType;
use App\Models\Skill;
use App\Services\TextToSpeechService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class TextToSpeechTest extends TestCase
{
    use CreatesStudyPrepSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createStudyPrepSchema();
    }

    public function test_tts_service_generates_mp3_file(): void
    {
        $service = new TextToSpeechService;
        $sampleText = 'Look at the picture marked No. 1 in your test book. She is eating in a picnic area.';

        $result = $service->generateAudio($sampleText, 1, 'test_part1_q1');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('audio_url', $result);
        $this->assertArrayHasKey('file_path', $result);
        $this->assertStringContainsString('/storage/audio/listening/part_1/', $result['audio_url']);
        $this->assertTrue(File::exists($result['file_path']));
        $this->assertGreaterThan(500, $result['file_size']);
    }

    public function test_api_generate_tts_endpoint(): void
    {
        $response = $this->postJson('/api/admin/questions/generate-tts', [
            'text' => 'Can you come to my jazz performance tonight?',
            'part_number' => 2,
            'custom_filename' => 'api_test_part2',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'part_number' => 2,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'audio_url',
            'file_path',
            'file_size',
            'duration_estimate',
        ]);

        $audioUrl = $response->json('audio_url');
        $this->assertStringContainsString('/storage/audio/listening/part_2/', $audioUrl);
    }

    public function test_api_generate_tts_validation_error_when_no_text(): void
    {
        $response = $this->postJson('/api/admin/questions/generate-tts', []);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_build_toeic_listening_script_and_batch_tts_preserves_image_media_url(): void
    {
        $skill = Skill::create(['skill_id' => 1, 'code' => 'LC', 'skill_name' => 'Listening']);
        $part1 = ExamSection::create(['section_id' => 1, 'skill_id' => 1, 'code' => 'PART1', 'name' => 'Part 1', 'sequence_no' => 1]);
        $part2 = ExamSection::create(['section_id' => 2, 'skill_id' => 1, 'code' => 'PART2', 'name' => 'Part 2', 'sequence_no' => 2]);
        $part4 = ExamSection::create(['section_id' => 4, 'skill_id' => 1, 'code' => 'PART4', 'name' => 'Part 4', 'sequence_no' => 4]);
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        // Part 1: Có cả ảnh lẫn các phương án A-D
        $setP1 = QuestionSet::create([
            'title' => 'Part 1 Photo',
            'media_url' => 'storage/questions/imported/p1.png',
            'media_type' => 'IMAGE',
        ]);
        $q1 = Question::create([
            'section_id' => $part1->section_id,
            'question_type_id' => 1,
            'question_set_id' => $setP1->question_set_id,
            'content' => 'Look at the picture.',
            'difficulty' => 'EASY',
            'status' => 'ACTIVE',
        ]);
        AnswerOption::create(['question_id' => $q1->question_id, 'label' => 'A', 'content' => 'She is typing on a laptop.', 'is_correct' => true]);
        AnswerOption::create(['question_id' => $q1->question_id, 'label' => 'B', 'content' => 'She is reading a book.', 'is_correct' => false]);

        // Part 2: Đọc câu hỏi + 3 phương án A-C
        $q2 = Question::create([
            'section_id' => $part2->section_id,
            'question_type_id' => 1,
            'content' => 'Where is the meeting room?',
            'difficulty' => 'EASY',
            'status' => 'ACTIVE',
        ]);
        AnswerOption::create(['question_id' => $q2->question_id, 'label' => 'A', 'content' => 'On the second floor.', 'is_correct' => true]);
        AnswerOption::create(['question_id' => $q2->question_id, 'label' => 'B', 'content' => 'At 2 PM.', 'is_correct' => false]);
        AnswerOption::create(['question_id' => $q2->question_id, 'label' => 'C', 'content' => 'Yes, I did.', 'is_correct' => false]);

        // Part 4: Có biểu đồ (IMAGE) + transcript bài nói
        $setP4 = QuestionSet::create([
            'title' => 'Part 4 Chart',
            'media_url' => 'storage/questions/imported/chart4.png',
            'media_type' => 'IMAGE',
            'transcript' => 'Welcome everyone to the annual sales conference.',
        ]);
        $q4 = Question::create([
            'section_id' => $part4->section_id,
            'question_type_id' => 1,
            'question_set_id' => $setP4->question_set_id,
            'content' => 'What is the event about?',
            'difficulty' => 'MEDIUM',
            'status' => 'ACTIVE',
        ]);

        $ttsService = app(TextToSpeechService::class);
        $p1Script = $ttsService->buildToeicListeningScript($setP1->fresh());
        $this->assertStringContainsString('(A) She is typing on a laptop.', $p1Script);
        $this->assertStringContainsString('(B) She is reading a book.', $p1Script);

        $p4Script = $ttsService->buildToeicListeningScript($setP4->fresh());
        $this->assertEquals('Welcome everyone to the annual sales conference.', $p4Script);

        // Gọi batch endpoint cho cả 3 câu
        $response = $this->postJson('/api/admin/questions/batch-generate-tts', [
            'question_ids' => [$q1->question_id, $q2->question_id, $q4->question_id],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'processed' => 3,
            'succeeded' => 3,
            'failed' => 0,
        ]);

        // Ảnh Part 1 & Part 4 được giữ nguyên ở media_url, MP3 lưu riêng ở audio_url!
        $setP1->refresh();
        $this->assertEquals('storage/questions/imported/p1.png', $setP1->media_url);
        $this->assertNotEmpty($setP1->audio_url);
        $this->assertStringContainsString('audio/listening/part_1/', (string) $setP1->audio_url);

        $setP4->refresh();
        $this->assertEquals('storage/questions/imported/chart4.png', $setP4->media_url);
        $this->assertNotEmpty($setP4->audio_url);
        $this->assertStringContainsString('audio/listening/part_4/', (string) $setP4->audio_url);

        // Kiểm tra Student Listening Practice đọc đúng dữ liệu thật từ DB
        $practiceRes = $this->get(route('luyen-tap.nghe.lam-bai'));
        $practiceRes->assertOk();
        $practiceRes->assertSee('toeic-audio-player.js');
        $practiceRes->assertSee('storage/questions/imported/chart4.png');
    }
}
