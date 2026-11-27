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
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class HistoryTest extends TestCase
{
    use CreatesStudyPrepSchema;
    use RefreshDatabase;

    private User $user1;

    private User $user2;

    private Skill $rcSkill;

    private Skill $lcSkill;

    private ExamSection $part5Section;

    private ExamSection $part6Section;

    private ExamSection $part1Section;

    private QuestionType $mcType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createStudyPrepSchema();

        $this->user1 = User::query()->create([
            'user_name' => 'student_history_1',
            'email' => 'student1@studyprep.test',
            'password_hash' => bcrypt('secret123'),
            'full_name' => 'Student One',
            'energy' => 5,
        ]);

        $this->user2 = User::query()->create([
            'user_name' => 'student_history_2',
            'email' => 'student2@studyprep.test',
            'password_hash' => bcrypt('secret123'),
            'full_name' => 'Student Two',
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

        $this->part1Section = ExamSection::query()->create([
            'skill_id' => $this->lcSkill->skill_id,
            'code' => 'PART1',
            'name' => 'Part 1: Mô tả tranh',
            'sequence_no' => 1,
        ]);

        $this->mcType = QuestionType::query()->create([
            'name' => 'Multiple Choice',
            'response_mode' => 'SINGLE_CHOICE',
        ]);
    }

    private function createQuestion(ExamSection $section, string $content, string $correctLabel = 'B'): Question
    {
        $q = Question::query()->create([
            'section_id' => $section->section_id,
            'question_type_id' => $this->mcType->question_type_id,
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
        ExamSection $section,
        array $responses, // [['q' => Question, 'answer' => 'A'|null, 'score' => 1|0]]
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
            $isCorrect = ($ans !== null && strtoupper(trim($ans)) === strtoupper(trim($correctOpt->label)));
            $score = $isCorrect ? 1.00 : 0.00;
            $totalScore += $score;

            AttemptResponse::query()->create([
                'attempt_id' => $attempt->attempt_id,
                'question_id' => $q->question_id,
                'sequence_no' => $idx + 1,
                'text_response' => $ans,
                'score' => $score,
                'submitted_at' => $ended,
            ]);
        }

        ScoreResult::query()->create([
            'attempt_id' => $attempt->attempt_id,
            'reading_score' => $section->skill_id === $this->rcSkill->skill_id ? $totalScore : null,
            'listening_score' => $section->skill_id === $this->lcSkill->skill_id ? $totalScore : null,
            'total_score' => $totalScore,
            'graded_at' => $ended,
        ]);

        return $attempt;
    }

    /**
     * Test 1: Người dùng chưa có lịch sử (Empty State & 0 KPIs).
     */
    public function test_user_without_history_sees_empty_state_and_zero_kpis(): void
    {
        $response = $this->actingAs($this->user1)->get('/lich-su');

        $response->assertStatus(200);
        $response->assertSee('Chưa có lịch sử làm bài');
        $response->assertSee('Hãy luyện tập hoặc làm đề thi thử để bắt đầu theo dõi quá trình học tập của bạn.');
        $response->assertSee('0'); // activity count
        $response->assertSee('lượt làm bài');
        $response->assertSee('Chưa có dữ liệu'); // accuracy
        $response->assertSee('0 phút'); // study time
        $response->assertDontSee('Ôn ngay'); // no review button when 0 wrong questions
    }

    /**
     * Test 2 & 5: Hiển thị đúng 1 phiên Reading riêng biệt với dữ liệu thật.
     */
    public function test_user_with_single_reading_attempt_displays_correctly(): void
    {
        $q1 = $this->createQuestion($this->part5Section, 'Question 1', 'A');
        $q2 = $this->createQuestion($this->part5Section, 'Question 2', 'B');

        // q1: answered correctly ('A'), q2: answered wrongly ('C')
        $this->createPracticeAttempt($this->user1, $this->part5Section, [
            ['q' => $q1, 'answer' => 'A'],
            ['q' => $q2, 'answer' => 'C'],
        ], now()->subMinutes(20), now()->subMinutes(10));

        $response = $this->actingAs($this->user1)->get('/lich-su');

        $response->assertStatus(200);
        $response->assertSee('Part 5: Hoàn thành câu');
        $response->assertSee('1 câu sai');
        $response->assertSee('1/2 câu đúng');
        $response->assertSee('50%'); // accuracy
        $response->assertSee('• Lần đầu'); // first time comparison
        $response->assertSee('Ôn ngay'); // 1 wrong question -> shows review button
        $response->assertSee('10m'); // 10 minutes study time
    }

    /**
     * Test 3 & 4: Nhiều phiên cùng Part và khác Part hiển thị riêng biệt.
     */
    public function test_multiple_attempts_same_and_different_parts_displayed_distinctly(): void
    {
        $q1 = $this->createQuestion($this->part5Section, 'Part 5 Q1', 'A');
        $q2 = $this->createQuestion($this->part5Section, 'Part 5 Q2', 'B');
        $q3 = $this->createQuestion($this->part6Section, 'Part 6 Q1', 'C');

        // Attempt 1: Part 5 (earlier, accuracy 0%)
        $this->createPracticeAttempt($this->user1, $this->part5Section, [
            ['q' => $q1, 'answer' => 'B'], // wrong
        ], now()->subDays(2), now()->subDays(2)->addMinutes(10));

        // Attempt 2: Part 5 (later, accuracy 100%) -> should show comparison with Attempt 1 (+100%)
        $this->createPracticeAttempt($this->user1, $this->part5Section, [
            ['q' => $q2, 'answer' => 'B'], // correct
        ], now()->subDay(), now()->subDay()->addMinutes(10));

        // Attempt 3: Part 6 -> first time for Part 6
        $this->createPracticeAttempt($this->user1, $this->part6Section, [
            ['q' => $q3, 'answer' => 'C'], // correct
        ], now()->subHours(2), now()->subHours(2)->addMinutes(15));

        $response = $this->actingAs($this->user1)->get('/lich-su');

        $response->assertStatus(200);
        $response->assertSee('Part 5: Hoàn thành câu');
        $response->assertSee('Part 6: Hoàn thành đoạn văn');
        $response->assertSee('+100% so với lần trước');
        $response->assertSee('• Lần đầu');
    }

    /**
     * Test 7, 8, 9, 10: Thống kê chính xác: Hoạt động, Tỉ lệ đúng TB, Thời gian, Câu sai tích lũy không trùng.
     */
    public function test_kpis_calculation_and_unanswered_vs_incorrect_handling(): void
    {
        $q1 = $this->createQuestion($this->part5Section, 'Question 1', 'A');
        $q2 = $this->createQuestion($this->part5Section, 'Question 2', 'B');
        $q3 = $this->createQuestion($this->part5Section, 'Question 3', 'C');

        // Attempt 1: 3 questions: 1 correct (q1), 1 incorrect (q2), 1 unanswered (q3)
        // Time: 30 minutes
        $this->createPracticeAttempt($this->user1, $this->part5Section, [
            ['q' => $q1, 'answer' => 'A'], // correct
            ['q' => $q2, 'answer' => 'D'], // wrong
            ['q' => $q3, 'answer' => null], // unanswered (not wrong!)
        ], now()->subDays(5), now()->subDays(5)->addMinutes(30));

        // Attempt 2: repeating q2 (still wrong), and q1 (correct)
        // Time: 15 minutes
        $this->createPracticeAttempt($this->user1, $this->part5Section, [
            ['q' => $q1, 'answer' => 'A'], // correct
            ['q' => $q2, 'answer' => 'C'], // wrong again
        ], now()->subDays(2), now()->subDays(2)->addMinutes(15));

        $response = $this->actingAs($this->user1)->get('/lich-su?range=30days');

        $response->assertStatus(200);
        // Total attempts: 2
        $response->assertSee('2');
        $response->assertSee('lượt làm bài');

        // Total MCQ: 3 (attempt 1) + 2 (attempt 2) = 5 questions
        // Total correct: 1 (attempt 1) + 1 (attempt 2) = 2 correct
        // Average accuracy: 2/5 = 40%
        $response->assertSee('40%');

        // Total time: 30m + 15m = 45m
        $response->assertSee('45m');

        // Accumulated wrong questions: q2 was failed twice, but must be counted only ONCE. q3 was unanswered, not wrong.
        // So accumulated wrong count is exactly 1!
        $response->assertSee('1');
        $response->assertSee('câu');
    }

    /**
     * Test 11 & 12: Nút Ôn ngay ẩn khi câu sai = 0 và hiện khi > 0.
     */
    public function test_review_button_rendered_only_when_wrong_questions_exist(): void
    {
        $q1 = $this->createQuestion($this->part5Section, 'Question 1', 'A');

        // Attempt with 100% correct -> 0 wrong questions
        $this->createPracticeAttempt($this->user1, $this->part5Section, [
            ['q' => $q1, 'answer' => 'A'],
        ]);

        $response = $this->actingAs($this->user1)->get('/lich-su');
        $response->assertStatus(200);
        $response->assertDontSee('Ôn ngay');

        // Another user with 1 wrong question
        $this->createPracticeAttempt($this->user2, $this->part5Section, [
            ['q' => $q1, 'answer' => 'B'], // wrong
        ]);

        $responseUser2 = $this->actingAs($this->user2)->get('/lich-su');
        $responseUser2->assertStatus(200);
        $responseUser2->assertSee('Ôn ngay');
    }

    /**
     * Test 14: Tìm kiếm theo tên bài tập, tên Part, mã Part.
     */
    public function test_search_functionality(): void
    {
        $q1 = $this->createQuestion($this->part5Section, 'Part 5 Question', 'A');
        $q2 = $this->createQuestion($this->part6Section, 'Part 6 Question', 'B');

        $this->createPracticeAttempt($this->user1, $this->part5Section, [['q' => $q1, 'answer' => 'A']]);
        $this->createPracticeAttempt($this->user1, $this->part6Section, [['q' => $q2, 'answer' => 'B']]);

        // Search "Part 5"
        $respPart5 = $this->actingAs($this->user1)->get('/lich-su?q=Part+5');
        $respPart5->assertStatus(200);
        $respPart5->assertSee('Part 5: Hoàn thành câu');
        $respPart5->assertDontSee('data-title="Part 6: Hoàn thành đoạn văn"', false);

        // Search "PART6"
        $respPart6 = $this->actingAs($this->user1)->get('/lich-su?q=PART6');
        $respPart6->assertStatus(200);
        $respPart6->assertSee('Part 6: Hoàn thành đoạn văn');
        $respPart6->assertDontSee('data-title="Part 5: Hoàn thành câu"', false);

        // Search non-existent
        $respNone = $this->actingAs($this->user1)->get('/lich-su?q=nonexistentkeyword');
        $respNone->assertStatus(200);
        $respNone->assertSee('Không tìm thấy bài làm phù hợp');
    }

    /**
     * Test 15: Lọc theo thời gian (today, 7days, 30days, all).
     */
    public function test_filter_by_time_range(): void
    {
        $q = $this->createQuestion($this->part5Section, 'Question', 'A');

        // Today
        $this->createPracticeAttempt($this->user1, $this->part5Section, [['q' => $q, 'answer' => 'A']], now()->subHours(1));
        // 10 days ago
        $this->createPracticeAttempt($this->user1, $this->part5Section, [['q' => $q, 'answer' => 'A']], now()->subDays(10));

        // Filter 7 days -> only 1 attempt
        $resp7days = $this->actingAs($this->user1)->get('/lich-su?range=7days');
        $resp7days->assertStatus(200);
        $resp7days->assertSee('Hiển thị 1 trong 1 hoạt động');

        // Filter 30 days -> 2 attempts
        $resp30days = $this->actingAs($this->user1)->get('/lich-su?range=30days');
        $resp30days->assertStatus(200);
        $resp30days->assertSee('Hiển thị 2 trong 2 hoạt động');
    }

    /**
     * Test 16 & 17: Tab Tất cả, Bài học, Luyện đề & Lọc theo Part.
     */
    public function test_activity_type_and_part_filter(): void
    {
        $q5 = $this->createQuestion($this->part5Section, 'P5', 'A');
        $q6 = $this->createQuestion($this->part6Section, 'P6', 'B');

        $this->createPracticeAttempt($this->user1, $this->part5Section, [['q' => $q5, 'answer' => 'A']]);
        $this->createPracticeAttempt($this->user1, $this->part6Section, [['q' => $q6, 'answer' => 'B']]);

        // Filter part = PART5
        $respPart = $this->actingAs($this->user1)->get('/lich-su?part=PART5');
        $respPart->assertStatus(200);
        $respPart->assertSee('Part 5: Hoàn thành câu');
        $respPart->assertDontSee('data-title="Part 6: Hoàn thành đoạn văn"', false);
        $respPart->assertSee('Hiển thị 1 trong 1 hoạt động');

        // Filter type = lesson -> both are lessons
        $respLesson = $this->actingAs($this->user1)->get('/lich-su?type=lesson');
        $respLesson->assertStatus(200);
        $respLesson->assertSee('Hiển thị 2 trong 2 hoạt động');

        // Filter type = exam -> 0 exams
        $respExam = $this->actingAs($this->user1)->get('/lich-su?type=exam');
        $respExam->assertStatus(200);
        $respExam->assertSee('Hiển thị 0 trong 0 hoạt động');
    }

    /**
     * Test 18: Sắp xếp hoạt động (newest, oldest, score_desc, score_asc).
     */
    public function test_sorting_history(): void
    {
        $q = $this->createQuestion($this->part5Section, 'Q', 'A');

        $attemptOld = $this->createPracticeAttempt($this->user1, $this->part5Section, [['q' => $q, 'answer' => 'A']], now()->subDays(5));
        $attemptNew = $this->createPracticeAttempt($this->user1, $this->part5Section, [['q' => $q, 'answer' => 'B']], now()->subDays(1));

        $respNewest = $this->actingAs($this->user1)->get('/lich-su?sort=newest');
        $respNewest->assertStatus(200);
        $content = $respNewest->getContent();
        $posNew = strpos($content, "data-attempt-id=\"{$attemptNew->attempt_id}\"");
        $posOld = strpos($content, "data-attempt-id=\"{$attemptOld->attempt_id}\"");
        $this->assertTrue($posNew < $posOld, 'Newest sort must place newer attempt first');

        $respOldest = $this->actingAs($this->user1)->get('/lich-su?sort=oldest');
        $respOldest->assertStatus(200);
        $contentOldest = $respOldest->getContent();
        $posOld2 = strpos($contentOldest, "data-attempt-id=\"{$attemptOld->attempt_id}\"");
        $posNew2 = strpos($contentOldest, "data-attempt-id=\"{$attemptNew->attempt_id}\"");
        $this->assertTrue($posOld2 < $posNew2, 'Oldest sort must place older attempt first');
    }

    /**
     * Test 19: Phân trang đúng 10 phiên mỗi trang.
     */
    public function test_pagination_10_items_per_page(): void
    {
        $q = $this->createQuestion($this->part5Section, 'Q', 'A');

        // Create 12 attempts
        for ($i = 1; $i <= 12; $i++) {
            $this->createPracticeAttempt(
                $this->user1,
                $this->part5Section,
                [['q' => $q, 'answer' => 'A']],
                now()->subDays(20)->addHours($i)
            );
        }

        $respPage1 = $this->actingAs($this->user1)->get('/lich-su?page=1');
        $respPage1->assertStatus(200);
        $respPage1->assertSee('Hiển thị 10 trong 12 hoạt động');
        $respPage1->assertSee('Sau &rsaquo;', false);

        $respPage2 = $this->actingAs($this->user1)->get('/lich-su?page=2');
        $respPage2->assertStatus(200);
        $respPage2->assertSee('Hiển thị 2 trong 12 hoạt động');
        $respPage2->assertSee('&lsaquo; Trước', false);
    }

    /**
     * Test 21 & 22 & 23: Xem kết quả bài làm cũ bằng giao diện kết quả hiện có.
     */
    public function test_view_result_of_past_attempt(): void
    {
        $q1 = $this->createQuestion($this->part5Section, 'Reading Question Detail', 'B');

        $attempt = $this->createPracticeAttempt($this->user1, $this->part5Section, [
            ['q' => $q1, 'answer' => 'B'],
        ]);

        // Direct show route redirects to reading result view
        $showResp = $this->actingAs($this->user1)->get("/lich-su/{$attempt->attempt_id}");
        $showResp->assertRedirect(route('luyen-tap.doc.ket-qua', ['attempt_id' => $attempt->attempt_id, 'from' => 'history']));

        // Result view renders existing doc-ket-qua with full question detail
        $resultResp = $this->actingAs($this->user1)->get(route('luyen-tap.doc.ket-qua', ['attempt_id' => $attempt->attempt_id]));
        $resultResp->assertStatus(200);
        $resultResp->assertSee('Part 5 — Hoàn thành câu');
        $resultResp->assertSee('Reading Question Detail');
        $resultResp->assertSee('Explanation for Reading Question Detail');
        $resultResp->assertSee('100%');
    }

    /**
     * Test 24 & 25: Ôn lại làm lại đúng toàn bộ câu hỏi và không ghi đè bài cũ.
     */
    public function test_retry_attempt_flow(): void
    {
        $q1 = $this->createQuestion($this->part5Section, 'Retry Q1', 'A');
        $q2 = $this->createQuestion($this->part5Section, 'Retry Q2', 'B');

        $oldAttempt = $this->createPracticeAttempt($this->user1, $this->part5Section, [
            ['q' => $q1, 'answer' => 'A'],
            ['q' => $q2, 'answer' => 'C'], // wrong
        ]);

        // Bấm Ôn lại
        $retryResp = $this->actingAs($this->user1)->get("/lich-su/{$oldAttempt->attempt_id}/on-lai");
        $retryResp->assertRedirect(route('luyen-tap.doc.hoan-thanh-cau', [
            'part' => 5,
            'retry_attempt_id' => $oldAttempt->attempt_id,
        ]));

        // Following redirect sets up fresh session with both questions
        $practiceResp = $this->actingAs($this->user1)->get(route('luyen-tap.doc.hoan-thanh-cau', [
            'part' => 5,
            'retry_attempt_id' => $oldAttempt->attempt_id,
        ]));

        $practiceResp->assertStatus(200);
        $practiceResp->assertSee('Retry Q1');
        $practiceResp->assertSee('Retry Q2');

        // Session has attempt_id = null so it will save as brand new attempt
        $session = session('reading_practice_session');
        $this->assertNotEmpty($session);
        $this->assertEquals([$q1->question_id, $q2->question_id], $session['question_ids']);
        $this->assertNull($session['attempt_id'] ?? null);

        // Old attempt is NOT modified
        $this->assertDatabaseHas('attempts', [
            'attempt_id' => $oldAttempt->attempt_id,
            'status' => TestAttempt::STATUS_COMPLETED,
        ]);
    }

    /**
     * Test 27: Chặn xem hoặc ôn lại bài làm của tài khoản khác (403/404).
     */
    public function test_forbidden_access_to_other_users_attempt(): void
    {
        $q = $this->createQuestion($this->part5Section, 'Private Question', 'A');

        $user2Attempt = $this->createPracticeAttempt($this->user2, $this->part5Section, [
            ['q' => $q, 'answer' => 'A'],
        ]);

        // User 1 tries to show User 2's attempt
        $respShow = $this->actingAs($this->user1)->get("/lich-su/{$user2Attempt->attempt_id}");
        $respShow->assertStatus(403);

        // User 1 tries to retry User 2's attempt
        $respRetry = $this->actingAs($this->user1)->get("/lich-su/{$user2Attempt->attempt_id}/on-lai");
        $respRetry->assertStatus(403);

        // User 1 tries to view result of User 2's attempt directly
        $respDirect = $this->actingAs($this->user1)->get(route('luyen-tap.doc.ket-qua', ['attempt_id' => $user2Attempt->attempt_id]));
        $respDirect->assertRedirect(route('luyen-tap.doc'));
        $respDirect->assertSessionHas('practice_error');
    }

    /**
     * Test 28: Không có lịch sử flashcard.
     */
    public function test_no_flashcard_history_in_dropdown_or_cards(): void
    {
        $response = $this->actingAs($this->user1)->get('/lich-su');
        $response->assertStatus(200);
        $response->assertDontSee('Flashcard Từ vựng');
        $response->assertDontSee('Luyện thẻ nhớ');
    }
}
