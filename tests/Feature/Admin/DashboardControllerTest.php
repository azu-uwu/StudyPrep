<?php

namespace Tests\Feature\Admin;

use App\Models\ExamSection;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\ScoreResult;
use App\Models\Skill;
use App\Models\TestAttempt;
use App\Models\TestForm;
use App\Models\User;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use CreatesStudyPrepSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createStudyPrepSchema();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_is_forbidden_from_admin_dashboard(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_admin_sees_empty_dashboard_states(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewIs('admin.dashboard');
        $response->assertSee('Tổng người dùng');
        $response->assertSee('Chưa có lượt thi thử nào trong hệ thống.');
        $response->assertSee('Không có phản ánh câu hỏi cần duyệt.');
        $response->assertSee('Chưa có lượt thi thử trong tuần này.');
    }

    public function test_admin_dashboard_shows_live_statistics_and_recent_rows(): void
    {
        $admin = User::factory()->admin()->create();
        $learner = User::factory()->create(['full_name' => 'Trần Học Viên']);
        User::factory()->locked()->create();

        DB::table('test_blueprint')->insert([
            'name' => 'TOEIC Full',
            'exam_type' => 'TOEIC',
            'status' => 'ACTIVE',
        ]);

        $form = TestForm::query()->create([
            'blueprint_id' => 1,
            'title' => 'Đề TOEIC Test 01',
        ]);

        $attempt = TestAttempt::query()->create([
            'user_id' => $learner->user_id,
            'test_form_id' => $form->test_form_id,
            'session_type' => 'FULL',
            'started_at' => now(),
            'submitted_at' => now(),
            'status' => 'SUBMITTED',
        ]);

        ScoreResult::query()->create([
            'attempt_id' => $attempt->attempt_id,
            'listening_score' => 400,
            'reading_score' => 385,
            'total_score' => 785,
            'graded_at' => now(),
        ]);

        Skill::query()->create([
            'skill_id' => 1,
            'code' => 'R',
            'skill_name' => 'Reading',
        ]);

        $section = ExamSection::query()->create([
            'skill_id' => 1,
            'code' => 'PART5',
            'name' => 'Incomplete Sentences',
            'sequence_no' => 5,
        ]);

        $type = QuestionType::query()->create([
            'name' => 'Incomplete Sentences',
            'response_mode' => 'MCQ',
        ]);

        Question::query()->create([
            'section_id' => $section->section_id,
            'question_type_id' => $type->question_type_id,
            'content' => 'Choose the best answer.',
            'difficulty' => 'MEDIUM',
            'status' => 'PENDING',
            'created_at' => now(),
        ]);
        $pendingQuestion = Question::query()->where('status', 'PENDING')->firstOrFail();
        Feedback::query()->create([
            'user_id' => $learner->user_id,
            'question_id' => $pendingQuestion->question_id,
            'content' => 'Phương án đúng cần được kiểm tra lại.',
            'status' => 'PENDING',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Question::query()->create([
            'section_id' => $section->section_id,
            'question_type_id' => $type->question_type_id,
            'content' => 'Active question',
            'difficulty' => 'EASY',
            'status' => 'ACTIVE',
            'created_at' => now(),
        ]);

        Vocabulary::query()->create([
            'topic_id' => 1,
            'word' => 'negotiate',
            'meaning_vi' => 'đàm phán',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('userStats', function (array $stats): bool {
            return $stats['total'] === 3 && $stats['active'] === 2 && $stats['locked'] === 1;
        });
        $response->assertViewHas('questionStats', function (array $stats): bool {
            return $stats['total'] === 2 && $stats['pending'] === 1 && $stats['active'] === 1;
        });
        $response->assertViewHas('attemptStats', function (array $stats): bool {
            return $stats['today'] === 1 && $stats['week'] === 1;
        });
        $response->assertViewHas('vocabularyTotal', 1);
        $response->assertSee('Trần Học Viên');
        $response->assertSee('Đề TOEIC Test 01');
        $response->assertSee('Incomplete Sentences');
        $response->assertSee('Reading');
        $response->assertSee('Phương án đúng cần được kiểm tra lại.');
    }

    public function test_admin_dashboard_renders_shared_sidebar_and_dynamic_empty_states(): void
    {
        $admin = User::factory()->admin()->create(['full_name' => 'Nguyễn Admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        // Check sidebar branding & badge
        $response->assertSee('StudyPrep');
        $response->assertSee('ADMIN');
        $response->assertSee('PORTAL');

        // Check sidebar navigation groups & items
        $response->assertSee('TỔNG QUAN');
        $response->assertSee('Dashboard');
        $response->assertSee('QUẢN LÝ ĐÀO TẠO');
        $response->assertSee('Đề thi');
        $response->assertSee('Ngân hàng câu hỏi');
        $response->assertSee('Từ vựng & Flashcards', false);
        $response->assertSee('QUẢN TRỊ HỆ THỐNG');
        $response->assertSee('Quản lý người dùng');

        // Check profile card
        $response->assertSee('Nguyễn Admin');
        $response->assertSee('Super Admin');

        // Check KPI cards
        $response->assertSee('NGƯỜI DÙNG HOẠT ĐỘNG');
        $response->assertSee('LƯỢT LÀM BÀI HÔM NAY');

        // Check Chart Section
        $response->assertSee('Xu hướng luyện thi & Lượt nộp bài', false);
        $response->assertSee('Listening & Reading', false);
        $response->assertSee('Writing & Speaking', false);
        $response->assertSee('Cập nhật '.now()->format('H:i'));

        // Check Review Queue Section
        $response->assertSee('Hàng đợi kiểm duyệt & Chấm bài', false);
        $response->assertSee('Không có phản ánh câu hỏi cần duyệt.');
        $response->assertDontSee('Q-4402');
    }

    public function test_dashboard_kpi_matches_exact_db_counts_across_four_skills_reported_tests_and_today_attempts(): void
    {
        $admin = User::factory()->admin()->create(['status' => 'ACTIVE']);
        $student1 = User::factory()->create(['status' => 'ACTIVE']);
        User::factory()->create(['status' => 'ACTIVE']);
        User::factory()->locked()->create();

        DB::table('test_blueprint')->insert([
            ['blueprint_id' => 1, 'name' => 'TOEIC LR', 'exam_type' => 'TOEIC_LR', 'status' => 'ACTIVE'],
            ['blueprint_id' => 2, 'name' => 'TOEIC SW', 'exam_type' => 'TOEIC_SW', 'status' => 'ACTIVE'],
        ]);

        $form1 = TestForm::query()->create(['blueprint_id' => 1, 'title' => 'ETS Live Form 1']);
        $form2 = TestForm::query()->create(['blueprint_id' => 2, 'title' => 'ETS Live Form 2']);

        // 2 lượt nộp hôm nay, 1 lượt chưa nộp (không tính vào submitted hôm nay)
        TestAttempt::query()->create([
            'user_id' => $student1->user_id,
            'test_form_id' => $form1->test_form_id,
            'session_type' => 'FULL',
            'started_at' => now()->subHour(),
            'submitted_at' => now(),
            'status' => 'SUBMITTED',
        ]);
        TestAttempt::query()->create([
            'user_id' => $student1->user_id,
            'test_form_id' => $form2->test_form_id,
            'session_type' => 'FULL',
            'started_at' => now()->subMinutes(40),
            'submitted_at' => now(),
            'status' => 'SUBMITTED',
        ]);
        TestAttempt::query()->create([
            'user_id' => $student1->user_id,
            'test_form_id' => $form1->test_form_id,
            'session_type' => 'FULL',
            'started_at' => now(),
            'submitted_at' => null,
            'status' => 'IN_PROGRESS',
        ]);

        Skill::query()->insert([
            ['skill_id' => 1, 'code' => 'LC', 'skill_name' => 'Listening'],
            ['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading'],
            ['skill_id' => 3, 'code' => 'SPK', 'skill_name' => 'Speaking'],
            ['skill_id' => 4, 'code' => 'WRT', 'skill_name' => 'Writing'],
        ]);

        $secL = ExamSection::query()->create(['skill_id' => 1, 'code' => 'PART1', 'name' => 'Photographs', 'sequence_no' => 1]);
        $secR = ExamSection::query()->create(['skill_id' => 2, 'code' => 'PART5', 'name' => 'Incomplete Sentences', 'sequence_no' => 5]);
        $secS = ExamSection::query()->create(['skill_id' => 3, 'code' => 'SPK_T2', 'name' => 'Describe a Picture', 'sequence_no' => 9]);
        $secW = ExamSection::query()->create(['skill_id' => 4, 'code' => 'WRITING_T3', 'name' => 'Opinion Essay', 'sequence_no' => 15]);
        $qType = QuestionType::query()->create(['name' => 'Standard', 'response_mode' => 'SINGLE_CHOICE']);

        // L: 2 câu, R: 3 câu (trong đó 2 REPORTED), S: 1 câu, W: 2 câu => Tổng 8 câu
        foreach ([1, 2] as $i) {
            Question::query()->create(['section_id' => $secL->section_id, 'question_type_id' => $qType->question_type_id, 'content' => "LC Q{$i}", 'difficulty' => 'EASY', 'status' => 'ACTIVE', 'created_at' => now()]);
        }
        Question::query()->create(['section_id' => $secR->section_id, 'question_type_id' => $qType->question_type_id, 'content' => 'RC Active', 'difficulty' => 'MEDIUM', 'status' => 'ACTIVE', 'created_at' => now()]);
        Question::query()->create(['section_id' => $secR->section_id, 'question_type_id' => $qType->question_type_id, 'content' => 'RC Reported 1', 'difficulty' => 'HARD', 'status' => 'REPORTED', 'created_at' => now()]);
        Question::query()->create(['section_id' => $secR->section_id, 'question_type_id' => $qType->question_type_id, 'content' => 'RC Reported 2', 'difficulty' => 'HARD', 'status' => 'REPORTED', 'created_at' => now()]);
        Question::query()->create(['section_id' => $secS->section_id, 'question_type_id' => $qType->question_type_id, 'content' => 'SPK Active', 'difficulty' => 'MEDIUM', 'status' => 'ACTIVE', 'created_at' => now()]);
        foreach ([1, 2] as $i) {
            Question::query()->create(['section_id' => $secW->section_id, 'question_type_id' => $qType->question_type_id, 'content' => "WRT Q{$i}", 'difficulty' => 'MEDIUM', 'status' => 'ACTIVE', 'created_at' => now()]);
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('userStats', fn (array $s): bool => $s['active'] === 3 && $s['locked'] === 1 && $s['total'] === 4);
        $response->assertViewHas('testStats', fn (array $s): bool => $s['total'] === 2);
        $response->assertViewHas('attemptStats', fn (array $s): bool => $s['today'] === 2);
        $response->assertViewHas('questionStats', fn (array $s): bool => $s['total'] === 8
            && $s['reported'] === 2
            && $s['listening'] === 2
            && $s['reading'] === 3
            && $s['speaking'] === 1
            && $s['writing'] === 2);
    }
}
