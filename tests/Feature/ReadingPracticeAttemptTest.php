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
use App\Services\PracticeAttemptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class ReadingPracticeAttemptTest extends TestCase
{
    use CreatesStudyPrepSchema;
    use RefreshDatabase;

    private User $user1;

    private User $user2;

    private Skill $rcSkill;

    private Skill $lcSkill;

    private ExamSection $part5Section;

    private ExamSection $part6Section;

    private ExamSection $part7Section;

    private ExamSection $part1Section;

    private QuestionType $mcType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createStudyPrepSchema();

        $this->user1 = User::query()->create([
            'user_name' => 'student1',
            'email' => 'student1@example.com',
            'password_hash' => bcrypt('password'),
            'full_name' => 'Student One',
            'energy' => 5,
        ]);

        $this->user2 = User::query()->create([
            'user_name' => 'student2',
            'email' => 'student2@example.com',
            'password_hash' => bcrypt('password'),
            'full_name' => 'Student Two',
            'energy' => 5,
        ]);

        $this->rcSkill = Skill::query()->create([
            'skill_id' => 2,
            'code' => 'RC',
            'skill_name' => 'Reading',
        ]);

        $this->lcSkill = Skill::query()->create([
            'skill_id' => 1,
            'code' => 'LC',
            'skill_name' => 'Listening',
        ]);

        $this->part5Section = ExamSection::query()->create([
            'skill_id' => $this->rcSkill->skill_id,
            'code' => 'PART5',
            'name' => 'Incomplete Sentences',
            'sequence_no' => 5,
        ]);

        $this->part6Section = ExamSection::query()->create([
            'skill_id' => $this->rcSkill->skill_id,
            'code' => 'PART6',
            'name' => 'Text Completion',
            'sequence_no' => 6,
        ]);

        $this->part7Section = ExamSection::query()->create([
            'skill_id' => $this->rcSkill->skill_id,
            'code' => 'PART7',
            'name' => 'Reading Comprehension',
            'sequence_no' => 7,
        ]);

        $this->part1Section = ExamSection::query()->create([
            'skill_id' => $this->lcSkill->skill_id,
            'code' => 'PART1',
            'name' => 'Photographs',
            'sequence_no' => 1,
        ]);

        $this->mcType = QuestionType::query()->create([
            'name' => 'Multiple Choice',
            'response_mode' => 'SINGLE_CHOICE',
        ]);
    }

    private function createPart5Question(string $content, string $correctLabel = 'B'): Question
    {
        $q = Question::query()->create([
            'section_id' => $this->part5Section->section_id,
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
                'is_correct' => $label === $correctLabel,
            ]);
        }

        return $q;
    }

    private function createQuestionSet(string $sectionCode, int $questionCount, string $title = 'Sample Set'): QuestionSet
    {
        $section = match ($sectionCode) {
            'PART6' => $this->part6Section,
            'PART7' => $this->part7Section,
            default => $this->part5Section,
        };

        $set = QuestionSet::query()->create([
            'title' => $title,
            'stimulus_text' => "<p>Passage for {$title}</p>",
        ]);

        for ($i = 1; $i <= $questionCount; $i++) {
            $q = Question::query()->create([
                'section_id' => $section->section_id,
                'question_type_id' => $this->mcType->question_type_id,
                'question_set_id' => $set->question_set_id,
                'content' => "Question {$i} of {$title} [{$i}]",
                'explanation' => "Explanation for Question {$i}",
                'difficulty' => 'MEDIUM',
                'status' => Question::STATUS_ACTIVE,
            ]);

            foreach (['A', 'B', 'C', 'D'] as $label) {
                AnswerOption::query()->create([
                    'question_id' => $q->question_id,
                    'label' => $label,
                    'content' => "Option {$label}",
                    'is_correct' => $label === 'A',
                ]);
            }
        }

        return $set;
    }

    // =========================================================================
    // NHÓM A: KIỂM TRA KHÔNG TỰ ĐỘNG GHI DB TRONG QUÁ TRÌNH LUYỆN TẬP
    // =========================================================================

    public function test_group_a_starting_practice_does_not_create_db_attempt(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->createPart5Question("Question {$i}");
        }

        $this->actingAs($this->user1)
            ->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 5]))
            ->assertOk();

        $this->assertDatabaseCount('attempts', 0);
        $this->assertDatabaseCount('attempt_response', 0);
        $this->assertDatabaseCount('score_result', 0);
    }

    public function test_group_a_selecting_and_changing_answers_does_not_create_db_records(): void
    {
        $q = $this->createPart5Question('Question 1');

        $sessionData = [
            'user_id' => $this->user1->user_id,
            'part' => 5,
            'target_count' => 1,
            'actual_count' => 1,
            'question_ids' => [$q->question_id],
            'answers' => [],
            'question_states' => [],
            'groups' => [],
        ];

        // 1. Chọn đáp án A
        $this->actingAs($this->user1)
            ->withSession(['reading_practice_session' => $sessionData])
            ->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
                'question_id' => $q->question_id,
                'answer' => 'A',
            ])
            ->assertOk();

        $this->assertDatabaseCount('attempts', 0);
        $this->assertDatabaseCount('attempt_response', 0);

        // 2. Đổi đáp án thành B
        $this->actingAs($this->user1)
            ->postJson(route('luyen-tap.doc.kiem-tra-cau-hoi'), [
                'question_id' => $q->question_id,
                'answer' => 'B',
            ])
            ->assertOk();

        $this->assertDatabaseCount('attempts', 0);
        $this->assertDatabaseCount('attempt_response', 0);
        $this->assertDatabaseCount('score_result', 0);

        // Năng lượng không bị trừ
        $this->assertSame(5, $this->user1->fresh()->energy);
    }

    public function test_group_a_reloading_page_preserves_session_without_creating_db_attempt(): void
    {
        $q = $this->createPart5Question('Question 1');

        $sessionData = [
            'user_id' => $this->user1->user_id,
            'part' => 5,
            'target_count' => 1,
            'actual_count' => 1,
            'question_ids' => [$q->question_id],
            'answers' => [$q->question_id => 'A'],
            'question_states' => [],
            'groups' => [],
        ];

        // F5 tải lại trang
        $this->actingAs($this->user1)
            ->withSession(['reading_practice_session' => $sessionData])
            ->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5]))
            ->assertOk();

        $this->assertDatabaseCount('attempts', 0);
        $this->assertDatabaseCount('attempt_response', 0);
    }

    // =========================================================================
    // NHÓM B: KIỂM TRA LƯU & THOÁT
    // =========================================================================

    public function test_group_b_save_and_exit_part5_creates_attempt_and_responses(): void
    {
        $questions = [];
        for ($i = 1; $i <= 5; $i++) {
            $questions[] = $this->createPart5Question("Question {$i}");
        }
        $ids = collect($questions)->pluck('question_id')->all();

        $sessionData = [
            'user_id' => $this->user1->user_id,
            'part' => 5,
            'target_count' => 5,
            'actual_count' => 5,
            'started_at' => now()->subMinutes(3)->toIso8601String(),
            'question_ids' => $ids,
            'answers' => [
                $ids[0] => 'B',
                $ids[1] => 'C',
            ],
            'question_states' => [],
            'groups' => [],
        ];

        $resp = $this->actingAs($this->user1)
            ->withSession(['reading_practice_session' => $sessionData])
            ->postJson(route('luyen-tap.doc.luu-va-thoat'), [
                'part' => 5,
                'answers' => [
                    $ids[0] => 'B',
                    $ids[1] => 'C',
                ],
                'time_spent' => 180,
            ]);

        $resp->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseCount('attempts', 1);
        $this->assertDatabaseCount('attempt_response', 5);
        $this->assertDatabaseCount('score_result', 0); // Chưa chấm điểm

        $attempt = TestAttempt::first();
        $this->assertSame((int) $this->user1->user_id, (int) $attempt->user_id);
        $this->assertSame('PRACTICE', $attempt->session_type);
        $this->assertSame('IN_PROGRESS', $attempt->status);
        $this->assertNull($attempt->test_form_id);
        $this->assertNull($attempt->ended_at);
        $this->assertNull($attempt->submitted_at);

        // Kiểm tra sequence_no và text_response
        $responses = AttemptResponse::where('attempt_id', $attempt->attempt_id)
            ->orderBy('sequence_no', 'asc')
            ->get();

        $this->assertCount(5, $responses);
        $this->assertSame('B', $responses[0]->text_response);
        $this->assertSame('C', $responses[1]->text_response);
        $this->assertNull($responses[2]->text_response);
        $this->assertNull($responses[3]->text_response);
        $this->assertNull($responses[4]->text_response);

        foreach ($responses as $idx => $r) {
            $this->assertSame($idx + 1, (int) $r->sequence_no);
            $this->assertNull($r->score);
            $this->assertNull($r->submitted_at);
        }
    }

    public function test_group_b_save_and_exit_part6_preserves_entire_groups(): void
    {
        $set1 = $this->createQuestionSet('PART6', 4, 'Part 6 Set 1');
        $set2 = $this->createQuestionSet('PART6', 4, 'Part 6 Set 2');

        $ids = array_merge(
            $set1->questions->pluck('question_id')->all(),
            $set2->questions->pluck('question_id')->all()
        );

        $sessionData = [
            'user_id' => $this->user1->user_id,
            'part' => 6,
            'target_count' => 8,
            'actual_count' => 8,
            'question_ids' => $ids,
            'answers' => [
                $ids[0] => 'A',
                $ids[1] => 'B',
            ],
            'question_states' => [],
            'groups' => [],
        ];

        $resp = $this->actingAs($this->user1)
            ->withSession(['reading_practice_session' => $sessionData])
            ->postJson(route('luyen-tap.doc.luu-va-thoat'), [
                'part' => 6,
                'answers' => [
                    $ids[0] => 'A',
                    $ids[1] => 'B',
                ],
            ]);

        $resp->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseCount('attempts', 1);
        $this->assertDatabaseCount('attempt_response', 8);

        $attempt = TestAttempt::first();
        $this->assertSame('IN_PROGRESS', $attempt->status);
    }

    // =========================================================================
    // NHÓM C: KIỂM TRA TIẾP TỤC (RESUME)
    // =========================================================================

    public function test_group_c_resume_restores_exact_questions_and_answers(): void
    {
        $questions = [];
        for ($i = 1; $i <= 5; $i++) {
            $questions[] = $this->createPart5Question("Question {$i}");
        }
        $ids = collect($questions)->pluck('question_id')->all();

        // Tạo 1 attempt đã lưu
        $attempt = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'session_type' => 'PRACTICE',
            'status' => 'IN_PROGRESS',
            'started_at' => now()->subMinutes(10),
        ]);

        foreach ($ids as $idx => $qid) {
            AttemptResponse::query()->create([
                'attempt_id' => $attempt->attempt_id,
                'question_id' => $qid,
                'sequence_no' => $idx + 1,
                'text_response' => $idx === 0 ? 'B' : ($idx === 1 ? 'C' : null),
            ]);
        }

        // Bấm Tiếp tục
        $resp = $this->actingAs($this->user1)
            ->get(route('luyen-tap.doc.tiep-tuc', ['part' => 5]));

        $resp->assertRedirect(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5]));

        $session = session('reading_practice_session');
        $this->assertNotNull($session);
        $this->assertSame($attempt->attempt_id, $session['attempt_id']);
        $this->assertSame($ids, $session['question_ids']);
        $this->assertSame('B', $session['answers'][$ids[0]]);
        $this->assertSame('C', $session['answers'][$ids[1]]);
        $this->assertArrayNotHasKey($ids[2], $session['answers']);
    }

    public function test_group_c_part_listing_shows_continue_button_when_in_progress_exists(): void
    {
        $q = $this->createPart5Question('Question 1');

        $attempt = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'session_type' => 'PRACTICE',
            'status' => 'IN_PROGRESS',
        ]);

        AttemptResponse::query()->create([
            'attempt_id' => $attempt->attempt_id,
            'question_id' => $q->question_id,
            'sequence_no' => 1,
            'text_response' => 'B',
        ]);

        $resp = $this->actingAs($this->user1)
            ->get(route('luyen-tap.doc'));

        $resp->assertOk();
        // Part 5 hiện nút Tiếp tục
        $resp->assertSee('Tiếp tục');
        $resp->assertSee('Đang làm dở');
        // Part 6 vẫn hiện Bắt đầu vì chưa có bài lưu
        $resp->assertSee('Bắt đầu');
    }

    // =========================================================================
    // NHÓM D: KIỂM TRA LƯU NHIỀU LẦN (UPDATE EXISTING ATTEMPT)
    // =========================================================================

    public function test_group_d_saving_second_time_updates_same_attempt_without_duplicates(): void
    {
        $questions = [];
        for ($i = 1; $i <= 3; $i++) {
            $questions[] = $this->createPart5Question("Question {$i}");
        }
        $ids = collect($questions)->pluck('question_id')->all();

        // Lần 1: Lưu & Thoát
        $sessionData = [
            'user_id' => $this->user1->user_id,
            'part' => 5,
            'target_count' => 3,
            'actual_count' => 3,
            'started_at' => now()->subMinutes(15)->toIso8601String(),
            'question_ids' => $ids,
            'answers' => [$ids[0] => 'A'],
            'question_states' => [],
            'groups' => [],
        ];

        $resp1 = $this->actingAs($this->user1)
            ->withSession(['reading_practice_session' => $sessionData])
            ->postJson(route('luyen-tap.doc.luu-va-thoat'), [
                'part' => 5,
                'answers' => [$ids[0] => 'A'],
            ]);

        $attemptId = $resp1->json('attempt_id');
        $this->assertDatabaseCount('attempts', 1);
        $this->assertDatabaseCount('attempt_response', 3);

        // Lần 2: Tiếp tục bài, sửa câu 1 thành B, làm thêm câu 2 chọn C
        $sessionData['attempt_id'] = $attemptId;
        $sessionData['answers'][$ids[0]] = 'B';
        $sessionData['answers'][$ids[1]] = 'C';

        $resp2 = $this->actingAs($this->user1)
            ->withSession(['reading_practice_session' => $sessionData])
            ->postJson(route('luyen-tap.doc.luu-va-thoat'), [
                'part' => 5,
                'answers' => [
                    $ids[0] => 'B',
                    $ids[1] => 'C',
                ],
            ]);

        $resp2->assertOk()->assertJson(['success' => true]);

        // Vẫn chỉ có 1 attempt và 3 attempt_response
        $this->assertDatabaseCount('attempts', 1);
        $this->assertDatabaseCount('attempt_response', 3);

        $attempt = TestAttempt::first();
        $this->assertSame($attemptId, $attempt->attempt_id);

        $r1 = AttemptResponse::where('attempt_id', $attemptId)->where('question_id', $ids[0])->first();
        $r2 = AttemptResponse::where('attempt_id', $attemptId)->where('question_id', $ids[1])->first();
        $r3 = AttemptResponse::where('attempt_id', $attemptId)->where('question_id', $ids[2])->first();

        $this->assertSame('B', $r1->text_response);
        $this->assertSame('C', $r2->text_response);
        $this->assertNull($r3->text_response);
    }

    // =========================================================================
    // NHÓM E: KIỂM TRA THOÁT KHÔNG LƯU
    // =========================================================================

    public function test_group_e_quit_without_saving_discards_unsaved_session_without_touching_db(): void
    {
        $q = $this->createPart5Question('Question 1');

        // Tạo 1 attempt đã lưu trước đó có đáp án A
        $attempt = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'session_type' => 'PRACTICE',
            'status' => 'IN_PROGRESS',
        ]);

        AttemptResponse::query()->create([
            'attempt_id' => $attempt->attempt_id,
            'question_id' => $q->question_id,
            'sequence_no' => 1,
            'text_response' => 'A',
        ]);

        // Người dùng đổi đáp án thành B trong session nhưng bấm "Thoát"
        $sessionData = [
            'attempt_id' => $attempt->attempt_id,
            'user_id' => $this->user1->user_id,
            'part' => 5,
            'target_count' => 1,
            'actual_count' => 1,
            'question_ids' => [$q->question_id],
            'answers' => [$q->question_id => 'B'],
        ];

        $this->actingAs($this->user1)
            ->withSession(['reading_practice_session' => $sessionData])
            ->get(route('luyen-tap.doc', ['exit' => 1]))
            ->assertOk();

        // Session đã bị xóa
        $this->assertNull(session('reading_practice_session'));

        // CSDL vẫn giữ nguyên đáp án A ở lần lưu gần nhất
        $respInDb = AttemptResponse::where('attempt_id', $attempt->attempt_id)->first();
        $this->assertSame('A', $respInDb->text_response);
    }

    // =========================================================================
    // NHÓM F: KIỂM TRA NỘP BÀI VÀ CHẤM ĐIỂM TỰ ĐỘNG
    // =========================================================================

    public function test_group_f_submit_direct_without_previous_save_creates_completed_attempt_and_scores(): void
    {
        $q1 = $this->createPart5Question('Q1', 'B'); // Đúng B
        $q2 = $this->createPart5Question('Q2', 'C'); // Đúng C
        $q3 = $this->createPart5Question('Q3', 'A'); // Đúng A
        $ids = [$q1->question_id, $q2->question_id, $q3->question_id];

        $sessionData = [
            'user_id' => $this->user1->user_id,
            'part' => 5,
            'target_count' => 3,
            'actual_count' => 3,
            'started_at' => now()->subMinutes(5)->toIso8601String(),
            'question_ids' => $ids,
            'answers' => [
                $ids[0] => 'B', // đúng
                $ids[1] => 'D', // sai
                // câu 3 chưa trả lời
            ],
        ];

        $resp = $this->actingAs($this->user1)
            ->withSession(['reading_practice_session' => $sessionData])
            ->post(route('luyen-tap.doc.nop-bai'), [
                'part' => 5,
                'question_ids' => implode(',', $ids),
                'total_count' => 3,
                'time_spent' => 300,
                'answers' => [
                    $ids[0] => 'B',
                    $ids[1] => 'D',
                ],
            ]);

        $attempt = TestAttempt::first();
        $this->assertNotNull($attempt);
        $this->assertSame('COMPLETED', $attempt->status);
        $this->assertNotNull($attempt->ended_at);
        $this->assertNotNull($attempt->submitted_at);

        $resp->assertRedirect(route('luyen-tap.doc.ket-qua', ['attempt_id' => $attempt->attempt_id]));

        // Kiểm tra attempt_response
        $this->assertDatabaseCount('attempt_response', 3);
        $r1 = AttemptResponse::where('attempt_id', $attempt->attempt_id)->where('question_id', $ids[0])->first();
        $r2 = AttemptResponse::where('attempt_id', $attempt->attempt_id)->where('question_id', $ids[1])->first();
        $r3 = AttemptResponse::where('attempt_id', $attempt->attempt_id)->where('question_id', $ids[2])->first();

        $this->assertSame('B', $r1->text_response);
        $this->assertEquals(1.00, $r1->score);

        $this->assertSame('D', $r2->text_response);
        $this->assertEquals(0.00, $r2->score);

        $this->assertNull($r3->text_response);
        $this->assertEquals(0.00, $r3->score);

        // Kiểm tra score_result
        $this->assertDatabaseCount('score_result', 1);
        $scoreRes = ScoreResult::where('attempt_id', $attempt->attempt_id)->first();
        $this->assertEquals(1.00, $scoreRes->reading_score);
        $this->assertEquals(1.00, $scoreRes->total_score);
        $this->assertNull($scoreRes->listening_score);

        // Vẫn lưu đầy đủ thời điểm vòng đời trong CSDL
        $this->assertNotNull($attempt->started_at);
        $this->assertNotNull($attempt->ended_at);
        $this->assertNotNull($attempt->submitted_at);
        $this->assertNotNull($scoreRes->graded_at);

        // Kết quả luyện tập không chứa các trường thời gian làm bài
        $result = session('reading_practice_result');
        $this->assertArrayNotHasKey('time_spent_seconds', $result);
        $this->assertArrayNotHasKey('time_spent_formatted', $result);
        $this->assertArrayNotHasKey('time_spent_text', $result);

        // Lượt này không còn xuất hiện dưới nút Tiếp tục
        $this->assertNull(app(PracticeAttemptService::class)->findInProgressAttempt($this->user1->user_id, 'RC', 'PART5'));
    }

    public function test_group_f_submit_previously_saved_attempt_updates_same_attempt(): void
    {
        $q1 = $this->createPart5Question('Q1', 'B');
        $q2 = $this->createPart5Question('Q2', 'C');
        $ids = [$q1->question_id, $q2->question_id];

        // Tạo 1 attempt đã lưu dở
        $attempt = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'session_type' => 'PRACTICE',
            'status' => 'IN_PROGRESS',
            'started_at' => now()->subMinutes(8),
        ]);

        foreach ($ids as $idx => $qid) {
            AttemptResponse::query()->create([
                'attempt_id' => $attempt->attempt_id,
                'question_id' => $qid,
                'sequence_no' => $idx + 1,
                'text_response' => 'A', // ban đầu chọn A
            ]);
        }

        // Nộp bài và đổi câu 1 thành B (đúng), câu 2 thành C (đúng)
        $sessionData = [
            'attempt_id' => $attempt->attempt_id,
            'user_id' => $this->user1->user_id,
            'part' => 5,
            'target_count' => 2,
            'actual_count' => 2,
            'question_ids' => $ids,
            'answers' => [
                $ids[0] => 'B',
                $ids[1] => 'C',
            ],
        ];

        $this->actingAs($this->user1)
            ->withSession(['reading_practice_session' => $sessionData])
            ->post(route('luyen-tap.doc.nop-bai'), [
                'part' => 5,
                'question_ids' => implode(',', $ids),
                'total_count' => 2,
                'time_spent' => 480,
                'answers' => [
                    $ids[0] => 'B',
                    $ids[1] => 'C',
                ],
            ])
            ->assertRedirect(route('luyen-tap.doc.ket-qua', ['attempt_id' => $attempt->attempt_id]));

        // Vẫn chỉ có 1 attempt
        $this->assertDatabaseCount('attempts', 1);
        $attempt->refresh();
        $this->assertSame('COMPLETED', $attempt->status);

        // Điểm 2/2
        $scoreRes = ScoreResult::where('attempt_id', $attempt->attempt_id)->first();
        $this->assertEquals(2.00, $scoreRes->reading_score);
    }

    // =========================================================================
    // NHÓM G: KIỂM TRA TRANG KẾT QUẢ VÀ XEM LẠI BÀI TỪ CSDL
    // =========================================================================

    public function test_group_g_result_page_reads_from_database_and_supports_f5(): void
    {
        $q = $this->createPart5Question('Q1', 'B');

        $attempt = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'session_type' => 'PRACTICE',
            'status' => 'COMPLETED',
            'started_at' => now()->subMinutes(4),
            'submitted_at' => now(),
            'ended_at' => now(),
        ]);

        AttemptResponse::query()->create([
            'attempt_id' => $attempt->attempt_id,
            'question_id' => $q->question_id,
            'sequence_no' => 1,
            'text_response' => 'B',
            'score' => 1.00,
            'submitted_at' => now(),
        ]);

        ScoreResult::query()->create([
            'attempt_id' => $attempt->attempt_id,
            'reading_score' => 1.00,
            'total_score' => 1.00,
            'graded_at' => now(),
        ]);

        // F5 nhiều lần trang kết quả không làm mất hoặc thay đổi kết quả
        for ($i = 0; $i < 3; $i++) {
            $resp = $this->actingAs($this->user1)
                ->get(route('luyen-tap.doc.ket-qua', ['attempt_id' => $attempt->attempt_id]));

            $resp->assertOk();
            $resp->assertSee('100%');
            $resp->assertSee('Đúng 1/1 câu');
            $resp->assertSee('1 câu');
            $resp->assertDontSee('phút');
        }

        // Không chứa trường thời gian làm bài khi đọc từ CSDL
        $dbResult = app(PracticeAttemptService::class)->getAttemptResult($attempt->attempt_id, $this->user1->user_id);
        $this->assertArrayNotHasKey('time_spent_seconds', $dbResult);
        $this->assertArrayNotHasKey('time_spent_formatted', $dbResult);
        $this->assertArrayNotHasKey('time_spent_text', $dbResult);

        // Không sinh thêm score_result hay attempt_response
        $this->assertDatabaseCount('attempts', 1);
        $this->assertDatabaseCount('score_result', 1);
    }

    public function test_group_g_retry_wrong_questions_loads_incorrect_ones_from_db(): void
    {
        $q1 = $this->createPart5Question('Q1', 'B'); // user chọn C -> sai
        $q2 = $this->createPart5Question('Q2', 'C'); // user chọn C -> đúng

        $attempt = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'session_type' => 'PRACTICE',
            'status' => 'COMPLETED',
            'started_at' => now()->subMinutes(2),
            'submitted_at' => now(),
            'ended_at' => now(),
        ]);

        AttemptResponse::query()->create([
            'attempt_id' => $attempt->attempt_id,
            'question_id' => $q1->question_id,
            'sequence_no' => 1,
            'text_response' => 'C',
            'score' => 0.00,
            'submitted_at' => now(),
        ]);

        AttemptResponse::query()->create([
            'attempt_id' => $attempt->attempt_id,
            'question_id' => $q2->question_id,
            'sequence_no' => 2,
            'text_response' => 'C',
            'score' => 1.00,
            'submitted_at' => now(),
        ]);

        ScoreResult::query()->create([
            'attempt_id' => $attempt->attempt_id,
            'reading_score' => 1.00,
            'total_score' => 1.00,
            'graded_at' => now(),
        ]);

        // Bấm Luyện lại câu sai với attempt_id
        $resp = $this->actingAs($this->user1)
            ->get(route('luyen-tap.doc.hoan-thanh-cau', [
                'part' => 5,
                'retry_wrong' => 1,
                'attempt_id' => $attempt->attempt_id,
            ]));

        $resp->assertOk();

        // Phiên mới chỉ gồm 1 câu sai (q1)
        $newSession = session('reading_practice_session');
        $this->assertNotNull($newSession);
        $this->assertSame([$q1->question_id], $newSession['question_ids']);
        $this->assertNull($newSession['attempt_id'] ?? null); // Chưa tạo attempt mới
        $this->assertDatabaseCount('attempts', 1); // CSDL vẫn chỉ có attempt cũ
    }

    // =========================================================================
    // NHÓM H: KIỂM TRA BẢO MẬT VÀ ĐỒNG THỜI
    // =========================================================================

    public function test_group_h_user_cannot_view_or_resume_other_users_attempt(): void
    {
        $q = $this->createPart5Question('Q1');

        $attempt = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'session_type' => 'PRACTICE',
            'status' => 'IN_PROGRESS',
        ]);

        AttemptResponse::query()->create([
            'attempt_id' => $attempt->attempt_id,
            'question_id' => $q->question_id,
            'sequence_no' => 1,
            'text_response' => 'A',
        ]);

        // User 2 cố gắng resume attempt của User 1
        $this->actingAs($this->user2)
            ->get(route('luyen-tap.doc.tiep-tuc', ['attempt_id' => $attempt->attempt_id]))
            ->assertRedirect(route('luyen-tap.doc'))
            ->assertSessionHas('practice_error');

        // User 2 cố gắng xem kết quả của User 1
        $attempt->update(['status' => 'COMPLETED']);
        ScoreResult::query()->create([
            'attempt_id' => $attempt->attempt_id,
            'reading_score' => 1.00,
            'total_score' => 1.00,
            'graded_at' => now(),
        ]);

        $this->actingAs($this->user2)
            ->get(route('luyen-tap.doc.ket-qua', ['attempt_id' => $attempt->attempt_id]))
            ->assertRedirect(route('luyen-tap.doc'))
            ->assertSessionHas('practice_error');
    }

    public function test_group_h_cannot_resume_completed_attempt(): void
    {
        $q = $this->createPart5Question('Q1');

        $attempt = TestAttempt::query()->create([
            'user_id' => $this->user1->user_id,
            'session_type' => 'PRACTICE',
            'status' => 'COMPLETED',
        ]);

        AttemptResponse::query()->create([
            'attempt_id' => $attempt->attempt_id,
            'question_id' => $q->question_id,
            'sequence_no' => 1,
            'text_response' => 'A',
        ]);

        $this->actingAs($this->user1)
            ->get(route('luyen-tap.doc.tiep-tuc', ['attempt_id' => $attempt->attempt_id]))
            ->assertRedirect(route('luyen-tap.doc'))
            ->assertSessionHas('practice_error');
    }
}
