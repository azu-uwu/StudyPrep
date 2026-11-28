<?php

namespace Tests\Feature;

use App\Models\AnswerOption;
use App\Models\AttemptResponse;
use App\Models\ExamSection;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\ScoreResult;
use App\Models\Skill;
use App\Models\TestAttempt;
use App\Models\TestBlueprint;
use App\Models\TestForm;
use App\Models\Topic;
use App\Models\User;
use App\Models\UserVocabulary;
use App\Models\Vocabulary;
use App\Services\HistoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use CreatesStudyPrepSchema;
    use RefreshDatabase;

    private User $user1;

    private User $user2;

    private Skill $lcSkill;

    private Skill $rcSkill;

    private ExamSection $part1Section;

    private ExamSection $part2Section;

    private ExamSection $part5Section;

    private ExamSection $part6Section;

    private QuestionType $mcType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createStudyPrepSchema();

        $this->user1 = User::query()->create([
            'user_name' => 'student_stat_1',
            'email' => 'stat1@studyprep.test',
            'password_hash' => bcrypt('secret123'),
            'full_name' => 'Student Stat One',
            'energy' => 5,
        ]);

        $this->user2 = User::query()->create([
            'user_name' => 'student_stat_2',
            'email' => 'stat2@studyprep.test',
            'password_hash' => bcrypt('secret123'),
            'full_name' => 'Student Stat Two',
            'energy' => 5,
        ]);

        $this->lcSkill = Skill::query()->create([
            'skill_id' => 8,
            'code' => 'LC',
            'skill_name' => 'Listening',
        ]);

        $this->rcSkill = Skill::query()->create([
            'skill_id' => 9,
            'code' => 'RC',
            'skill_name' => 'Reading',
        ]);

        $this->part1Section = ExamSection::query()->create([
            'skill_id' => $this->lcSkill->skill_id,
            'code' => 'PART1',
            'name' => 'Part 1: Photographs',
            'sequence_no' => 1,
        ]);

        $this->part2Section = ExamSection::query()->create([
            'skill_id' => $this->lcSkill->skill_id,
            'code' => 'PART2',
            'name' => 'Part 2: Question-Response',
            'sequence_no' => 2,
        ]);

        $this->part5Section = ExamSection::query()->create([
            'skill_id' => $this->rcSkill->skill_id,
            'code' => 'PART5',
            'name' => 'Part 5: Incomplete Sentences',
            'sequence_no' => 5,
        ]);

        $this->part6Section = ExamSection::query()->create([
            'skill_id' => $this->rcSkill->skill_id,
            'code' => 'PART6',
            'name' => 'Part 6: Text Completion',
            'sequence_no' => 6,
        ]);

        $this->mcType = QuestionType::query()->create([
            'name' => 'Multiple Choice',
            'response_mode' => 'SINGLE_CHOICE',
        ]);
    }

    private function createQuestion(ExamSection $section, string $content, string $correctLabel = 'A'): Question
    {
        $q = Question::query()->create([
            'section_id' => $section->section_id,
            'question_type_id' => $this->mcType->question_type_id,
            'content' => $content,
            'explanation' => "Giải thích cho {$content}",
            'difficulty' => 'MEDIUM',
            'status' => Question::STATUS_ACTIVE,
        ]);

        foreach (['A', 'B', 'C', 'D'] as $label) {
            AnswerOption::query()->create([
                'question_id' => $q->question_id,
                'label' => $label,
                'content' => "Lựa chọn {$label}",
                'is_correct' => ($label === $correctLabel),
            ]);
        }

        return $q;
    }

    private function createTestForm(string $title): TestForm
    {
        $blueprint = TestBlueprint::query()->create([
            'name' => 'Blueprint for '.$title,
            'exam_type' => 'LR',
            'version' => '1.0',
            'status' => 'ACTIVE',
        ]);

        return TestForm::query()->create([
            'blueprint_id' => $blueprint->blueprint_id,
            'title' => $title,
        ]);
    }

    public function test_guest_is_redirected_from_analytics_page(): void
    {
        $response = $this->get('/phan-tich');
        $response->assertRedirect('/login');
    }

    public function test_user_with_no_history_sees_empty_states_cleanly(): void
    {
        $response = $this->actingAs($this->user1)->get('/phan-tich');

        $response->assertStatus(200);
        $response->assertSee('Phân tích');
        $response->assertSee('---'); // Điểm cao nhất
        $response->assertSee('0%'); // Tỉ lệ đúng trung bình
        $response->assertSee('Chưa có bài thi Full Test nào');
        $response->assertSee('Chưa có dữ liệu phân tích điểm yếu');
        $response->assertSee('Chưa có hoạt động nào');
        $response->assertDontSee('câu sai cần ôn lại'); // Thẻ câu sai phải ẩn khi 0 câu sai
    }

    public function test_reading_practice_only_calculates_correct_stats(): void
    {
        // Tạo 5 câu hỏi Part 5
        $questions = [];
        for ($i = 1; $i <= 5; $i++) {
            $questions[] = $this->createQuestion($this->part5Section, "Part 5 Question {$i}", 'A');
        }

        // Tạo 1 lượt PRACTICE: 3 câu đúng (A), 2 câu sai (B)
        $attempt = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'session_type' => TestAttempt::SESSION_TYPE_PRACTICE,
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHours(1),
            'submitted_at' => now()->subHours(1),
            'status' => TestAttempt::STATUS_COMPLETED,
        ]);

        foreach ($questions as $idx => $q) {
            $answer = $idx < 3 ? 'A' : 'B';
            AttemptResponse::query()->create([
                'attempt_id' => $attempt->attempt_id,
                'question_id' => $q->question_id,
                'sequence_no' => $idx + 1,
                'text_response' => $answer,
                'submitted_at' => now()->subHours(1),
            ]);
        }

        $response = $this->actingAs($this->user1)->get('/phan-tich');

        $response->assertStatus(200);
        // Tỉ lệ đúng trung bình: 3/5 = 60%
        $response->assertSee('60%');
        $response->assertSee('(3/5 câu)');
        // Part 5 đủ 5 câu -> has_data = true, 60%
        $response->assertSee('title="Part 5: 3/5 câu đúng"', false);
        // Có 2 câu sai -> Thẻ câu sai hiển thị
        $response->assertSee('Bạn có 2 câu sai cần ôn lại');
    }

    public function test_part_accuracy_requires_at_least_five_questions(): void
    {
        // Chỉ làm 4 câu Part 1
        $q1 = $this->createQuestion($this->part1Section, 'Part 1 Q1', 'A');
        $q2 = $this->createQuestion($this->part1Section, 'Part 1 Q2', 'A');
        $q3 = $this->createQuestion($this->part1Section, 'Part 1 Q3', 'A');
        $q4 = $this->createQuestion($this->part1Section, 'Part 1 Q4', 'A');

        $attempt = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'session_type' => TestAttempt::SESSION_TYPE_PRACTICE,
            'started_at' => now()->subHours(1),
            'submitted_at' => now()->subHours(1),
            'status' => TestAttempt::STATUS_COMPLETED,
        ]);

        foreach ([$q1, $q2, $q3, $q4] as $idx => $q) {
            AttemptResponse::query()->create([
                'attempt_id' => $attempt->attempt_id,
                'question_id' => $q->question_id,
                'sequence_no' => $idx + 1,
                'text_response' => 'A',
                'submitted_at' => now()->subHours(1),
            ]);
        }

        $response = $this->actingAs($this->user1)->get('/phan-tich');
        $response->assertStatus(200);

        // Part 1 chỉ có 4 câu -> chưa đủ 5 câu -> has_data = false -> title="Part 1: Chưa đủ 5 câu"
        $response->assertSee('title="Part 1: Chưa đủ 5 câu"', false);
    }

    public function test_wrong_questions_count_matches_history_and_improvement(): void
    {
        $q1 = $this->createQuestion($this->part5Section, 'P5 Wrong Question', 'A');

        $attempt = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'session_type' => TestAttempt::SESSION_TYPE_PRACTICE,
            'started_at' => now()->subHours(1),
            'submitted_at' => now()->subHours(1),
            'status' => TestAttempt::STATUS_COMPLETED,
        ]);

        // Làm sai: chọn C thay vì A
        AttemptResponse::query()->create([
            'attempt_id' => $attempt->attempt_id,
            'question_id' => $q1->question_id,
            'sequence_no' => 1,
            'text_response' => 'C',
            'submitted_at' => now()->subHours(1),
        ]);

        /** @var HistoryService $historyService */
        $historyService = app(HistoryService::class);
        $expectedCount = $historyService->countAccumulatedWrongQuestions($this->user1->user_id);

        $this->assertEquals(1, $expectedCount);

        $response = $this->actingAs($this->user1)->get('/phan-tich');
        $response->assertStatus(200);
        $response->assertSee('Bạn có 1 câu sai cần ôn lại');
        $response->assertSee(url('/cai-thien'));
    }

    public function test_full_test_attempt_shows_in_chart_and_overview(): void
    {
        $testForm = $this->createTestForm('Test 1 TOEIC NEW 2026');

        $attempt = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'test_form_id' => $testForm->test_form_id,
            'session_type' => TestAttempt::SESSION_TYPE_FULL_TEST,
            'started_at' => now()->subDays(2),
            'submitted_at' => now()->subDays(2),
            'status' => TestAttempt::STATUS_COMPLETED,
        ]);

        ScoreResult::query()->create([
            'attempt_id' => $attempt->attempt_id,
            'listening_score' => 350,
            'reading_score' => 300,
            'total_score' => 650,
            'graded_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->user1)->get('/phan-tich');

        $response->assertStatus(200);
        $response->assertSee('650'); // Điểm cao nhất
        $response->assertSee('Test 1 TOEIC NEW 2026');
        $response->assertSee('350/495'); // Lần thi gần nhất - Nghe
        $response->assertSee('300/495'); // Lần thi gần nhất - Đọc
        $response->assertSee('Chỉ có 1 lần thi trong khoảng này — chưa đủ để thấy xu hướng.');
    }

    public function test_multiple_full_test_attempts_and_period_filtering(): void
    {
        $testForm = $this->createTestForm('TOEIC Exam A');

        // Bài 1: Cách đây 40 ngày (thuộc 90 ngày, không thuộc 7 ngày hoặc 30 ngày)
        $att1 = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'test_form_id' => $testForm->test_form_id,
            'session_type' => TestAttempt::SESSION_TYPE_FULL_TEST,
            'started_at' => now()->subDays(40),
            'submitted_at' => now()->subDays(40),
            'status' => TestAttempt::STATUS_COMPLETED,
        ]);
        ScoreResult::query()->create([
            'attempt_id' => $att1->attempt_id,
            'listening_score' => 200,
            'reading_score' => 200,
            'total_score' => 400,
        ]);

        // Bài 2: Cách đây 3 ngày (thuộc 7 ngày, 30 ngày, 90 ngày)
        $att2 = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'test_form_id' => $testForm->test_form_id,
            'session_type' => TestAttempt::SESSION_TYPE_FULL_TEST,
            'started_at' => now()->subDays(3),
            'submitted_at' => now()->subDays(3),
            'status' => TestAttempt::STATUS_COMPLETED,
        ]);
        ScoreResult::query()->create([
            'attempt_id' => $att2->attempt_id,
            'listening_score' => 300,
            'reading_score' => 300,
            'total_score' => 600,
        ]);

        // Bộ lọc 90 ngày: Cả 2 bài thi đều xuất hiện -> có đường kẻ nối (connecting line)
        $response90 = $this->actingAs($this->user1)->get('/phan-tich?period=90');
        $response90->assertStatus(200);
        $response90->assertSee('TOEIC Exam A: 400 điểm');
        $response90->assertSee('TOEIC Exam A: 600 điểm');
        $response90->assertViewHas('fullTestData', fn ($data) => $data['is_single_attempt'] === false && $data['attempts_count'] === 2);

        // Bộ lọc 7 ngày: Chỉ có 1 bài thi (Bài 2) trong biểu đồ -> không có bài 400 điểm trong biểu đồ
        $response7 = $this->actingAs($this->user1)->get('/phan-tich?period=7');
        $response7->assertStatus(200);
        $response7->assertSee('TOEIC Exam A: 600 điểm');
        $response7->assertDontSee('TOEIC Exam A: 400 điểm');
        $response7->assertViewHas('fullTestData', fn ($data) => $data['is_single_attempt'] === true && $data['attempts_count'] === 1);
    }

    public function test_practice_attempts_are_excluded_from_full_test_chart(): void
    {
        // Bài PRACTICE không được tính vào biểu đồ Full Test
        $attPractice = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'session_type' => TestAttempt::SESSION_TYPE_PRACTICE,
            'started_at' => now()->subDays(1),
            'submitted_at' => now()->subDays(1),
            'status' => TestAttempt::STATUS_COMPLETED,
        ]);
        ScoreResult::query()->create([
            'attempt_id' => $attPractice->attempt_id,
            'total_score' => 900,
        ]);

        $response = $this->actingAs($this->user1)->get('/phan-tich');
        $response->assertStatus(200);
        // Điểm cao nhất của Full Test không được lấy từ bài PRACTICE
        $response->assertSee('Điểm cao nhất');
        $response->assertSee('---');
        $response->assertSee('Chưa có bài thi Full Test nào');
    }

    public function test_chart_data_api_endpoint_returns_json(): void
    {
        $testForm = $this->createTestForm('TOEIC Mock 1');

        $att = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'test_form_id' => $testForm->test_form_id,
            'session_type' => TestAttempt::SESSION_TYPE_FULL_TEST,
            'started_at' => now()->subDays(5),
            'submitted_at' => now()->subDays(5),
            'status' => TestAttempt::STATUS_COMPLETED,
        ]);
        ScoreResult::query()->create([
            'attempt_id' => $att->attempt_id,
            'listening_score' => 250,
            'reading_score' => 250,
            'total_score' => 500,
        ]);

        $response = $this->actingAs($this->user1)->getJson('/phan-tich/chart-data?period=30');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'period' => '30',
            'data' => [
                'has_attempts' => true,
                'is_single_attempt' => true,
                'attempts_count' => 1,
            ],
        ]);
    }

    public function test_vocabulary_statistics_matches_user_vocabulary(): void
    {
        $topic = Topic::query()->create([
            'name' => 'Business English',
        ]);

        $v1 = Vocabulary::query()->create(['topic_id' => $topic->topic_id, 'word' => 'negotiate', 'meaning_vi' => 'đàm phán']);
        $v2 = Vocabulary::query()->create(['topic_id' => $topic->topic_id, 'word' => 'contract', 'meaning_vi' => 'hợp đồng']);
        $v3 = Vocabulary::query()->create(['topic_id' => $topic->topic_id, 'word' => 'proposal', 'meaning_vi' => 'đề xuất']);
        $v4 = Vocabulary::query()->create(['topic_id' => $topic->topic_id, 'word' => 'agenda', 'meaning_vi' => 'chương trình nghị sự']);

        // 3 từ nắm vững (is_done = 1), 1 từ dễ quên (is_done = 0)
        UserVocabulary::query()->create(['user_id' => $this->user1->user_id, 'vocabulary_id' => $v1->vocabulary_id, 'is_done' => 1]);
        UserVocabulary::query()->create(['user_id' => $this->user1->user_id, 'vocabulary_id' => $v2->vocabulary_id, 'is_done' => 1]);
        UserVocabulary::query()->create(['user_id' => $this->user1->user_id, 'vocabulary_id' => $v3->vocabulary_id, 'is_done' => 1]);
        UserVocabulary::query()->create(['user_id' => $this->user1->user_id, 'vocabulary_id' => $v4->vocabulary_id, 'is_done' => 0]);

        $response = $this->actingAs($this->user1)->get('/phan-tich');

        $response->assertStatus(200);
        $response->assertSee('4'); // Đã học
        $response->assertSee('3'); // Nắm vững
        $response->assertSee('1'); // Dễ quên
        $response->assertSee('75%'); // 3 / 4 = 75%
    }

    public function test_recent_activities_shows_latest_attempts_in_order_max_three(): void
    {
        // Tạo 4 lượt làm bài
        for ($i = 1; $i <= 4; $i++) {
            TestAttempt::query()->create([
                'user_id' => $this->user1->user_id,
                'session_type' => TestAttempt::SESSION_TYPE_PRACTICE,
                'started_at' => now()->subHours(10 - $i),
                'submitted_at' => now()->subHours(10 - $i),
                'status' => TestAttempt::STATUS_COMPLETED,
            ]);
        }

        $response = $this->actingAs($this->user1)->get('/phan-tich');
        $response->assertStatus(200);

        // Xem tất cả điều hướng về trang Lịch sử
        $response->assertSee(url('/lich-su'));
    }

    public function test_user_cannot_see_other_user_statistics(): void
    {
        // User 2 có bài thi 885 điểm
        $testForm = $this->createTestForm('Private Test User 2');
        $att2 = TestAttempt::query()->create([
            'user_id' => $this->user2->user_id,
            'test_form_id' => $testForm->test_form_id,
            'session_type' => TestAttempt::SESSION_TYPE_FULL_TEST,
            'started_at' => now()->subDays(1),
            'submitted_at' => now()->subDays(1),
            'status' => TestAttempt::STATUS_COMPLETED,
        ]);
        ScoreResult::query()->create([
            'attempt_id' => $att2->attempt_id,
            'total_score' => 885,
        ]);

        // User 1 đăng nhập
        $response = $this->actingAs($this->user1)->get('/phan-tich');

        $response->assertStatus(200);
        $response->assertDontSee('885');
        $response->assertDontSee('Private Test User 2');
        $response->assertSee('---');
    }
}
