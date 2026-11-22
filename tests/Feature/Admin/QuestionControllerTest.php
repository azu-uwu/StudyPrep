<?php

namespace Tests\Feature\Admin;

use App\Models\AnswerOption;
use App\Models\ExamSection;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\QuestionType;
use App\Models\Skill;
use App\Models\TestBlueprint;
use App\Models\TestForm;
use App\Models\User;
use App\Services\TestFormService;
use App\Services\WordQuestionImportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
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
        $questionSet = QuestionSet::create([
            'title' => 'Admin test set',
            'media_url' => 'https://example.test/question.png',
            'media_type' => 'image',
        ]);

        Question::create([
            'question_id' => 10892,
            'section_id' => 1,
            'question_type_id' => 1,
            'question_set_id' => $questionSet->question_set_id,
            'content' => 'Sample Question Content for TOEIC test',
            'difficulty' => 'MEDIUM',
            'points' => 1,
            'status' => 'ACTIVE',
        ]);
        AnswerOption::create([
            'question_id' => 10892,
            'label' => 'A',
            'content' => 'Sample option A',
            'is_correct' => true,
        ]);
        AnswerOption::create([
            'question_id' => 10892,
            'label' => 'B',
            'content' => 'Sample option B',
            'is_correct' => false,
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
        $response->assertSee('Sample option A');
        $response->assertSee('https://example.test/question.png');
    }

    public function test_admin_can_filter_archived_questions_by_part_and_skill(): void
    {
        $admin = User::factory()->admin()->create();
        Skill::create(['skill_id' => 1, 'code' => 'LC', 'skill_name' => 'Listening']);
        ExamSection::create([
            'section_id' => 1,
            'skill_id' => 1,
            'code' => 'PART5',
            'name' => 'Part 5',
            'sequence_no' => 5,
        ]);
        QuestionType::create([
            'question_type_id' => 1,
            'name' => 'Multiple Choice',
            'response_mode' => 'SINGLE_CHOICE',
        ]);

        Question::create([
            'section_id' => 1,
            'question_type_id' => 1,
            'content' => 'Archived filtered question',
            'difficulty' => 'MEDIUM',
            'status' => 'ARCHIVED',
        ]);
        Question::create([
            'section_id' => 1,
            'question_type_id' => 1,
            'content' => 'Active unfiltered question',
            'difficulty' => 'MEDIUM',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.questions.index', [
            'skill' => 'LC',
            'part' => 'PART5',
            'status' => 'ARCHIVED',
        ]));

        $response->assertOk();
        $response->assertSee('Archived filtered question');
        $response->assertDontSee('Active unfiltered question');
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
        $response->assertSee('Tải file Word mẫu (.docx)');
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

    public function test_admin_can_download_sample_word_docx_template(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.questions.sample-docx'));

        $response->assertOk();
        $this->assertStringContainsString(
            'mau_nhap_cau_hoi_toeic_studyprep.docx',
            (string) $response->headers->get('Content-Disposition')
        );
    }

    public function test_admin_can_import_questions_from_word_docx_file(): void
    {
        $admin = User::factory()->admin()->create();

        $skill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        $part5 = ExamSection::create(['section_id' => 5, 'skill_id' => $skill->skill_id, 'code' => 'PART5', 'name' => 'Part 5', 'sequence_no' => 5]);
        $part7 = ExamSection::create(['section_id' => 7, 'skill_id' => $skill->skill_id, 'code' => 'PART7', 'name' => 'Part 7', 'sequence_no' => 7]);
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $wordService = app(WordQuestionImportService::class);
        $docxBinary = $wordService->buildDocxFromParagraphs([
            '[Part 5]',
            'Câu 1: The new software update will ------- system security across all branches.',
            'A. enhance',
            'B. enhanced',
            'C. enhancing',
            'D. enhancement',
            'Đáp án: A',
            'Độ khó: MEDIUM',
            'Giải thích: Sau động từ khuyết thiếu "will" cần động từ nguyên thể "enhance".',
            '[Part 7]',
            'Đoạn văn: Internal Memo: The annual shareholders meeting will take place in Conference Room B at 10:00 AM.',
            'Câu 2: Where will the meeting be held?',
            'A. In the main lobby',
            'B. In Conference Room B',
            'C. At the downtown hotel',
            'D. In the cafeteria',
            'Đáp án: B',
            'Độ khó: EASY',
            'Giải thích: Thông báo ghi rõ "in Conference Room B".',
        ]);

        $uploadedFile = UploadedFile::fake()->createWithContent('de_toeic_mau.docx', $docxBinary);

        $response = $this->actingAs($admin)->post(route('admin.questions.import'), [
            'import_file' => $uploadedFile,
            'publish_status' => 'live',
        ]);

        $response->assertRedirect(route('admin.questions.index'));
        $this->assertDatabaseHas('questions', [
            'content' => 'The new software update will ------- system security across all branches.',
            'section_id' => $part5->section_id,
            'status' => 'ACTIVE',
        ]);
        $this->assertDatabaseHas('questions', [
            'content' => 'Where will the meeting be held?',
            'section_id' => $part7->section_id,
            'difficulty' => 'EASY',
        ]);
        $this->assertDatabaseHas('question_set', [
            'stimulus_text' => 'Internal Memo: The annual shareholders meeting will take place in Conference Room B at 10:00 AM.',
        ]);
        $this->assertDatabaseHas('answer_option', [
            'label' => 'A',
            'content' => 'enhance',
            'is_correct' => 1,
        ]);
    }

    public function test_admin_can_assign_questions_from_bank_to_test_and_avoid_duplicates(): void
    {
        if (! Schema::hasTable('test_form_question')) {
            Schema::create('test_form_question', function (Blueprint $table): void {
                $table->unsignedBigInteger('test_form_id');
                $table->unsignedBigInteger('question_id');
                $table->integer('sequence_no');
                $table->decimal('point', 6, 2)->default(1);
                $table->primary(['test_form_id', 'question_id']);
            });
        }

        $admin = User::factory()->admin()->create();
        $skill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        $section = ExamSection::create(['section_id' => 5, 'skill_id' => $skill->skill_id, 'code' => 'PART5', 'name' => 'Part 5', 'sequence_no' => 5]);
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $blueprint = TestBlueprint::create([
            'name' => 'TOEIC Listening & Reading (Full Test)',
            'exam_type' => 'TOEIC_LR',
            'total_time' => 120,
            'status' => 'ACTIVE',
        ]);
        $testForm = TestForm::create([
            'blueprint_id' => $blueprint->blueprint_id,
            'title' => 'ETS Official Test 10',
        ]);

        $q1 = Question::create([
            'section_id' => $section->section_id,
            'question_type_id' => 1,
            'content' => 'Question 1 to attach',
            'difficulty' => 'EASY',
            'status' => 'ACTIVE',
        ]);
        $q2 = Question::create([
            'section_id' => $section->section_id,
            'question_type_id' => 1,
            'content' => 'Question 2 to attach',
            'difficulty' => 'MEDIUM',
            'status' => 'ACTIVE',
        ]);

        // Gắn lần 1 từ Ngân hàng câu hỏi
        $response = $this->actingAs($admin)->post(route('admin.questions.bulk-action'), [
            'action' => 'assign',
            'test_id' => $testForm->test_form_id,
            'question_ids' => [$q1->question_id, $q2->question_id],
        ]);

        $response->assertRedirect();
        $this->assertEquals(2, $testForm->questions()->count());

        // Gắn lần 2 trùng lặp câu q1 -> không bị lỗi duplicate PK và giữ nguyên 2 câu
        $this->actingAs($admin)->post(route('admin.tests.questions.attach', $testForm->test_form_id), [
            'question_ids' => [$q1->question_id],
        ]);

        $this->assertEquals(2, $testForm->questions()->count());
    }

    public function test_bilingual_docx_import_merges_vietnamese_translation_into_explanation_and_groups_question_set(): void
    {
        $admin = User::factory()->admin()->create();
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $wordService = app(WordQuestionImportService::class);
        $docxBinary = $wordService->buildDocxFromParagraphs([
            '[Part 4]',
            'Questions 99-100 refer to the following talk and chart.',
            '[IMAGE_URL: storage/questions/imported/test_batch/chart99.png]',
            '99. Look at the graphic. At what depth should samples be collected this month?',
            'A. 12 inches',
            'B. 4 inches',
            'C. 6 inches',
            'D. 8 inches',
            '99. Nhìn vào ảnh. Mẫu nên được thu thập ở độ sâu nào trong tháng này?',
            'A. 12 inch',
            'B. 4 inch',
            'C. 6 inch',
            'D. 8 inch',
            '100. What does the speaker encourage the listeners to do?',
            'A. Turn off mobile phones',
            'B. Have some refreshments',
            'C. Purchase some seeds',
            'D. Sign up for a mailing list',
            'Người nói khuyến khích người nghe làm gì?',
            'A. Tắt điện thoại di động',
            'B. Dùng đồ giải khát',
            'C. Mua hạt giống',
            'D. Đăng ký danh sách gửi thư',
        ]);

        $uploadedFile = UploadedFile::fake()->createWithContent('listening_bilingual.docx', $docxBinary);

        $response = $this->actingAs($admin)->post(route('admin.questions.import'), [
            'import_file' => $uploadedFile,
            'publish_status' => 'live',
        ]);

        $response->assertRedirect(route('admin.questions.index'));

        // Chỉ tạo đúng 2 câu hỏi tiếng Anh, KHÔNG tách bản dịch tiếng Việt thành câu riêng!
        $questions = Question::query()->with(['options', 'questionSet'])->orderBy('question_id')->get();
        $this->assertCount(2, $questions);

        $q99 = $questions[0];
        $q100 = $questions[1];

        $this->assertStringContainsString('Look at the graphic', $q99->content);
        $this->assertStringContainsString('Dịch đề bài: Nhìn vào ảnh', (string) $q99->explanation);
        $this->assertStringContainsString('(A) 12 inch', (string) $q99->explanation);
        $this->assertEquals('ACTIVE', $q99->status);

        $this->assertStringContainsString('What does the speaker encourage', $q100->content);
        $this->assertStringContainsString('Dịch đề bài: Người nói khuyến khích người nghe làm gì?', (string) $q100->explanation);
        $this->assertStringContainsString('(D) Đăng ký danh sách gửi thư', (string) $q100->explanation);

        // Cả 2 câu thuộc nhóm Questions 99-100 phải dùng chung 1 QuestionSet duy nhất và giữ đúng ảnh biểu đồ
        $this->assertNotNull($q99->question_set_id);
        $this->assertEquals($q99->question_set_id, $q100->question_set_id);
        $this->assertEquals('storage/questions/imported/test_batch/chart99.png', $q99->questionSet->media_url);
        $this->assertStringEndsWith('/storage/questions/imported/test_batch/chart99.png', (string) $q99->questionSet->resolved_media_url);
    }

    public function test_image_placeholder_after_completed_question_binds_forward_not_backward(): void
    {
        $wordService = app(WordQuestionImportService::class);
        $parsed = $wordService->parseLines([
            '[Part 4]',
            '97. What are the listeners reminded to do?',
            'A. Complete a survey',
            'B. Donate money',
            'C. Join a club',
            'D. Post photos',
            '[IMAGE_URL: storage/questions/imported/batch1/graphic99.png]',
            '99. Look at the graphic. At what depth should samples be collected?',
            'A. 12 inches',
            'B. 4 inches',
            'C. 6 inches',
            'D. 8 inches',
        ]);

        $this->assertCount(2, $parsed);
        $this->assertNull($parsed[0]['image_url']);
        $this->assertEquals('storage/questions/imported/batch1/graphic99.png', $parsed[1]['image_url']);
    }

    public function test_question_set_resolved_media_url_normalizes_legacy_and_relative_paths(): void
    {
        $legacy = new QuestionSet(['media_url' => '/storage/questions/imported/old/image7.png']);
        $relative = new QuestionSet(['media_url' => 'storage/questions/imported/new/image8.png']);
        $external = new QuestionSet(['media_url' => 'https://cdn.example.com/a.png']);

        $this->assertEquals(asset('storage/questions/imported/old/image7.png'), $legacy->resolved_media_url);
        $this->assertEquals(asset('storage/questions/imported/new/image8.png'), $relative->resolved_media_url);
        $this->assertEquals('https://cdn.example.com/a.png', $external->resolved_media_url);
    }

    public function test_store_supports_part2_three_options_and_speaking_without_mcq_and_auto_attaches_to_test(): void
    {
        if (! Schema::hasTable('test_form_question')) {
            Schema::create('test_form_question', function (Blueprint $table): void {
                $table->unsignedBigInteger('test_form_id');
                $table->unsignedBigInteger('question_id');
                $table->integer('sequence_no');
                $table->decimal('point', 6, 2)->default(1);
                $table->primary(['test_form_id', 'question_id']);
            });
        }

        $admin = User::factory()->admin()->create();
        app(TestFormService::class)->ensureStandardSectionsExist();
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        // 1. Part 2: chỉ cần 3 phương án A, B, C (ẩn D) + lưu transcript vào QuestionSet
        $part2 = ExamSection::where('code', 'PART2')->firstOrFail();
        $respP2 = $this->actingAs($admin)->post(route('admin.questions.store'), [
            'section_id' => $part2->section_id,
            'content' => 'Where is the quarterly meeting being held?',
            'transcript' => 'Where is the quarterly meeting being held? A. In Room 202. B. At 3 PM. C. Yes, I did.',
            'opt_a' => 'In Room 202.',
            'opt_b' => 'At 3 PM.',
            'opt_c' => 'Yes, I did.',
            'correct_option' => 'A',
        ]);
        $respP2->assertRedirect(route('admin.questions.index'));

        $p2Question = Question::where('content', 'Where is the quarterly meeting being held?')->with(['options', 'questionSet'])->firstOrFail();
        $this->assertCount(3, $p2Question->options);
        $this->assertNotNull($p2Question->questionSet);
        $this->assertEquals('AUDIO', $p2Question->questionSet->media_type);

        // 2. Speaking Part 1 gắn trực tiếp vào đề TOEIC_SPEAKING (không cần phương án A-D)
        $spk1 = ExamSection::where('code', 'SPK_T1')->firstOrFail();
        $spkTest = app(TestFormService::class)->create([
            'title' => 'Speaking Auto Attach Test',
            'exam_type' => TestForm::EXAM_TYPE_SPEAKING,
        ]);

        // Mở form /admin/questions/create?test_id=... -> tự động lọc đúng các Part của đề Speaking
        $createPage = $this->actingAs($admin)->get(route('admin.questions.create', ['test_id' => $spkTest->test_form_id]));
        $createPage->assertOk();
        $createPage->assertSee('Speaking Auto Attach Test');
        $createPage->assertSee('data-code="SPK_T1"', false);
        $createPage->assertDontSee('data-code="PART5"', false);

        $respSpk = $this->actingAs($admin)->post(route('admin.questions.store'), [
            'test_id' => $spkTest->test_form_id,
            'section_id' => $spk1->section_id,
            'content' => 'Read the airport announcement aloud clearly.',
            'stimulus_text' => 'Welcome aboard Flight 408 bound for Seattle.',
            'explanation' => 'Focus on clear consonant endings.',
        ]);
        $respSpk->assertRedirect(route('admin.tests.show', $spkTest->test_form_id));
        $this->assertEquals(1, $spkTest->questions()->count());

        $spkQ = $spkTest->questions()->with(['options', 'questionSet'])->firstOrFail();
        $this->assertCount(0, $spkQ->options);
        $this->assertEquals('Welcome aboard Flight 408 bound for Seattle.', $spkQ->questionSet->stimulus_text);
    }

    public function test_bilingual_arrow_options_and_part2_three_options_have_zero_english_leak(): void
    {
        $admin = User::factory()->admin()->create();
        app(TestFormService::class)->ensureStandardSectionsExist();
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $wordService = app(WordQuestionImportService::class);
        $docxBinary = $wordService->buildDocxFromParagraphs([
            '[Part 2]',
            'Câu 7: When will the new shipment arrive?',
            '(A) By Thursday afternoon.',
            '(B) At the warehouse entrance.',
            '(C) Yes, we ordered ten boxes.',
            'Khi nào lô hàng mới sẽ tới?',
            '→ Trước chiều thứ Năm.',
            '→ Tại lối vào nhà kho.',
            '→ Vâng, chúng tôi đã đặt mười thùng.',
            'Đáp án: A',
        ]);

        $uploadedFile = UploadedFile::fake()->createWithContent('part2_arrow_bilingual.docx', $docxBinary);

        $response = $this->actingAs($admin)->post(route('admin.questions.import'), [
            'import_file' => $uploadedFile,
            'import_scope' => 'LISTENING',
            'publish_status' => 'live',
        ]);

        $response->assertRedirect(route('admin.questions.index'));

        $questions = Question::query()->with(['options', 'section'])->get();
        $this->assertCount(1, $questions);

        $q = $questions->first();
        $this->assertEquals('PART2', strtoupper((string) $q->section->code));
        $this->assertCount(3, $q->options);
        $this->assertEquals(['A', 'B', 'C'], $q->options->pluck('label')->sort()->values()->all());
        $this->assertStringContainsString('Dịch đề bài: Khi nào lô hàng mới sẽ tới?', (string) $q->explanation);
        $this->assertStringContainsString('(A) Trước chiều thứ Năm.', (string) $q->explanation);
        $this->assertStringNotContainsString('At the warehouse entrance', (string) $q->explanation);
    }

    public function test_four_skill_speaking_and_writing_docx_import_creates_no_answer_options_and_preserves_stimulus(): void
    {
        $admin = User::factory()->admin()->create();
        app(TestFormService::class)->ensureStandardSectionsExist();
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $wordService = app(WordQuestionImportService::class);
        $docxBinary = $wordService->buildDocxFromParagraphs([
            '[Speaking Task 1: Read a Text Aloud]',
            'Đoạn văn: Good afternoon passengers. The express train to Boston will depart from Platform 4.',
            'Câu 1: Read the station announcement aloud clearly with natural intonation.',
            'Giải thích: Nhấn trọng âm ở express train và Platform 4.',
            '[Speaking Task 2: Describe a Picture]',
            '[IMAGE_URL: storage/questions/imported/sw_batch/office_meeting.png]',
            'Câu 2: Describe the picture on your screen in as much detail as you can.',
            'Giải thích: Bài mẫu: This picture shows four colleagues discussing a project around a conference table.',
            '[Writing Task 2: Respond to a Written Request]',
            'Email: From: Laura Chen <laura@client.com>',
            'Subject: Catering Inquiry for June 15',
            'Could you send us your vegetarian menu options and pricing?',
            'Câu 3: Respond to the e-mail as if you are the catering manager. Provide two menu choices and ask one question.',
            'Giải thích: Dàn ý: Cảm ơn khách hàng -> Giới thiệu 2 set menu chay -> Hỏi số lượng khách tham dự.',
            '[Writing Task 3: Write an Opinion Essay]',
            'Câu 4: Do you agree or disagree that remote work increases employee productivity? Give specific reasons.',
            'Giải thích: Nên viết tối thiểu 300 từ gồm Mở bài, 2 đoạn Thân bài và Kết bài.',
        ]);

        $uploadedFile = UploadedFile::fake()->createWithContent('toeic_sw_4skills.docx', $docxBinary);

        $response = $this->actingAs($admin)->post(route('admin.questions.import'), [
            'import_file' => $uploadedFile,
            'import_scope' => 'AUTO',
            'publish_status' => 'live',
        ]);

        $response->assertRedirect(route('admin.questions.index'));

        $questions = Question::query()->with(['options', 'section', 'questionSet'])->orderBy('question_id')->get();
        $this->assertCount(4, $questions);

        $codes = $questions->map(fn (Question $q) => strtoupper((string) $q->section?->code))->all();
        $this->assertEquals(['SPK_T1', 'SPK_T2', 'WRITING_T2', 'WRITING_T3'], $codes);

        // Tuyệt đối không tạo AnswerOption A/B/C/D cho Speaking & Writing
        foreach ($questions as $q) {
            $this->assertCount(0, $q->options, "Question {$q->section?->code} must not have MCQ options");
        }

        // Kiểm tra ngữ liệu Speaking Task 1, ảnh Speaking Task 2, email nhiều dòng Writing Task 2
        $this->assertStringContainsString('express train to Boston', (string) $questions[0]->questionSet?->stimulus_text);
        $this->assertEquals('storage/questions/imported/sw_batch/office_meeting.png', $questions[1]->questionSet?->media_url);
        $this->assertStringContainsString('Catering Inquiry for June 15', (string) $questions[2]->questionSet?->stimulus_text);
        $this->assertStringContainsString('vegetarian menu options', (string) $questions[2]->questionSet?->stimulus_text);
    }

    public function test_sample_docx_download_supports_skill_parameter(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (['all', 'listening', 'reading', 'speaking', 'writing'] as $skill) {
            $response = $this->actingAs($admin)->get(route('admin.questions.sample-docx', ['skill' => $skill]));
            $response->assertOk();
            $this->assertStringContainsString('.docx', (string) $response->headers->get('Content-Disposition'));
        }
    }

    public function test_question_preview_payload_includes_sibling_group_questions_and_ets_timing(): void
    {
        app(TestFormService::class)->ensureStandardSectionsExist();
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $part3 = ExamSection::where('code', 'PART3')->firstOrFail();
        $spk2 = ExamSection::where('code', 'SPK_T2')->firstOrFail();

        $set = QuestionSet::create([
            'title' => 'Part 3 Group',
            'transcript' => 'M: Where is the invoice? W: On your desk.',
            'media_url' => 'storage/questions/imported/demo/chart.png',
            'media_type' => 'IMAGE',
        ]);

        $q1 = Question::create([
            'section_id' => $part3->section_id,
            'question_type_id' => 1,
            'question_set_id' => $set->question_set_id,
            'content' => 'What is the man looking for?',
            'difficulty' => 'MEDIUM',
            'status' => 'ACTIVE',
        ]);
        $q2 = Question::create([
            'section_id' => $part3->section_id,
            'question_type_id' => 1,
            'question_set_id' => $set->question_set_id,
            'content' => 'Where is the document located?',
            'difficulty' => 'MEDIUM',
            'status' => 'ACTIVE',
        ]);
        AnswerOption::create(['question_id' => $q1->question_id, 'label' => 'A', 'content' => 'An invoice', 'is_correct' => true]);
        AnswerOption::create(['question_id' => $q2->question_id, 'label' => 'B', 'content' => 'On a desk', 'is_correct' => true]);

        $loadedQ1 = Question::with(['section.skill', 'options', 'questionSet.questions.options'])->findOrFail($q1->question_id);
        $payload = $loadedQ1->toPreviewPayload();

        $this->assertEquals('listening', $payload['skill_key']);
        $this->assertEquals(2, $payload['group_count']);
        $this->assertCount(2, $payload['question_set']['questions']);
        $this->assertTrue($payload['question_set']['is_image']);
        $this->assertEquals('What is the man looking for?', $payload['question_set']['questions'][0]['content']);
        $this->assertEquals('Where is the document located?', $payload['question_set']['questions'][1]['content']);

        $spkQ = Question::create([
            'section_id' => $spk2->section_id,
            'question_type_id' => 1,
            'content' => 'Describe this picture.',
            'explanation' => 'Sample answer text.',
            'difficulty' => 'MEDIUM',
            'status' => 'ACTIVE',
        ]);
        $spkPayload = Question::with(['section.skill', 'options'])->findOrFail($spkQ->question_id)->toPreviewPayload();

        $this->assertEquals('speaking', $spkPayload['skill_key']);
        $this->assertEquals('SPK Task 2', $spkPayload['part_badge']);
        $this->assertEquals('45 giây', $spkPayload['ets_timing']['prep']);
        $this->assertEquals('30 giây', $spkPayload['ets_timing']['response']);
    }

    public function test_bulk_action_select_all_applies_to_all_matching_questions_beyond_page_limit(): void
    {
        $admin = User::factory()->admin()->create();
        app(TestFormService::class)->ensureStandardSectionsExist();
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $part1 = ExamSection::where('code', 'PART1')->firstOrFail();
        $part5 = ExamSection::where('code', 'PART5')->firstOrFail();

        // Tạo 15 câu Part 5 và 10 câu Part 1 (tổng 25 câu, vượt giới hạn mặc định 10 câu/trang)
        for ($i = 1; $i <= 15; $i++) {
            Question::create([
                'section_id' => $part5->section_id,
                'question_type_id' => 1,
                'content' => "Part 5 bulk question #{$i}",
                'difficulty' => 'MEDIUM',
                'status' => 'DRAFT',
            ]);
        }

        for ($i = 1; $i <= 10; $i++) {
            Question::create([
                'section_id' => $part1->section_id,
                'question_type_id' => 1,
                'content' => "Part 1 bulk question #{$i}",
                'difficulty' => 'EASY',
                'status' => 'DRAFT',
            ]);
        }

        // 1. Chọn tất cả (không lọc) -> Phê duyệt cả 25 câu, không bị giới hạn 10 câu
        $approveResponse = $this->actingAs($admin)->post(route('admin.questions.bulk-action'), [
            'action' => 'approve',
            'select_all' => '1',
            'filter_search' => '',
            'filter_skill' => 'all',
            'filter_part' => 'all',
            'filter_difficulty' => 'all',
            'filter_status' => 'all',
            'filter_tag' => 'all',
        ]);

        $approveResponse->assertRedirect(route('admin.questions.index'));
        $this->assertEquals(25, Question::where('status', 'ACTIVE')->count());

        // 2. Chọn tất cả theo bộ lọc Part 5 -> Xóa sạch cả 15 câu Part 5, giữ nguyên 10 câu Part 1
        $deleteResponse = $this->actingAs($admin)->post(route('admin.questions.bulk-action'), [
            'action' => 'delete',
            'select_all' => '1',
            'filter_search' => '',
            'filter_skill' => 'all',
            'filter_part' => 'PART5',
            'filter_difficulty' => 'all',
            'filter_status' => 'all',
            'filter_tag' => 'all',
        ]);

        $deleteResponse->assertRedirect(route('admin.questions.index'));
        $this->assertEquals(0, Question::where('section_id', $part5->section_id)->count());
        $this->assertEquals(10, Question::where('section_id', $part1->section_id)->count());
    }

    public function test_part1_docx_without_stem_text_parses_image_four_options_and_vietnamese_translation(): void
    {
        $admin = User::factory()->admin()->create();
        app(TestFormService::class)->ensureStandardSectionsExist();
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $wordService = app(WordQuestionImportService::class);
        $docxBinary = $wordService->buildDocxFromParagraphs([
            '[Part 1]',
            '1.',
            '[IMAGE_URL: storage/questions/imported/p1_batch/pic1.png]',
            '(A) A woman is typing on a laptop.',
            '(B) A woman is watering plants.',
            '(C) A woman is closing a window.',
            '(D) A woman is carrying a box.',
            '→ Một người phụ nữ đang gõ máy tính xách tay.',
            '→ Một người phụ nữ đang tưới cây.',
            '→ Một người phụ nữ đang đóng cửa sổ.',
            '→ Một người phụ nữ đang mang một chiếc hộp.',
            'Đáp án: A',
        ]);

        $uploadedFile = UploadedFile::fake()->createWithContent('part1_no_stem.docx', $docxBinary);

        $response = $this->actingAs($admin)->post(route('admin.questions.import'), [
            'import_file' => $uploadedFile,
            'import_scope' => 'LISTENING',
            'publish_status' => 'draft',
        ]);

        $response->assertRedirect(route('admin.questions.index'));

        $q = Question::query()->with(['section', 'options', 'questionSet'])->firstOrFail();
        $this->assertEquals('PART1', strtoupper((string) $q->section->code));
        $this->assertEquals('DRAFT', $q->status);
        $this->assertEquals('EASY', $q->difficulty);
        $this->assertEquals('Look at the picture and listen to the four statements.', $q->content);
        $this->assertCount(4, $q->options);
        $this->assertEquals('storage/questions/imported/p1_batch/pic1.png', $q->questionSet?->media_url);
        $this->assertStringContainsString('Dịch phương án:', (string) $q->explanation);
        $this->assertStringContainsString('(A) Một người phụ nữ đang gõ máy tính xách tay.', (string) $q->explanation);
        $this->assertStringNotContainsString('watering plants', (string) $q->explanation);
    }

    public function test_question_bank_shows_real_stats_and_filters_four_skills_independently(): void
    {
        $admin = User::factory()->admin()->create();
        app(TestFormService::class)->ensureStandardSectionsExist();
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $spk1 = ExamSection::where('code', 'SPK_T1')->firstOrFail();
        $wrt1 = ExamSection::where('code', 'WRITING_T1')->firstOrFail();

        Question::create([
            'section_id' => $spk1->section_id,
            'question_type_id' => 1,
            'content' => 'Speaking unique prompt alpha',
            'difficulty' => 'UNSET',
            'status' => 'DRAFT',
        ]);
        Question::create([
            'section_id' => $wrt1->section_id,
            'question_type_id' => 1,
            'content' => 'Writing unique prompt beta',
            'difficulty' => 'UNSET',
            'status' => 'ACTIVE',
        ]);

        // Mặc định không filter -> hiển thị cả DRAFT & ACTIVE, không còn số liệu giả
        $indexRes = $this->actingAs($admin)->get(route('admin.questions.index'));
        $indexRes->assertOk();
        $indexRes->assertSee('Speaking unique prompt alpha');
        $indexRes->assertSee('Writing unique prompt beta');
        $indexRes->assertSee('Không có câu hỏi báo lỗi');
        $indexRes->assertDontSee('32 phản hồi');
        $indexRes->assertDontSee('8,210 audio');

        // Lọc riêng Speaking
        $spkRes = $this->actingAs($admin)->get(route('admin.questions.index', ['skill' => 'SPK']));
        $spkRes->assertOk();
        $spkRes->assertSee('Speaking unique prompt alpha');
        $spkRes->assertDontSee('Writing unique prompt beta');

        // Lọc riêng Writing
        $wrtRes = $this->actingAs($admin)->get(route('admin.questions.index', ['skill' => 'WRT']));
        $wrtRes->assertOk();
        $wrtRes->assertSee('Writing unique prompt beta');
        $wrtRes->assertDontSee('Speaking unique prompt alpha');
    }

    public function test_reading_docx_maps_part123_dual_numbers_transcript_table_and_explanation(): void
    {
        $wordService = app(WordQuestionImportService::class);
        $parsed = $wordService->parseLines([
            'ACTUAL TEST 1 - READING',
            'PART 1',
            'Question 5 : 105. The director will ------- the budget tomorrow.',
            '(A) review',
            '(B) reviewed',
            '(C) reviewing',
            '(D) reviews',
            'Đáp án: A',
            'Giải thích chi tiết ▲',
            '➢ Chọn (A) review vì sau "will" cần động từ nguyên thể.',
            '➢ Loại trừ (B), (C), (D) sai thì/dạng từ.',
            'PART 2',
            'Transcript ▲',
            'Đây là bản dịch tiếng Việt của đoạn email nội bộ.',
            'Questions 131-132 refer to the following notice.',
            'Winnipeg | Toulouse | $450',
            'Please confirm your flight itinerary.',
            'Question 31 : 131',
            'What is the notice about?',
            '(A) Flight booking',
            '(B) Hotel reservation',
            '(C) Car rental',
            '(D) Train schedule',
            'Đáp án: A',
            'PART 3',
            'Questions 147-148 refer to the following memo.',
            'All staff must attend the safety briefing.',
            'Question 47 : 147. Who must attend the briefing?',
            '(A) Only managers',
            '(B) All staff',
            '(C) Visitors',
            '(D) Contractors',
            'Đáp án: B',
        ], 'READING');

        $this->assertCount(3, $parsed);
        $this->assertEquals('PART5', $parsed[0]['part_code']);
        $this->assertEquals(105, $parsed[0]['question_number']);
        $this->assertStringContainsString('Chọn (A) review', (string) $parsed[0]['explanation']);
        $this->assertStringContainsString('Loại trừ (B), (C), (D)', (string) $parsed[0]['explanation']);

        $this->assertEquals('PART6', $parsed[1]['part_code']);
        $this->assertEquals(131, $parsed[1]['question_number']);
        $this->assertStringContainsString('Winnipeg | Toulouse | $450', (string) $parsed[1]['stimulus_text']);
        $this->assertStringContainsString('Đây là bản dịch tiếng Việt', (string) $parsed[1]['audio_transcript']);

        $this->assertEquals('PART7', $parsed[2]['part_code']);
        $this->assertEquals(147, $parsed[2]['question_number']);
    }

    public function test_speaking_docx_parses_all_five_tasks_split_images_and_unnumbered_task4_questions(): void
    {
        $wordService = app(WordQuestionImportService::class);
        $parsed = $wordService->parseLines([
            'SPEAKING',
            'Read a text aloud (câu 1, 2)',
            'Hướng dẫn: Bạn có 45 giây chuẩn bị và 45 giây đọc.',
            'Câu 1: Welcome to City Library.',
            'Câu 2: Please turn off your phone.',
            'Describe a picture (câu 3, 4)',
            '[SPLIT_IMAGE_CELL]',
            '[IMAGE_URL: storage/questions/imported/spk/img3.png]',
            '[SPLIT_IMAGE_CELL]',
            '[IMAGE_URL: storage/questions/imported/spk/img4.png]',
            'Respond to questions (câu 5, 6, 7)',
            'Imagine that a Canadian magazine is interviewing you.',
            'Câu 5: How often do you read?',
            'Câu 6: What genre do you like?',
            'Câu 7: Do you prefer print or digital books?',
            'Respond to questions using information (câu 8, 9, 10)',
            'CONFERENCE SCHEDULE | 09:00 AM | Keynote',
            'Where is the conference being held?',
            'What time does the keynote start?',
            'Can you tell me about the afternoon workshops?',
            'Express an opinion (câu 11)',
            'Câu 11: Do you agree that remote work is better?',
        ], 'SPEAKING');

        $this->assertCount(11, $parsed);
        $codes = array_column($parsed, 'part_code');
        $this->assertEquals([
            'SPK_T1', 'SPK_T1',
            'SPK_T2', 'SPK_T2',
            'SPK_T3', 'SPK_T3', 'SPK_T3',
            'SPK_T4', 'SPK_T4', 'SPK_T4',
            'SPK_T5',
        ], $codes);

        // Task 2 tách 2 ảnh riêng biệt
        $this->assertEquals('storage/questions/imported/spk/img3.png', $parsed[2]['image_url']);
        $this->assertEquals('storage/questions/imported/spk/img4.png', $parsed[3]['image_url']);

        // Task 3 dùng chung Imagine that...
        $this->assertEquals('Imagine that a Canadian magazine is interviewing you.', $parsed[4]['stimulus_text']);
        $this->assertEquals($parsed[4]['group_key'], $parsed[6]['group_key']);

        // Task 4 tự động bắt 3 câu hỏi ? không đánh số và gắn chung stimulus bảng
        $this->assertEquals('Where is the conference being held?', $parsed[7]['content']);
        $this->assertEquals('CONFERENCE SCHEDULE | 09:00 AM | Keynote', $parsed[7]['stimulus_text']);
        $this->assertEquals($parsed[7]['group_key'], $parsed[9]['group_key']);
    }

    public function test_writing_docx_parses_consecutive_images_email_and_opinion_essay(): void
    {
        $wordService = app(WordQuestionImportService::class);
        $parsed = $wordService->parseLines([
            'Đề 5',
            'Part 1: Write a sentence based on a picture',
            'Directions: Write ONE sentence based on the picture.',
            'Question 1: breakfast, croissant',
            '[IMAGE_URL: storage/questions/imported/wrt/w1.png]',
            'Question 2: clear, off',
            '[IMAGE_URL: storage/questions/imported/wrt/w2.png]',
            '[IMAGE_URL: storage/questions/imported/wrt/w3.png]',
            '[IMAGE_URL: storage/questions/imported/wrt/w4.png]',
            'Question 3: umbrella, rainy',
            'Question 4: train, platform',
            'Question 5: laptop, desk',
            '[IMAGE_URL: storage/questions/imported/wrt/w5.png]',
            'Part 2: Written Requests',
            'Question 6:',
            'From: Sarah Lee',
            'Subject: Order Delay',
            'When will our order arrive?',
            'Directions: Respond to the email as a customer service representative.',
            'Answers',
            'Dear Ms. Lee, your order will arrive on Friday.',
            'Part 3: Question 8: Opinion Essay',
            'Do you agree or disagree that technology makes life easier? Support your opinion.',
            'Answers',
            'Technology has transformed modern life in many ways.',
        ], 'WRITING');

        $this->assertCount(7, $parsed);
        $this->assertEquals('storage/questions/imported/wrt/w1.png', $parsed[0]['image_url']);
        $this->assertEquals('storage/questions/imported/wrt/w2.png', $parsed[1]['image_url']);
        $this->assertEquals('storage/questions/imported/wrt/w3.png', $parsed[2]['image_url']);
        $this->assertEquals('storage/questions/imported/wrt/w4.png', $parsed[3]['image_url']);
        $this->assertEquals('storage/questions/imported/wrt/w5.png', $parsed[4]['image_url']);

        $this->assertEquals('WRITING_T2', $parsed[5]['part_code']);
        $this->assertStringContainsString('From: Sarah Lee', (string) $parsed[5]['stimulus_text']);
        $this->assertStringContainsString('Respond to the email', (string) $parsed[5]['content']);
        $this->assertStringContainsString('your order will arrive on Friday', (string) $parsed[5]['explanation']);

        $this->assertEquals('WRITING_T3', $parsed[6]['part_code']);
        $this->assertStringContainsString('technology makes life easier', (string) $parsed[6]['content']);
        $this->assertStringContainsString('transformed modern life', (string) $parsed[6]['explanation']);
    }

    public function test_question_type_auto_resolves_per_skill_and_assign_auto_activates_draft_or_returns_error_on_mismatch(): void
    {
        if (! Schema::hasTable('test_form_question')) {
            Schema::create('test_form_question', function (Blueprint $table): void {
                $table->unsignedBigInteger('test_form_id');
                $table->unsignedBigInteger('question_id');
                $table->integer('sequence_no');
                $table->decimal('point', 6, 2)->default(1);
                $table->primary(['test_form_id', 'question_id']);
            });
        }

        $admin = User::factory()->admin()->create();
        app(TestFormService::class)->ensureStandardSectionsExist();
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        // 1. Tạo câu Writing & Speaking -> tự động map đúng question_type_id (không phải Multiple Choice)
        $wrt1 = ExamSection::where('code', 'WRITING_T1')->firstOrFail();
        $spk1 = ExamSection::where('code', 'SPK_T1')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.questions.store'), [
            'section_id' => $wrt1->section_id,
            'content' => 'Write a sentence using coffee / morning.',
            'action_type' => 'draft',
        ])->assertRedirect(route('admin.questions.index'));

        $this->actingAs($admin)->post(route('admin.questions.store'), [
            'section_id' => $spk1->section_id,
            'content' => 'Read the museum announcement aloud.',
            'action_type' => 'draft',
        ])->assertRedirect(route('admin.questions.index'));

        $wrtQ = Question::with('questionType')->where('section_id', $wrt1->section_id)->firstOrFail();
        $spkQ = Question::with('questionType')->where('section_id', $spk1->section_id)->firstOrFail();

        $this->assertEquals('Written Response', $wrtQ->questionType->name);
        $this->assertEquals('Speaking Response', $spkQ->questionType->name);
        $this->assertEquals('DRAFT', $wrtQ->status);

        // 2. Tạo đề TOEIC Writing
        $wrtTest = app(TestFormService::class)->create([
            'title' => 'TOEIC Writing Sync Test',
            'exam_type' => TestForm::EXAM_TYPE_WRITING,
        ]);

        // Gắn câu Speaking vào đề Writing -> phải báo lỗi (session error), giữ nguyên URL bộ lọc
        $filterUrl = route('admin.questions.index', ['skill' => 'SPK', 'status' => 'DRAFT']);
        $mismatchRes = $this->actingAs($admin)->from($filterUrl)->post(route('admin.questions.bulk-action'), [
            'action' => 'assign',
            'test_id' => $wrtTest->test_form_id,
            'question_ids' => [$spkQ->question_id],
            'auto_activate_drafts' => '1',
        ]);
        $mismatchRes->assertRedirect($filterUrl);
        $mismatchRes->assertSessionHas('error');

        // Gắn câu Writing DRAFT vào đề Writing -> thành công & tự động duyệt sang ACTIVE
        $wrtFilterUrl = route('admin.questions.index', ['skill' => 'WRT']);
        $okRes = $this->actingAs($admin)->from($wrtFilterUrl)->post(route('admin.questions.bulk-action'), [
            'action' => 'assign',
            'test_id' => $wrtTest->test_form_id,
            'question_ids' => [$wrtQ->question_id],
            'auto_activate_drafts' => '1',
        ]);
        $okRes->assertRedirect($wrtFilterUrl);
        $okRes->assertSessionHas('success');
        $this->assertEquals('ACTIVE', $wrtQ->fresh()->status);
        $this->assertEquals(1, $wrtTest->questions()->count());
    }

    public function test_admin_can_search_questions_by_transcript(): void
    {
        $admin = User::factory()->admin()->create();
        $skill = Skill::create(['skill_id' => 1, 'code' => 'LC', 'skill_name' => 'Listening']);
        $section = ExamSection::create(['section_id' => 3, 'skill_id' => $skill->skill_id, 'code' => 'PART3', 'name' => 'Part 3', 'sequence_no' => 3]);
        QuestionType::create(['question_type_id' => 3, 'name' => 'Audio Listening', 'response_mode' => 'SINGLE_CHOICE']);

        $qSet = QuestionSet::create([
            'title' => 'Office Printer Conversation',
            'transcript' => 'W: Excuse me, the quarterly budget binder is missing from room 402.',
            'media_type' => 'AUDIO',
        ]);

        Question::create([
            'section_id' => $section->section_id,
            'question_type_id' => 3,
            'question_set_id' => $qSet->question_set_id,
            'content' => 'What are the speakers discussing?',
            'difficulty' => 'MEDIUM',
            'points' => 1.0,
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.questions.index', [
            'search' => 'quarterly budget binder',
        ]));

        $response->assertOk();
        $response->assertSee('What are the speakers discussing?');
        $response->assertDontSee('Bộ lọc nâng cao');
    }
}
