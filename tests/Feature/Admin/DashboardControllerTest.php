<?php

namespace Tests\Feature\Admin;

use App\Models\ExamSection;
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
        $response->assertSee('Tổng học viên');
        $response->assertSee('Chưa có lượt thi thử nào trong hệ thống.');
        $response->assertSee('Không có câu hỏi nào đang chờ duyệt.');
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
        $response->assertViewHas('learnerStats', function (array $stats): bool {
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
        $response->assertSee('PART5');
        $response->assertSee('Reading');
    }

    public function test_admin_dashboard_renders_shared_sidebar_and_ui_mockup_elements(): void
    {
        $admin = User::factory()->admin()->create(['full_name' => 'Nguyễn Admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        // Check sidebar branding & badge
        $response->assertSee('Study');
        $response->assertSee('PREP');
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
        $response->assertSee('Phân quyền & Vai trò', false);
        $response->assertSee('Báo cáo & Thống kê', false);
        $response->assertSee('Cài đặt hệ thống');

        // Check profile card
        $response->assertSee('Nguyễn Admin');
        $response->assertSee('Super Admin');

        // Check header topbar
        $response->assertSee('StudyPREP Global');

        // Check KPI cards
        $response->assertSee('HỌC VIÊN HOẠT ĐỘNG');
        $response->assertSee('LƯỢT LÀM BÀI HÔM NAY');

        // Check Chart Section
        $response->assertSee('Xu hướng luyện thi & Lượt nộp bài', false);
        $response->assertSee('Listening & Reading', false);
        $response->assertSee('Writing & Speaking AI', false);
        $response->assertSee('Cập nhật 5 phút trước');

        // Check Review Queue Section
        $response->assertSee('Hàng đợi kiểm duyệt & Chấm bài', false);
        $response->assertSee('Phản ánh câu hỏi #Q-4402 (Part 5 Incomplete Sentences)');
        $response->assertSee('Cần duyệt');
        $response->assertSee('Xem câu hỏi');
    }
}
