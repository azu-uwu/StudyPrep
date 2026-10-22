<?php

namespace Tests\Feature\Admin;

use App\Models\ExamSection;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class QuestionControllerTest extends TestCase
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
        $response = $this->get(route('admin.questions.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_question_bank(): void
    {
        $student = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($student)->get(route('admin.questions.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_question_bank(): void
    {
        $admin = User::factory()->admin()->create();

        $skill = Skill::create(['skill_id' => 1, 'code' => 'LC', 'skill_name' => 'Listening']);
        $section = ExamSection::create(['section_id' => 1, 'skill_id' => 1, 'code' => 'PART1', 'name' => 'Part 1', 'sequence_no' => 1]);
        $type = QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        Question::create([
            'question_id' => 10892,
            'section_id' => 1,
            'question_type_id' => 1,
            'content' => 'Sample Question Content for TOEIC test',
            'difficulty' => 'MEDIUM',
            'points' => 1,
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.questions.index'));

        $response->assertOk();
        $response->assertViewIs('admin.questions.index');
        $response->assertSee('Kho Đề thi');
        $response->assertSee('Câu hỏi Chuẩn hóa');
        $response->assertSee('Tổng số câu hỏi');
        $response->assertSee('TOEIC Listening');
        $response->assertSee('TOEIC Speaking');
        $response->assertSee('Cần kiểm duyệt');
        $response->assertSee('Xuất Excel');
        $response->assertSee('Sample Question Content for TOEIC test');
    }

    public function test_admin_can_create_new_question(): void
    {
        $admin = User::factory()->admin()->create();

        $skill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        $section = ExamSection::create(['skill_id' => $skill->skill_id, 'code' => 'PART5', 'name' => 'Part 5', 'sequence_no' => 5]);
        $type = QuestionType::create(['name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $response = $this->actingAs($admin)->post(route('admin.questions.store'), [
            'content' => 'The manager requested that all employees submit their reports by Friday.',
            'section_id' => $section->section_id,
            'question_type_id' => $type->question_type_id,
            'difficulty' => 'MEDIUM',
            'points' => 1.00,
            'status' => 'ACTIVE',
            'explanation' => 'Kiểm tra cấu trúc câu subjunctive.',
            'opt_a' => 'submits',
            'opt_b' => 'submit',
            'opt_c' => 'submitted',
            'opt_d' => 'submitting',
            'correct_opt' => 'B',
        ]);

        $response->assertRedirect(route('admin.questions.index'));
        $this->assertDatabaseHas('questions', [
            'content' => 'The manager requested that all employees submit their reports by Friday.',
            'difficulty' => 'MEDIUM',
            'status' => 'ACTIVE',
        ]);
    }

    public function test_admin_can_update_question(): void
    {
        $admin = User::factory()->admin()->create();

        $skill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        $section = ExamSection::create(['skill_id' => $skill->skill_id, 'code' => 'PART5', 'name' => 'Part 5', 'sequence_no' => 5]);
        $type = QuestionType::create(['name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $question = Question::create([
            'section_id' => $section->section_id,
            'question_type_id' => $type->question_type_id,
            'content' => 'Nội dung câu hỏi cũ',
            'difficulty' => 'EASY',
            'points' => 1,
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.questions.update', $question->question_id), [
            'content' => 'Nội dung câu hỏi mới đã sửa',
            'section_id' => $section->section_id,
            'difficulty' => 'HARD',
            'status' => 'ACTIVE',
        ]);

        $response->assertRedirect(route('admin.questions.index'));
        $this->assertEquals('Nội dung câu hỏi mới đã sửa', $question->fresh()->content);
        $this->assertEquals('HARD', $question->fresh()->difficulty);
    }

    public function test_admin_can_toggle_question_status(): void
    {
        $admin = User::factory()->admin()->create();

        $skill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        $section = ExamSection::create(['section_id' => 5, 'skill_id' => 2, 'code' => 'PART5', 'name' => 'Part 5', 'sequence_no' => 5]);
        $type = QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $question = Question::create([
            'section_id' => 5,
            'question_type_id' => 1,
            'content' => 'Nội dung kiểm tra duyệt',
            'difficulty' => 'EASY',
            'points' => 1,
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.questions.toggle-status', $question->question_id), [
            'status' => 'ACTIVE',
        ]);

        $response->assertRedirect();
        $this->assertEquals('ACTIVE', $question->fresh()->status);
    }

    public function test_admin_can_delete_question(): void
    {
        $admin = User::factory()->admin()->create();

        $skill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        $section = ExamSection::create(['section_id' => 5, 'skill_id' => 2, 'code' => 'PART5', 'name' => 'Part 5', 'sequence_no' => 5]);
        $type = QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $question = Question::create([
            'section_id' => 5,
            'question_type_id' => 1,
            'content' => 'Câu hỏi cần xóa',
            'difficulty' => 'EASY',
            'points' => 1,
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.questions.destroy', $question->question_id));

        $response->assertRedirect(route('admin.questions.index'));
        $this->assertDatabaseMissing('questions', ['question_id' => $question->question_id]);
    }

    public function test_admin_can_export_questions_to_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.questions.export'));

        $response->assertOk();
        $this->assertTrue(str_contains((string) $response->headers->get('Content-Disposition'), 'ngan_hang_cau_hoi_studyprep_'));
    }

    public function test_admin_can_view_create_question_page(): void
    {
        $admin = User::factory()->admin()->create();

        $skill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        ExamSection::create(['section_id' => 5, 'skill_id' => $skill->skill_id, 'code' => 'PART5', 'name' => 'Part 5', 'sequence_no' => 5]);
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $response = $this->actingAs($admin)->get(route('admin.questions.create'));

        $response->assertOk();
        $response->assertViewIs('admin.questions.create');
        $response->assertSee('Thêm câu hỏi TOEIC mới');
        $response->assertSee('Cấu hình thông tin danh mục & Chuẩn kiểm định', false);
        $response->assertSee('Mô phỏng giao diện học viên');
        $response->assertSee('Lưu & Xuất bản câu hỏi', false);
    }

    public function test_admin_can_save_draft_question(): void
    {
        $admin = User::factory()->admin()->create();

        $skill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        $section = ExamSection::create(['section_id' => 5, 'skill_id' => $skill->skill_id, 'code' => 'PART5', 'name' => 'Part 5', 'sequence_no' => 5]);
        $type = QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $response = $this->actingAs($admin)->post(route('admin.questions.store'), [
            'content' => 'Because the recent marketing campaign was extraordinarily -------, the board approved...',
            'section_id' => $section->section_id,
            'question_type_id' => $type->question_type_id,
            'action_type' => 'draft',
            'opt_a' => 'success',
            'opt_b' => 'successful',
            'opt_c' => 'successfully',
            'opt_d' => 'succeed',
            'correct_option' => 'B',
        ]);

        $response->assertRedirect(route('admin.questions.index'));
        $this->assertDatabaseHas('questions', [
            'status' => 'DRAFT',
        ]);
        $this->assertDatabaseHas('answer_option', [
            'label' => 'B',
            'content' => 'successful',
            'is_correct' => 1,
        ]);
    }

    public function test_admin_can_view_bulk_import_page(): void
    {
        $admin = User::factory()->admin()->create();

        $skill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        ExamSection::create(['section_id' => 5, 'skill_id' => $skill->skill_id, 'code' => 'PART5', 'name' => 'Part 5', 'sequence_no' => 5]);

        $response = $this->actingAs($admin)->get(route('admin.questions.bulk-import'));

        $response->assertOk();
        $response->assertViewIs('admin.questions.bulk-import');
        $response->assertSee('Nhập câu hỏi TOEIC hàng loạt');
        $response->assertSee('#IMPORT-2025-04');
        $response->assertSee('Ánh xạ trường dữ liệu (Field Mapping)');
        $response->assertSee('Kiểm tra hợp lệ & Xem trước dữ liệu', false);
        $response->assertSee('ETS_2024_Test05_Batch.xlsx');
    }

    public function test_admin_can_execute_bulk_import(): void
    {
        $admin = User::factory()->admin()->create();

        $skill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        ExamSection::create(['section_id' => 5, 'skill_id' => $skill->skill_id, 'code' => 'PART5', 'name' => 'Part 5', 'sequence_no' => 5]);

        $response = $this->actingAs($admin)->post(route('admin.questions.import'), [
            'bulk_confirm' => '1',
            'publish_status' => 'live',
            'selected_count' => 116,
        ]);

        $response->assertRedirect(route('admin.questions.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('questions', [
            'status' => 'ACTIVE',
        ]);
        $this->assertDatabaseHas('answer_option', [
            'content' => 'before',
            'is_correct' => 1,
        ]);
    }

    public function test_admin_can_view_create_question_group_page(): void
    {
        $admin = User::factory()->admin()->create();

        $skill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        ExamSection::create(['section_id' => 7, 'skill_id' => $skill->skill_id, 'code' => 'PART7', 'name' => 'Part 7', 'sequence_no' => 7]);

        $response = $this->actingAs($admin)->get(route('admin.questions.group.create'));

        $response->assertOk();
        $response->assertViewIs('admin.questions.create-group');
        $response->assertSee('Tạo nhóm câu hỏi TOEIC');
        $response->assertSee('#GRP-2025-RC07-M2');
        $response->assertSee('Phân loại & Ngữ cảnh nhóm câu hỏi', false);
        $response->assertSee('Dữ liệu ngữ cảnh dùng chung (Stimulus)', false);
        $response->assertSee('Danh sách 5 câu hỏi con', false);
    }

    public function test_admin_can_store_question_group_with_sub_questions(): void
    {
        $admin = User::factory()->admin()->create();

        $skill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        $section = ExamSection::create(['section_id' => 7, 'skill_id' => $skill->skill_id, 'code' => 'PART7', 'name' => 'Part 7', 'sequence_no' => 7]);
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $response = $this->actingAs($admin)->post(route('admin.questions.group.store'), [
            'group_title' => 'Thông báo tuyển dụng & Email trao đổi lịch phỏng vấn',
            'section_id' => $section->section_id,
            'test_pack' => 'ETS TOEIC 2024 - Test 03 #02',
            'passage_1_title' => 'BrightTech Solutions - Senior Frontend Developer Opening',
            'passage_1_content' => 'BrightTech Solutions is seeking an accomplished Senior Frontend Developer...',
            'passage_2_from' => 'Clara Sanders <c.sanders@techmail.org>',
            'passage_2_to' => 'BrightTech HR Department <hr@brighttech-solutions.com>',
            'passage_2_content' => 'Dear Hiring Committee: Thank you for reviewing my credentials...',
            'action_type' => 'publish',
        ]);

        $response->assertRedirect(route('admin.questions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('question_set', [
            'title' => 'Thông báo tuyển dụng & Email trao đổi lịch phỏng vấn',
        ]);

        $this->assertDatabaseHas('questions', [
            'section_id' => $section->section_id,
            'status' => 'ACTIVE',
        ]);

        $this->assertDatabaseHas('answer_option', [
            'content' => 'It is actively expanding its engineering staff.',
            'is_correct' => 1,
        ]);
    }
}
