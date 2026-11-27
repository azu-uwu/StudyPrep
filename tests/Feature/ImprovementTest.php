<?php

namespace Tests\Feature;

use App\Models\AnswerOption;
use App\Models\AttemptResponse;
use App\Models\ExamSection;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\QuestionType;
use App\Models\ScoreResult;
use App\Models\Skill;
use App\Models\TestAttempt;
use App\Models\User;
use App\Services\HistoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class ImprovementTest extends TestCase
{
    use CreatesStudyPrepSchema;
    use RefreshDatabase;

    private User $user1;

    private User $user2;

    private Skill $rcSkill;

    private Skill $lcSkill;

    private Skill $spSkill;

    private Skill $wrSkill;

    private ExamSection $part1Section;

    private ExamSection $part5Section;

    private ExamSection $part6Section;

    private ExamSection $part7Section;

    private ExamSection $speakingSection;

    private ExamSection $writingSection;

    private QuestionType $mcType;

    private QuestionType $essayType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createStudyPrepSchema();

        $this->user1 = User::query()->create([
            'user_name' => 'student_improve_1',
            'email' => 'improve1@studyprep.test',
            'password_hash' => bcrypt('secret123'),
            'full_name' => 'Student Improvement One',
            'energy' => 5,
        ]);

        $this->user2 = User::query()->create([
            'user_name' => 'student_improve_2',
            'email' => 'improve2@studyprep.test',
            'password_hash' => bcrypt('secret123'),
            'full_name' => 'Student Improvement Two',
            'energy' => 5,
        ]);

        $this->rcSkill = Skill::query()->create([
            'skill_id' => 9,
            'code' => 'RC',
            'skill_name' => 'Reading',
        ]);

        $this->lcSkill = Skill::query()->create([
            'skill_id' => 8,
            'code' => 'LC',
            'skill_name' => 'Listening',
        ]);

        $this->spSkill = Skill::query()->create([
            'skill_id' => 7,
            'code' => 'SPEAKING',
            'skill_name' => 'Speaking',
        ]);

        $this->wrSkill = Skill::query()->create([
            'skill_id' => 6,
            'code' => 'WRITING',
            'skill_name' => 'Writing',
        ]);

        $this->part1Section = ExamSection::query()->create([
            'skill_id' => $this->lcSkill->skill_id,
            'code' => 'PART1',
            'name' => 'Part 1: Mô tả hình ảnh',
            'sequence_no' => 1,
        ]);

        $this->part5Section = ExamSection::query()->create([
            'skill_id' => $this->rcSkill->skill_id,
            'code' => 'PART5',
            'name' => 'Part 5: Hoàn thành câu',
            'sequence_no' => 5,
        ]);

        $this->part6Section = ExamSection::query()->create([
            'skill_id' => $this->rcSkill->skill_id,
            'code' => 'PART6',
            'name' => 'Part 6: Hoàn thành đoạn văn',
            'sequence_no' => 6,
        ]);

        $this->part7Section = ExamSection::query()->create([
            'skill_id' => $this->rcSkill->skill_id,
            'code' => 'PART7',
            'name' => 'Part 7: Đọc hiểu văn bản',
            'sequence_no' => 7,
        ]);

        $this->speakingSection = ExamSection::query()->create([
            'skill_id' => $this->spSkill->skill_id,
            'code' => 'SP_PART1',
            'name' => 'Part 1: Read a text aloud',
            'sequence_no' => 1,
        ]);

        $this->writingSection = ExamSection::query()->create([
            'skill_id' => $this->wrSkill->skill_id,
            'code' => 'WR_PART1',
            'name' => 'Part 1: Write a sentence based on a picture',
            'sequence_no' => 1,
        ]);

        $this->mcType = QuestionType::query()->create([
            'name' => 'Multiple Choice',
            'response_mode' => 'SINGLE_CHOICE',
        ]);

        $this->essayType = QuestionType::query()->create([
            'name' => 'Constructed Response',
            'response_mode' => 'ESSAY',
        ]);
    }

    private function createQuestion(ExamSection $section, string $content, string $correctLabel = 'B', ?QuestionSet $qSet = null): Question
    {
        $q = Question::query()->create([
            'section_id' => $section->section_id,
            'question_type_id' => $this->mcType->question_type_id,
            'question_set_id' => $qSet?->question_set_id,
            'content' => $content,
            'explanation' => "Explanation for {$content}",
            'difficulty' => 'MEDIUM',
            'status' => Question::STATUS_ACTIVE,
        ]);

        foreach (['A', 'B', 'C', 'D'] as $label) {
            AnswerOption::query()->create([
                'question_id' => $q->question_id,
                'label' => $label,
                'content' => "Option {$label} for {$content}",
                'is_correct' => ($label === $correctLabel),
            ]);
        }

        return $q;
    }

    private function createPracticeAttempt(
        User $user,
        array $responses, // [['q' => Question, 'answer' => 'A'|null]]
        ?Carbon $startedAt = null,
        ?Carbon $endedAt = null,
        string $status = TestAttempt::STATUS_COMPLETED
    ): TestAttempt {
        $started = $startedAt ?? now()->subMinutes(30);
        $ended = $endedAt ?? $started->copy()->addMinutes(15);

        $attempt = TestAttempt::query()->create([
            'user_id' => $user->user_id,
            'test_form_id' => null,
            'session_type' => TestAttempt::SESSION_TYPE_PRACTICE,
            'started_at' => $started,
            'ended_at' => $ended,
            'submitted_at' => $ended,
            'status' => $status,
        ]);

        $totalScore = 0;
        foreach ($responses as $idx => $r) {
            $q = $r['q'];
            $ans = $r['answer'] ?? null;
            $correctOpt = $q->options()->where('is_correct', true)->first();
            $isCorrect = ($ans !== null && strtoupper(trim((string) $ans)) === strtoupper(trim((string) $correctOpt?->label)));
            $score = $isCorrect ? 1.00 : 0.00;
            $totalScore += $score;

            AttemptResponse::query()->create([
                'attempt_id' => $attempt->attempt_id,
                'question_id' => $q->question_id,
                'sequence_no' => $idx + 1,
                'text_response' => $ans,
                'score' => $score,
                'is_flagged' => false,
            ]);
        }

        ScoreResult::query()->create([
            'attempt_id' => $attempt->attempt_id,
            'total_score' => $totalScore,
            'graded_at' => $ended,
        ]);

        return $attempt;
    }

    public function test_guest_is_redirected_to_login_when_accessing_improvement_page(): void
    {
        $response = $this->get('/cai-thien');
        $response->assertRedirect('/login');
    }

    public function test_user_with_no_wrong_questions_sees_empty_state_and_zero_count(): void
    {
        $response = $this->actingAs($this->user1)->get('/cai-thien');

        $response->assertOk();
        $response->assertSee('0');
        $response->assertSee('câu');
        $response->assertSee('Hiện chưa có câu sai');
        $response->assertSee('Chưa có dữ liệu phân tích');
    }

    public function test_user_with_wrong_questions_sees_only_matching_parts_and_correct_counts(): void
    {
        // Tạo câu hỏi Part 1 và Part 5
        $qP1_1 = $this->createQuestion($this->part1Section, 'Part 1 Question 1', 'A');
        $qP1_2 = $this->createQuestion($this->part1Section, 'Part 1 Question 2', 'B');
        $qP5_1 = $this->createQuestion($this->part5Section, 'Part 5 Question 1', 'C');
        $qP5_2 = $this->createQuestion($this->part5Section, 'Part 5 Question 2', 'D');

        // User 1 làm sai qP1_1 (chọn B thay vì A), đúng qP1_2
        $this->createPracticeAttempt($this->user1, [
            ['q' => $qP1_1, 'answer' => 'B'],
            ['q' => $qP1_2, 'answer' => 'B'],
        ]);

        // User 1 làm sai cả 2 câu Part 5
        $this->createPracticeAttempt($this->user1, [
            ['q' => $qP5_1, 'answer' => 'A'],
            ['q' => $qP5_2, 'answer' => 'A'],
        ]);

        $response = $this->actingAs($this->user1)->get('/cai-thien');

        $response->assertOk();
        // Tổng số câu sai: 1 (Part 1) + 2 (Part 5) = 3 câu
        $response->assertSee('3');
        $response->assertSee('câu');
        $response->assertSee('Part 1: Mô tả hình');
        $response->assertSee('Part 5: Hoàn thành câu');
        // Part 6 và Part 7 chưa có câu sai nên KHÔNG được hiển thị
        $response->assertDontSee('Part 6: Hoàn thành đoạn văn');
        $response->assertDontSee('Part 7: Đọc hiểu văn bản');
    }

    public function test_speaking_and_writing_questions_are_not_displayed_nor_counted(): void
    {
        // Tạo câu hỏi RC và Speaking/Writing
        $qP5 = $this->createQuestion($this->part5Section, 'Part 5 Question', 'A');

        $qSp = Question::query()->create([
            'section_id' => $this->speakingSection->section_id,
            'question_type_id' => $this->essayType->question_type_id,
            'content' => 'Speaking Prompt 1',
            'difficulty' => 'MEDIUM',
            'status' => Question::STATUS_ACTIVE,
        ]);

        $qWr = Question::query()->create([
            'section_id' => $this->writingSection->section_id,
            'question_type_id' => $this->essayType->question_type_id,
            'content' => 'Writing Prompt 1',
            'difficulty' => 'MEDIUM',
            'status' => Question::STATUS_ACTIVE,
        ]);

        // Tạo bài làm: sai Part 5, và có bản ghi Speaking/Writing với điểm 0
        $this->createPracticeAttempt($this->user1, [
            ['q' => $qP5, 'answer' => 'C'], // sai
            ['q' => $qSp, 'answer' => 'Incomplete audio', 'score' => 0.0],
            ['q' => $qWr, 'answer' => 'Wrong text', 'score' => 0.0],
        ]);

        $historyService = app(HistoryService::class);
        $totalWrong = $historyService->countAccumulatedWrongQuestions($this->user1->user_id);
        $this->assertSame(1, $totalWrong);

        $response = $this->actingAs($this->user1)->get('/cai-thien');
        $response->assertOk();
        $response->assertSee('1');
        $response->assertSee('câu');
        $response->assertDontSee('Read a text aloud');
        $response->assertDontSee('Write a sentence based on a picture');
    }

    public function test_total_wrong_count_matches_between_history_and_improvement_pages(): void
    {
        $qP1 = $this->createQuestion($this->part1Section, 'Listening Q1', 'A');
        $qP5 = $this->createQuestion($this->part5Section, 'Reading Q1', 'B');

        $this->createPracticeAttempt($this->user1, [
            ['q' => $qP1, 'answer' => 'D'], // sai
            ['q' => $qP5, 'answer' => 'A'], // sai
        ]);

        $historyRes = $this->actingAs($this->user1)->get('/lich-su');
        $historyRes->assertOk();
        $historyRes->assertSee('2');
        $historyRes->assertSee('câu');

        $improveRes = $this->actingAs($this->user1)->get('/cai-thien');
        $improveRes->assertOk();
        $improveRes->assertSee('2');
        $improveRes->assertSee('câu');
    }

    public function test_duplicate_wrong_attempts_on_same_question_only_count_once(): void
    {
        $qP5 = $this->createQuestion($this->part5Section, 'Repeated Wrong Q', 'A');

        // Làm sai lần 1
        $this->createPracticeAttempt($this->user1, [
            ['q' => $qP5, 'answer' => 'B'],
        ], now()->subHours(3));

        // Làm sai lần 2
        $this->createPracticeAttempt($this->user1, [
            ['q' => $qP5, 'answer' => 'C'],
        ], now()->subHours(1));

        $historyService = app(HistoryService::class);
        $wrongCount = $historyService->countAccumulatedWrongQuestions($this->user1->user_id);
        $this->assertSame(1, $wrongCount);

        $response = $this->actingAs($this->user1)->get('/cai-thien');
        $response->assertOk();
        $response->assertSee('1');
        $response->assertSee('câu');
    }

    public function test_xem_ket_qua_endpoint_returns_json_with_accumulated_wrong_questions_for_selected_part(): void
    {
        $qP5 = $this->createQuestion($this->part5Section, 'Part 5 Question Detail', 'B');

        $this->createPracticeAttempt($this->user1, [
            ['q' => $qP5, 'answer' => 'A'],
        ]);

        $response = $this->actingAs($this->user1)->getJson('/cai-thien/cau-sai/'.$this->part5Section->section_id);

        $response->assertOk();
        $response->assertJsonStructure([
            'part' => [
                'section_id',
                'section_code',
                'title',
                'skill_code',
                'wrong_count',
                'questions' => [
                    '*' => [
                        'id',
                        'question_number',
                        'stem',
                        'options',
                        'user_answer',
                        'correct_answer',
                        'explanation',
                    ],
                ],
            ],
        ]);

        $data = $response->json('part');
        $this->assertSame(1, $data['wrong_count']);
        $this->assertSame('A', $data['questions'][0]['user_answer']);
        $this->assertSame('B', $data['questions'][0]['correct_answer']);
        $this->assertSame('Explanation for Part 5 Question Detail', $data['questions'][0]['explanation']);
    }

    public function test_user_cannot_access_another_users_wrong_questions_via_endpoint(): void
    {
        $qP5 = $this->createQuestion($this->part5Section, 'User 1 Question', 'A');

        // Chỉ user 1 làm sai
        $this->createPracticeAttempt($this->user1, [
            ['q' => $qP5, 'answer' => 'C'],
        ]);

        // User 2 truy vấn kết quả của Section này: trả về 404 vì user 2 không có câu sai trong phần này
        $response = $this->actingAs($this->user2)->getJson('/cai-thien/cau-sai/'.$this->part5Section->section_id);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Không tìm thấy câu sai thuộc phần này hoặc phần chưa được luyện tập.',
        ]);
    }

    public function test_review_mode_validates_count_and_redirects_if_exceeding_wrong_count(): void
    {
        $qP5 = $this->createQuestion($this->part5Section, 'Single Wrong Question', 'B');

        $this->createPracticeAttempt($this->user1, [
            ['q' => $qP5, 'answer' => 'C'],
        ]);

        // Yêu cầu ôn 5 câu nhưng chỉ có 1 câu sai
        $response = $this->actingAs($this->user1)->get('/luyen-tap/doc/hoan-thanh-cau?part=5&count=5&mode=review');

        $response->assertRedirect('/luyen-tap/doc?part=5&mode=review');
        $response->assertSessionHas('practice_error', 'Số câu ôn lại không được vượt quá số câu sai hiện có (1 câu).');
    }

    public function test_review_mode_loads_only_wrong_questions(): void
    {
        $qP5_1 = $this->createQuestion($this->part5Section, 'Wrong P5 Question', 'A');
        $qP5_2 = $this->createQuestion($this->part5Section, 'Correct P5 Question', 'B');

        $this->createPracticeAttempt($this->user1, [
            ['q' => $qP5_1, 'answer' => 'C'], // sai
            ['q' => $qP5_2, 'answer' => 'B'], // đúng
        ]);

        $response = $this->actingAs($this->user1)->get('/luyen-tap/doc/hoan-thanh-cau?part=5&count=1&mode=review');

        $response->assertOk();
        $response->assertViewHas('questions', function ($questions) use ($qP5_1, $qP5_2) {
            $ids = collect($questions)->pluck('id')->all();

            return in_array($qP5_1->question_id, $ids, true)
                && ! in_array($qP5_2->question_id, $ids, true);
        });
    }

    public function test_submitting_review_creates_new_attempt_without_overwriting_previous_attempts(): void
    {
        $qP5 = $this->createQuestion($this->part5Section, 'Retry Test Question', 'A');

        $originalAttempt = $this->createPracticeAttempt($this->user1, [
            ['q' => $qP5, 'answer' => 'C'],
        ], now()->subDay());

        $this->assertSame(1, TestAttempt::query()->where('user_id', $this->user1->user_id)->count());

        // Bắt đầu phiên ôn lại
        $this->actingAs($this->user1)->get('/luyen-tap/doc/hoan-thanh-cau?part=5&count=1&mode=review');

        // Nộp bài ôn lại (lần này trả lời đúng A)
        $submitRes = $this->actingAs($this->user1)->post('/luyen-tap/doc/nop-bai', [
            'answers' => [
                $qP5->question_id => 'A',
            ],
            'time_spent' => 45,
        ]);

        $this->assertStringContainsString('/luyen-tap/doc/ket-qua', (string) $submitRes->headers->get('Location'));

        // Phải có 2 attempts riêng biệt trong CSDL
        $this->assertSame(2, TestAttempt::query()->where('user_id', $this->user1->user_id)->count());

        $newAttempt = TestAttempt::query()
            ->where('user_id', $this->user1->user_id)
            ->where('attempt_id', '!=', $originalAttempt->attempt_id)
            ->latest('attempt_id')
            ->first();

        $this->assertNotNull($newAttempt);
        $this->assertSame(TestAttempt::STATUS_COMPLETED, $newAttempt->status);

        // Bản ghi attempt cũ không bị thay đổi
        $this->assertSame('C', $originalAttempt->attemptResponses()->first()->text_response);
    }
}
