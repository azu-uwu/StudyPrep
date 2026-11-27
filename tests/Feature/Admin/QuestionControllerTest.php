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
use App\Services\DocxToStandardXmlService;
use App\Services\PdfImport\Parsers\PdfAnswerScriptParser;
use App\Services\TestFormService;
use App\Services\TextToSpeechService;
use App\Services\WordQuestionImportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
        $response->assertSee('Nhập câu hỏi hàng loạt');
        $response->assertSee('Tải file mẫu');
        $response->assertSee('Quay lại danh sách');
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

        $codes = $questions->map(fn ($q) => strtoupper((string) $q->section?->code))->all();
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
        $this->assertEquals('', $q->content);
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

    public function test_admin_can_preview_docx_staging_export_xml_and_commit_with_images_and_tts(): void
    {
        $admin = User::factory()->admin()->create();

        $lcSkill = Skill::create(['skill_id' => 1, 'code' => 'LC', 'skill_name' => 'Listening']);
        $rcSkill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        ExamSection::create(['section_id' => 1, 'skill_id' => $lcSkill->skill_id, 'code' => 'PART1', 'name' => 'Part 1', 'sequence_no' => 1]);
        ExamSection::create(['section_id' => 5, 'skill_id' => $rcSkill->skill_id, 'code' => 'PART5', 'name' => 'Part 5', 'sequence_no' => 5]);
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);
        QuestionType::create(['question_type_id' => 3, 'name' => 'Audio Listening', 'response_mode' => 'SINGLE_CHOICE']);

        // Commit staging is non-blocking (does not call TTS synchronously during commit)
        $this->mock(TextToSpeechService::class, function ($mock): void {
            $mock->shouldNotReceive('synthesize');
        });

        // Build a real .docx with an embedded PNG image + Part 1 + Part 5
        $docxPath = tempnam(sys_get_temp_dir(), 'studyprep_stage_').'.docx';
        $zip = new \ZipArchive;
        $zip->open($docxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
        $zip->addFromString('word/_rels/document.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rIdImg1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/image1.png"/></Relationships>');
        $zip->addFromString('word/media/image1.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));
        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><w:body>'
            .'<w:p><w:r><w:t>[Part 1]</w:t></w:r></w:p>'
            .'<w:p><w:r><w:drawing><a:blip r:embed="rIdImg1"/></w:drawing></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 1: Look at the picture.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(A) A man is typing. *(B) A woman is reading. (C) They are walking. (D) The desk is empty.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>[Part 5]</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 101: The director ------- the proposal yesterday.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(A) approve (B) approved* (C) approving (D) approval</w:t></w:r></w:p>'
            .'</w:body></w:document>');
        $zip->close();

        $uploadedFile = new UploadedFile($docxPath, 'staging_test.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);

        // Step 1: Preview staging
        $previewResp = $this->actingAs($admin)->post(route('admin.questions.import.preview'), [
            'import_file' => $uploadedFile,
            'import_scope' => 'AUTO',
            'publish_status' => 'live',
        ]);
        $previewResp->assertRedirect();
        $reviewUrl = (string) $previewResp->headers->get('Location');
        $this->assertStringContainsString('/admin/questions/import/review/', $reviewUrl);
        $batchId = basename($reviewUrl);

        // Step 2: Review page + XML download
        $reviewPage = $this->actingAs($admin)->get($reviewUrl);
        $reviewPage->assertOk();
        $reviewPage->assertViewIs('admin.questions.bulk-import-review');
        $reviewPage->assertSee('Duyệt &amp; Lưu vào Ngân hàng đề', false);

        $xmlResp = $this->actingAs($admin)->get(route('admin.questions.import.xml', ['batchId' => $batchId]));
        $xmlResp->assertOk();
        $xmlContent = $xmlResp->getContent();
        $this->assertStringContainsString('<studyprep_exam>', $xmlContent);
        $this->assertStringContainsString('part="PART1"', $xmlContent);
        $this->assertStringContainsString('correct="B"', $xmlContent);

        // Step 3: Commit staging
        $commitResp = $this->actingAs($admin)->post(route('admin.questions.import.commit', ['batchId' => $batchId]), [
            'auto_generate_tts' => '1',
            'publish_status' => 'ACTIVE',
        ]);
        $commitResp->assertRedirect(route('admin.questions.index'));
        $commitResp->assertSessionHas('success');

        $this->assertDatabaseHas('questions', [
            'content' => 'The director ------- the proposal yesterday.',
            'status' => 'ACTIVE',
        ]);
        $this->assertDatabaseHas('question_set', [
            'audio_url' => null,
        ]);

        // Cleanup promoted image created during test
        $promotedImg = QuestionSet::query()->whereNotNull('media_url')->value('media_url');
        if ($promotedImg) {
            @unlink(public_path($promotedImg));
        }
        @unlink($docxPath);
    }

    public function test_docx_staging_maps_mislabeled_reading_part1_2_3_to_part5_6_7_and_separates_invoice_stimulus(): void
    {
        $admin = User::factory()->admin()->create();
        $rcSkill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        ExamSection::create(['section_id' => 5, 'skill_id' => $rcSkill->skill_id, 'code' => 'PART5', 'name' => 'Part 5', 'sequence_no' => 5]);
        ExamSection::create(['section_id' => 6, 'skill_id' => $rcSkill->skill_id, 'code' => 'PART6', 'name' => 'Part 6', 'sequence_no' => 6]);
        ExamSection::create(['section_id' => 7, 'skill_id' => $rcSkill->skill_id, 'code' => 'PART7', 'name' => 'Part 7', 'sequence_no' => 7]);

        $docxPath = tempnam(sys_get_temp_dir(), 'studyprep_rc_').'.docx';
        $zip = new \ZipArchive;
        $zip->open($docxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');

        $paragraphs = [
            '0/100',
            'Audio 00:00 / 00:00',
            '▶',
            'Tốc độ: 1x',
            'PART 1',
            '101. All employees must submit their travel receipts ------- Friday.',
            '(A) until (B) by (C) since (D) while',
            'Answer: B',
            'Giải thích chi tiết ▲',
            'PART 2',
            'Questions 131-134 refer to the following memo.',
            'Our office will relocate to the third floor next month. [131] please pack your desks.',
            '131. (A) Therefore (B) However (C) Although (D) Unless',
            'Answer: A',
            'PART 3',
            'Questions 149-150 refer to the following invoice.',
            'Apex Supplies Invoice #4491 | Subtotal: $200.00 | Shipping: $25.00 | Total Due: $225.00',
            '150. What is the total amount due on the invoice?',
            'A. $200.00',
            'B. $25.00',
            'C. $225.00',
            'D. $250.00',
            'Answer: C',
        ];
        $bodyXml = '';
        foreach ($paragraphs as $p) {
            $bodyXml .= '<w:p><w:r><w:t xml:space="preserve">'.htmlspecialchars($p, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</w:t></w:r></w:p>';
        }
        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.$bodyXml.'</w:body></w:document>');
        $zip->close();

        $uploadedFile = new UploadedFile($docxPath, 'Actual_Test_R1.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);

        $previewResp = $this->actingAs($admin)->post(route('admin.questions.import.preview'), [
            'import_file' => $uploadedFile,
            'import_scope' => 'AUTO',
            'publish_status' => 'live',
        ]);
        $previewResp->assertRedirect();
        $batchId = basename((string) $previewResp->headers->get('Location'));

        $staging = app(DocxToStandardXmlService::class)->getStagingPayload($batchId);
        $this->assertIsArray($staging);
        $this->assertSame(0, $staging['summary']['transcript_count']);

        $parts = array_column($staging['sets'], 'part');
        $this->assertSame(['PART5', 'PART6', 'PART7'], $parts);

        $part7Set = $staging['sets'][2];
        $this->assertStringContainsString('Apex Supplies Invoice #4491', (string) $part7Set['stimulus']);
        $this->assertSame('What is the total amount due on the invoice?', $part7Set['questions'][0]['content']);
        $this->assertSame('C', $part7Set['questions'][0]['correct']);

        app(DocxToStandardXmlService::class)->forgetStagingPayload($batchId);
        @unlink($docxPath);
    }

    public function test_word_parser_cleans_pipes_and_infers_answers_from_real_reading_sample(): void
    {
        $parser = app(WordQuestionImportService::class);
        $rawText = <<<'TXT'
Part 1:
Q101-130
Question 1 : 101. Edison Delivery’s trucks leave the warehouse promptly ------- 6:00 A.M. each morning.
A.at
B.on
C.for
D.with
Giải thích chi tiết ▲
| At + mốc thời gian nhất định   | Xe tải của Edison Delivery rời khỏi nhà kho đúng lúc 6 giờ sáng mỗi ngày.   | Warehouse (n) kho hàng  Promptly (adv) đúng lúc, kịp thời   |
Part 2:
Q131-134
Questions 131–134 refer to the following letter.
To: Roger Wall
From: Guillermo Torres
This is in response to your ------- 131 e-mail notifying us that you did not receive your April shipment.
Question 31 : 131
A.constant
B.nearby
C.early
D.recent
Giải thích chi tiết ▲
| Chọn từ có nghĩa phù hợp với câu.  A. liên tục  D. gần đây (về thời gian)   | This is in response to your recent e-mail notifying us that you did not receive your April shipment.   |
Part 3:
Q149-150
Green’s Athletic Shoes
Receipt number: 5926
A receipt is required to make a return.
Question 50 : 150.What must a customer do to return an item?
A.Complete an online form
B.Bring the item back within six months
C.Show an original store receipt
D.Mail the item to the manufacturer
Giải thích chi tiết ▲
| Khách hàng phải làm gì để trả lại hàng? | Dòng cuối cùng tờ biên lai: A receipt is required to make a return = Show an original store receipt (xuất trình biên lai gốc của cửa hàng) |
TXT;

        $parsed = $parser->parseLines(explode("\n", $rawText), 'AUTO');
        $this->assertCount(3, $parsed);

        // 101 -> PART5, suy ra đáp án A ("at"), sạch dấu | và "Giải thích chi tiết ▲"
        $this->assertSame('PART5', $parsed[0]['part_code']);
        $this->assertSame('A', $parsed[0]['correct']);
        $this->assertStringNotContainsString('|', (string) $parsed[0]['explanation']);
        $this->assertStringNotContainsString('Giải thích chi tiết', (string) $parsed[0]['explanation']);

        // 131 -> PART6, suy ra đáp án D ("recent" từ "your recent e-mail")
        $this->assertSame('PART6', $parsed[1]['part_code']);
        $this->assertSame('D', $parsed[1]['correct']);
        $this->assertStringContainsString('April shipment', (string) $parsed[1]['stimulus_text']);

        // 150 -> PART7, gom Q149-150 stimulus, suy ra đáp án C ("= Show an original store receipt")
        $this->assertSame('PART7', $parsed[2]['part_code']);
        $this->assertSame('C', $parsed[2]['correct']);
        $this->assertStringContainsString('Receipt number: 5926', (string) $parsed[2]['stimulus_text']);
    }

    public function test_part6_without_individual_stem_is_100_percent_valid_in_review_and_commits_to_database(): void
    {
        $admin = User::factory()->admin()->create();
        $rcSkill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        $part6Section = ExamSection::create(['section_id' => 6, 'skill_id' => $rcSkill->skill_id, 'code' => 'PART6', 'name' => 'Part 6', 'sequence_no' => 6]);
        QuestionType::create(['question_type_id' => 1, 'name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $docxPath = tempnam(sys_get_temp_dir(), 'studyprep_p6_').'.docx';
        $zip = new \ZipArchive;
        $zip->open($docxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');

        $paragraphs = [
            'Part 2:',
            'Q131-134',
            'Questions 131-134 refer to the following letter.',
            'This is in response to your ------- 131 e-mail notifying us about the April shipment. Rest assured the error will not ------- 132 again.',
            'Question 31 : 131',
            'A.constant',
            'B.nearby',
            'C.early',
            'D.recent',
            'Giải thích chi tiết ▲',
            '| This is in response to your recent e-mail notifying us about the April shipment. |',
            'Question 32 : 132',
            'A.combine',
            'B.revise',
            'C.affect',
            'D.occur',
            'Giải thích chi tiết ▲',
            '| Rest assured the error will not occur again. |',
        ];
        $bodyXml = '';
        foreach ($paragraphs as $p) {
            $bodyXml .= '<w:p><w:r><w:t xml:space="preserve">'.htmlspecialchars($p, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</w:t></w:r></w:p>';
        }
        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.$bodyXml.'</w:body></w:document>');
        $zip->close();

        $uploadedFile = new UploadedFile($docxPath, 'Reading_Part6.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);

        $previewResp = $this->actingAs($admin)->post(route('admin.questions.import.preview'), [
            'import_file' => $uploadedFile,
            'import_scope' => 'AUTO',
            'publish_status' => 'live',
        ]);
        $previewResp->assertRedirect();
        $batchId = basename((string) $previewResp->headers->get('Location'));

        $staging = app(DocxToStandardXmlService::class)->getStagingPayload($batchId);
        $this->assertIsArray($staging);
        $this->assertSame(2, $staging['summary']['total_questions']);
        $this->assertSame(2, $staging['summary']['valid_questions']);
        $this->assertSame(0, $staging['summary']['error_questions']);
        $this->assertSame('Blank 131 (Complete the text)', $staging['sets'][0]['questions'][0]['content']);
        $this->assertSame('Blank 132 (Complete the text)', $staging['sets'][0]['questions'][1]['content']);

        // Review page không hiển thị cảnh báo lỗi trống nội dung
        $reviewPage = $this->actingAs($admin)->get(route('admin.questions.import.review', ['batchId' => $batchId]));
        $reviewPage->assertOk();
        $reviewPage->assertDontSee('Nội dung câu hỏi đang trống');

        // Commit lưu vào Ngân hàng câu hỏi
        $commitResp = $this->actingAs($admin)->post(route('admin.questions.import.commit', ['batchId' => $batchId]), [
            'publish_status' => 'ACTIVE',
        ]);
        $commitResp->assertRedirect(route('admin.questions.index'));
        $commitResp->assertSessionHas('success');

        $this->assertDatabaseHas('questions', [
            'section_id' => $part6Section->section_id,
            'content' => 'Blank 131 (Complete the text)',
            'status' => 'ACTIVE',
        ]);
        $this->assertDatabaseHas('questions', [
            'section_id' => $part6Section->section_id,
            'content' => 'Blank 132 (Complete the text)',
            'status' => 'ACTIVE',
        ]);

        @unlink($docxPath);
    }

    public function test_speaking_docx_staging_parses_all_11_questions_without_duplicate_seq_3_and_includes_3_spk_t4_questions(): void
    {
        $admin = User::factory()->admin()->create();
        $spkSkill = Skill::create(['skill_id' => 3, 'code' => 'SPK', 'skill_name' => 'Speaking']);
        ExamSection::create(['section_id' => 11, 'skill_id' => $spkSkill->skill_id, 'code' => 'SPK_T1', 'name' => 'Read a text aloud', 'sequence_no' => 1]);
        ExamSection::create(['section_id' => 12, 'skill_id' => $spkSkill->skill_id, 'code' => 'SPK_T2', 'name' => 'Describe a picture', 'sequence_no' => 2]);
        ExamSection::create(['section_id' => 13, 'skill_id' => $spkSkill->skill_id, 'code' => 'SPK_T3', 'name' => 'Respond to questions', 'sequence_no' => 3]);
        ExamSection::create(['section_id' => 14, 'skill_id' => $spkSkill->skill_id, 'code' => 'SPK_T4', 'name' => 'Respond using information', 'sequence_no' => 4]);
        ExamSection::create(['section_id' => 15, 'skill_id' => $spkSkill->skill_id, 'code' => 'SPK_T5', 'name' => 'Express an opinion', 'sequence_no' => 5]);

        $png1 = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $png2 = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAEklEQVR42mNk+M9QzwAEjDAGACCKAoFF73uPAAAAAElFTkSuQmCC');

        $docxPath = tempnam(sys_get_temp_dir(), 'studyprep_spk_').'.docx';
        $zip = new \ZipArchive;
        $zip->open($docxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
        $zip->addFromString('word/_rels/document.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rIdImg1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/image1.png"/>'
            .'<Relationship Id="rIdImg2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/image2.png"/>'
            .'</Relationships>');
        $zip->addFromString('word/media/image1.png', $png1);
        $zip->addFromString('word/media/image2.png', $png2);

        $bodyXml = ''
            .'<w:p><w:r><w:t>1. Dạng câu hỏi Read a text aloud (câu 1, 2)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Câu 1: Welcome to our annual technology conference in Seattle.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Câu 2: Please turn off all mobile devices before the keynote begins.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>2. Dạng câu hỏi Describe a picture (câu 3, 4)</w:t></w:r></w:p>'
            .'<w:tbl>'
            .'<w:tr><w:tc><w:p><w:r><w:t>Câu 3</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Câu 4</w:t></w:r></w:p></w:tc></w:tr>'
            .'<w:tr><w:tc><w:p><w:r><w:drawing><a:blip r:embed="rIdImg1"/></w:drawing></w:r></w:p></w:tc><w:tc><w:p><w:r><w:drawing><a:blip r:embed="rIdImg2"/></w:drawing></w:r></w:p></w:tc></w:tr>'
            .'</w:tbl>'
            .'<w:p><w:r><w:t>3. Dạng câu hỏi Respond to questions (câu 5, 6, 7)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Hướng dẫn: Bạn sẽ trả lời 3 câu hỏi về thói quen mua sắm.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Imagine that a local magazine is conducting a survey on shopping habits.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Câu 5: How often do you shop for clothes online?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Câu 6: What factors are most important to you when choosing a store?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Câu 7: Would you recommend online shopping to your friends? Why or why not?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>4. Dạng câu hỏi Respond to questions using information (câu 8, 9, 10)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Hướng dẫn: Bạn sẽ có 45 giây đọc thông tin trước khi trả lời 3 câu hỏi.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Job Seeking Seminar - June 15</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>9:00 AM - Resume Writing Workshop with Bruce Geller</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>What day is the seminar on and what time does it start?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>I think I saw that Bruce Geller is leading the first workshop, right?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>I have an important appointment at 9:30 AM. Can you tell me what workshops are in the afternoon?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>5. Dạng câu hỏi Express an opinion (câu 11)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Câu 11: Do you agree or disagree that employees should work from home at least two days a week?</w:t></w:r></w:p>';

        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><w:body>'.$bodyXml.'</w:body></w:document>');
        $zip->close();

        $uploadedFile = new UploadedFile($docxPath, 'S.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);

        $previewResp = $this->actingAs($admin)->post(route('admin.questions.import.preview'), [
            'import_file' => $uploadedFile,
            'import_scope' => 'AUTO',
            'publish_status' => 'live',
        ]);
        $previewResp->assertRedirect();
        $batchId = basename((string) $previewResp->headers->get('Location'));

        $staging = app(DocxToStandardXmlService::class)->getStagingPayload($batchId);
        $this->assertIsArray($staging);
        $this->assertSame(11, $staging['summary']['total_questions']);
        $this->assertSame(11, $staging['summary']['valid_questions']);
        $this->assertSame(0, $staging['summary']['error_questions']);

        $allSeqs = [];
        $spkT2Media = [];
        $spkT4Set = null;
        foreach ($staging['sets'] as $set) {
            foreach ($set['questions'] as $q) {
                $allSeqs[] = (int) $q['seq'];
                if ($set['part'] === 'SPK_T2') {
                    $spkT2Media[(int) $q['seq']] = $q['media_url'] ?: $set['media_url'];
                }
            }
            if ($set['part'] === 'SPK_T4') {
                $spkT4Set = $set;
            }
        }

        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11], $allSeqs);
        $this->assertArrayHasKey(3, $spkT2Media);
        $this->assertArrayHasKey(4, $spkT2Media);
        $this->assertNotEmpty($spkT2Media[3]);
        $this->assertNotEmpty($spkT2Media[4]);
        $this->assertNotSame($spkT2Media[3], $spkT2Media[4]);

        $this->assertNotNull($spkT4Set);
        $this->assertCount(3, $spkT4Set['questions']);
        $this->assertStringContainsString('Job Seeking Seminar', (string) ($spkT4Set['stimulus'] ?? ''));

        $reviewPage = $this->actingAs($admin)->get(route('admin.questions.import.review', ['batchId' => $batchId]));
        $reviewPage->assertOk();
        $reviewPage->assertSee('Xem trước');
        $reviewPage->assertSee('modal-preview-question');

        @unlink($docxPath);
    }

    public function test_combined_speaking_and_writing_docx_parses_all_19_questions_with_5_writing_t1_images_and_writing_t3(): void
    {
        $admin = User::factory()->admin()->create();
        $spkSkill = Skill::create(['skill_id' => 3, 'code' => 'SPK', 'skill_name' => 'Speaking']);
        $wriSkill = Skill::create(['skill_id' => 4, 'code' => 'WRI', 'skill_name' => 'Writing']);

        ExamSection::create(['section_id' => 11, 'skill_id' => $spkSkill->skill_id, 'code' => 'SPK_T1', 'name' => 'Read a text aloud', 'sequence_no' => 1]);
        ExamSection::create(['section_id' => 12, 'skill_id' => $spkSkill->skill_id, 'code' => 'SPK_T2', 'name' => 'Describe a picture', 'sequence_no' => 2]);
        ExamSection::create(['section_id' => 13, 'skill_id' => $spkSkill->skill_id, 'code' => 'SPK_T3', 'name' => 'Respond to questions', 'sequence_no' => 3]);
        ExamSection::create(['section_id' => 14, 'skill_id' => $spkSkill->skill_id, 'code' => 'SPK_T4', 'name' => 'Respond using information', 'sequence_no' => 4]);
        ExamSection::create(['section_id' => 15, 'skill_id' => $spkSkill->skill_id, 'code' => 'SPK_T5', 'name' => 'Express an opinion', 'sequence_no' => 5]);
        ExamSection::create(['section_id' => 16, 'skill_id' => $wriSkill->skill_id, 'code' => 'WRITING_T1', 'name' => 'Write a sentence', 'sequence_no' => 6]);
        ExamSection::create(['section_id' => 17, 'skill_id' => $wriSkill->skill_id, 'code' => 'WRITING_T2', 'name' => 'Respond to a written request', 'sequence_no' => 7]);
        ExamSection::create(['section_id' => 18, 'skill_id' => $wriSkill->skill_id, 'code' => 'WRITING_T3', 'name' => 'Opinion essay', 'sequence_no' => 8]);

        $png1 = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $png2 = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAEklEQVR42mNk+M9QzwAEjDAGACCKAoFF73uPAAAAAElFTkSuQmCC');

        $docxPath = tempnam(sys_get_temp_dir(), 'studyprep_sw_').'.docx';
        $zip = new \ZipArchive;
        $zip->open($docxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');

        $rels = '';
        for ($i = 1; $i <= 7; $i++) {
            $rels .= '<Relationship Id="rIdImg'.$i.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/image'.$i.'.png"/>';
            $zip->addFromString("word/media/image{$i}.png", $i % 2 === 0 ? $png2 : $png1);
        }
        $zip->addFromString('word/_rels/document.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.$rels.'</Relationships>');

        $bodyXml = ''
            // SPEAKING (11 câu)
            .'<w:p><w:r><w:t>SPEAKING</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>1. Read a text aloud (câu 1, 2)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 1: Welcome to the City Art Museum audio tour.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 2: Please check your coats and bags at the front desk.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>2. Describe a picture (câu 3, 4)</w:t></w:r></w:p>'
            .'<w:tbl>'
            .'<w:tr><w:tc><w:p><w:r><w:t>Câu 3</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Câu 4</w:t></w:r></w:p></w:tc></w:tr>'
            .'<w:tr><w:tc><w:p><w:r><w:drawing><a:blip r:embed="rIdImg1"/></w:drawing></w:r></w:p></w:tc><w:tc><w:p><w:r><w:drawing><a:blip r:embed="rIdImg2"/></w:drawing></w:r></w:p></w:tc></w:tr>'
            .'</w:tbl>'
            .'<w:p><w:r><w:t>3. Respond to questions (câu 5, 6, 7)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 5: Where do you usually buy groceries?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 6: How do you decide which products to purchase?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 7: Do you prefer shopping alone or with friends?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>4. Respond to questions using information (câu 8, 9, 10)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Hướng dẫn: Đọc thông tin hội thảo trong 45 giây.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Annual Marketing Workshop - October 20</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>10:00 AM - Social Media Strategy by Sarah Lin</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>When is the marketing workshop taking place?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Who is leading the Social Media Strategy session?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Please tell me the exact time the morning session begins.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>5. Express an opinion (câu 11)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 11: Should companies offer flexible working hours to all employees?</w:t></w:r></w:p>'
            // WRITING (8 câu)
            .'<w:p><w:r><w:t>WRITING</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Writing Task 1: Write a sentence based on a picture</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 1: man / laptop</w:t></w:r></w:p>'
            .'<w:p><w:r><w:drawing><a:blip r:embed="rIdImg3"/></w:drawing></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 2: woman / coffee</w:t></w:r></w:p>'
            .'<w:p><w:r><w:drawing><a:blip r:embed="rIdImg4"/></w:drawing></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 3: fall / block</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 4: cashier / items</w:t></w:r></w:p>'
            .'<w:p><w:r><w:drawing><a:blip r:embed="rIdImg5"/></w:drawing></w:r></w:p>'
            .'<w:p><w:r><w:drawing><a:blip r:embed="rIdImg6"/></w:drawing></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 5: workers / meeting</w:t></w:r></w:p>'
            .'<w:p><w:r><w:drawing><a:blip r:embed="rIdImg7"/></w:drawing></w:r></w:p>'
            .'<w:p><w:r><w:t>Writing Task 2: Respond to a written request</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 6: Respond to the e-mail as if you are a customer service manager.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>From: Alice Brown</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Subject: Order #101 Delay</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Dear Support, my order #101 has not arrived yet.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 7: Respond to the e-mail as if you are an HR coordinator.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>From: David Chen</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Subject: Interview Schedule</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Hello, could we reschedule my interview to Friday?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 8: Opinion Essay</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Directions: In this part of the test, you will write an essay in response to a question.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Some people prefer to work for a large company, while others prefer to work for a small business. Which do you prefer and why?</w:t></w:r></w:p>';

        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><w:body>'.$bodyXml.'</w:body></w:document>');
        $zip->close();

        $uploadedFile = new UploadedFile($docxPath, 'S+W.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);

        $previewResp = $this->actingAs($admin)->post(route('admin.questions.import.preview'), [
            'import_file' => $uploadedFile,
            'import_scope' => 'AUTO',
            'publish_status' => 'live',
        ]);
        $previewResp->assertRedirect();
        $batchId = basename((string) $previewResp->headers->get('Location'));

        $staging = app(DocxToStandardXmlService::class)->getStagingPayload($batchId);
        $this->assertIsArray($staging);
        $this->assertSame(19, $staging['summary']['total_questions']);
        $this->assertSame(19, $staging['summary']['valid_questions']);
        $this->assertSame(0, $staging['summary']['error_questions']);

        $partCounts = [];
        $writingT1Images = [];
        $writingT2Stimuli = [];
        $writingT3Contents = [];

        foreach ($staging['sets'] as $set) {
            $part = $set['part'];
            $partCounts[$part] = ($partCounts[$part] ?? 0) + count($set['questions']);
            if ($part === 'WRITING_T1') {
                foreach ($set['questions'] as $q) {
                    $writingT1Images[] = $q['media_url'] ?: $set['media_url'];
                }
            } elseif ($part === 'WRITING_T2') {
                foreach ($set['questions'] as $q) {
                    $writingT2Stimuli[(int) $q['seq']] = (string) ($set['stimulus'] ?? '');
                }
            } elseif ($part === 'WRITING_T3') {
                foreach ($set['questions'] as $q) {
                    $writingT3Contents[] = $q['content'];
                }
            }
        }

        $this->assertSame(3, $partCounts['SPK_T4'] ?? 0);
        $this->assertSame(5, $partCounts['WRITING_T1'] ?? 0);
        $this->assertSame(2, $partCounts['WRITING_T2'] ?? 0);
        $this->assertSame(1, $partCounts['WRITING_T3'] ?? 0);

        $this->assertCount(5, array_filter($writingT1Images));
        $this->assertSame(5, count(array_unique($writingT1Images)));

        $this->assertStringContainsString('Interview Schedule', $writingT2Stimuli[7] ?? '');
        $this->assertStringNotContainsString('Order #101 Delay', $writingT2Stimuli[7] ?? '');

        $this->assertNotEmpty($writingT3Contents);
        $this->assertStringContainsString('large company', $writingT3Contents[0]);

        @unlink($docxPath);
    }

    public function test_full_lr_and_full_sw_import_scopes_render_in_ui_and_parse_accurately(): void
    {
        $admin = User::factory()->admin()->create();
        $bulkPage = $this->actingAs($admin)->get(route('admin.questions.bulk-import'));
        $bulkPage->assertOk();
        $bulkPage->assertSee('FULL_LR');
        $bulkPage->assertSee('FULL_SW');

        $wordService = app(WordQuestionImportService::class);

        // 1. Kiểm thử chế độ FULL_LR: Listening (1..100) tự chuyển sang Reading (101..200) và khóa không nhận nhầm Speaking/Writing
        $lrParsed = $wordService->parseLines([
            'Question 1: Look at the picture.',
            '(A) A man is walking. *(B) A woman is reading. (C) They are sitting. (D) The room is empty.',
            'Question 7: Where is the meeting room?',
            '(A) On the second floor. (B) At 2 P.M. (C) Yes, I did.',
            'Question 101: The manager ------- the report yesterday.',
            '(A) submit *(B) submitted (C) submitting (D) submission',
            'Question 147: What is the notice mainly about?',
            '(A) Office relocation *(B) New parking rules (C) Holiday hours (D) Staff training',
        ], 'FULL_LR');

        $this->assertCount(4, $lrParsed);
        $this->assertSame('PART1', $lrParsed[0]['part_code']);
        $this->assertSame('PART2', $lrParsed[1]['part_code']);
        $this->assertSame('PART5', $lrParsed[2]['part_code']);
        $this->assertSame('PART7', $lrParsed[3]['part_code']);

        // 2. Kiểm thử chế độ FULL_SW: Speaking (T1..T5) tự chuyển sang Writing khi gặp "Questions 1–5: Write a sentence based on a picture"
        $swParsed = $wordService->parseLines([
            'Question 1: Welcome to the city library audio guide.',
            'Question 11: Do you agree or disagree that students should wear uniforms?',
            'Questions 1–5: Write a sentence based on a picture',
            'Question 1: woman / laptop',
            'Question 6: Respond to the e-mail as if you are a travel agent.',
            'From: John Doe',
            'Subject: Flight Change',
            'Question 8: Opinion Essay',
            'Directions: Write an essay in response to the following question.',
            'Some people prefer living in big cities while others prefer small towns. Which do you prefer?',
        ], 'FULL_SW');

        $this->assertCount(5, $swParsed);
        $this->assertSame('SPK_T1', $swParsed[0]['part_code']);
        $this->assertSame('SPK_T5', $swParsed[1]['part_code']);
        $this->assertSame('WRITING_T1', $swParsed[2]['part_code']);
        $this->assertSame('WRITING_T2', $swParsed[3]['part_code']);
        $this->assertSame('WRITING_T3', $swParsed[4]['part_code']);
    }

    public function test_question_validation_boundaries_media_limits_type_mapping_multidimensional_search_and_bom_csv(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $lcSkill = Skill::create(['skill_id' => 1, 'code' => 'LC', 'skill_name' => 'Listening']);
        $rcSkill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        $spkSkill = Skill::create(['skill_id' => 3, 'code' => 'SPK', 'skill_name' => 'Speaking']);

        $part2Sec = ExamSection::create(['section_id' => 2, 'skill_id' => $lcSkill->skill_id, 'code' => 'PART2', 'name' => 'Question-Response', 'sequence_no' => 2]);
        $part5Sec = ExamSection::create(['section_id' => 5, 'skill_id' => $rcSkill->skill_id, 'code' => 'PART5', 'name' => 'Incomplete Sentences', 'sequence_no' => 5]);
        $spkSec = ExamSection::create(['section_id' => 11, 'skill_id' => $spkSkill->skill_id, 'code' => 'SPK_T1', 'name' => 'Read Aloud', 'sequence_no' => 11]);

        // 1. Kiểm tra tự động ánh xạ question_type_id theo Part (resolveQuestionTypeIdForPart)
        $part5TypeId = Question::resolveQuestionTypeIdForPart('PART5');
        $spkTypeId = Question::resolveQuestionTypeIdForPart('SPK_T1');
        $wrtTypeId = Question::resolveQuestionTypeIdForPart('WRITING_T3');
        $this->assertGreaterThan(0, $part5TypeId);
        $this->assertGreaterThan(0, $spkTypeId);
        $this->assertNotSame($part5TypeId, $spkTypeId);
        $this->assertNotSame($part5TypeId, $wrtTypeId);

        // 2. Validation biên: thiếu nội dung, thiếu đáp án A/B/C/D ở Part 5
        $missingFieldsResp = $this->actingAs($admin)->post(route('admin.questions.store'), [
            'section_id' => $part5Sec->section_id,
            'content' => '',
        ]);
        $missingFieldsResp->assertSessionHasErrors(['content', 'opt_a', 'opt_b', 'opt_c', 'opt_d']);

        // Validation biên: upload ảnh > 5MB (5200 KB), audio > 15MB (15500 KB) và sai MIME
        $oversizedImage = UploadedFile::fake()->image('huge.png')->size(5200);
        $oversizedAudio = UploadedFile::fake()->create('huge.mp3', 15500, 'audio/mpeg');
        $oversizedResp = $this->actingAs($admin)->post(route('admin.questions.store'), [
            'section_id' => $spkSec->section_id,
            'content' => 'Read the announcement aloud.',
            'image_file' => $oversizedImage,
            'audio_file' => $oversizedAudio,
        ]);
        $oversizedResp->assertSessionHasErrors(['image_file', 'audio_file']);

        $wrongMimeResp = $this->actingAs($admin)->post(route('admin.questions.store'), [
            'section_id' => $spkSec->section_id,
            'content' => 'Read the announcement aloud.',
            'image_file' => UploadedFile::fake()->create('malware.pdf', 100, 'application/pdf'),
            'audio_file' => UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload'),
        ]);
        $wrongMimeResp->assertSessionHasErrors(['image_file', 'audio_file']);

        // 3. Tìm kiếm đa chiều (#Q-ID, content, explanation, question_set.transcript) & giữ query string khi phân trang
        $qSet = QuestionSet::query()->create([
            'title' => 'Part 2 Audio Set',
            'transcript' => 'UNIQUE_TRANSCRIPT_TOKEN_98765',
            'media_type' => 'AUDIO',
        ]);
        $targetQ = Question::query()->create([
            'section_id' => $part2Sec->section_id,
            'question_set_id' => $qSet->question_set_id,
            'question_type_id' => $part5TypeId,
            'content' => 'Where did you park the delivery van?',
            'explanation' => 'UNIQUE_EXPLANATION_TOKEN_54321',
            'difficulty' => 'HARD',
            'status' => 'ACTIVE',
            'created_at' => now(),
        ]);

        // Tìm theo #Q-ID
        $byIdResp = $this->actingAs($admin)->get(route('admin.questions.index', ['search' => '#Q-'.$targetQ->question_id]));
        $byIdResp->assertOk()->assertSee('Where did you park the delivery van?');

        // Tìm theo explanation
        $byExpResp = $this->actingAs($admin)->get(route('admin.questions.index', ['search' => 'UNIQUE_EXPLANATION_TOKEN_54321']));
        $byExpResp->assertOk()->assertSee('Where did you park the delivery van?');

        // Tìm xuyên bảng question_set.transcript + kết hợp 5 bộ lọc + phân trang giữ query string
        for ($i = 1; $i <= 11; $i++) {
            Question::query()->create([
                'section_id' => $part2Sec->section_id,
                'question_set_id' => $qSet->question_set_id,
                'question_type_id' => $part5TypeId,
                'content' => "Filtered Pagination Question {$i}",
                'difficulty' => 'HARD',
                'status' => 'ACTIVE',
                'created_at' => now(),
            ]);
        }

        $comboResp = $this->actingAs($admin)->get(route('admin.questions.index', [
            'search' => 'UNIQUE_TRANSCRIPT_TOKEN_98765',
            'skill' => 'LC',
            'part' => 'PART2',
            'difficulty' => 'HARD',
            'status' => 'ACTIVE',
        ]));
        $comboResp->assertOk();
        $this->assertStringContainsString('search=UNIQUE_TRANSCRIPT_TOKEN_98765', $comboResp->getContent());
        $this->assertStringContainsString('difficulty=HARD', $comboResp->getContent());

        // 4. Export CSV bắt đầu bằng UTF-8 BOM (\xEF\xBB\xBF)
        $csvResp = $this->actingAs($admin)->get(route('admin.questions.export'));
        $csvResp->assertOk();
        $csvContent = $csvResp->streamedContent();
        $this->assertTrue(str_starts_with($csvContent, "\xEF\xBB\xBF"));
    }

    public function test_questions_index_shows_separate_listening_reading_counts_and_filters_by_test_form(): void
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
        $lcSkill = Skill::create(['skill_id' => 1, 'code' => 'LC', 'skill_name' => 'Listening']);
        $rcSkill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        $part1 = ExamSection::create(['section_id' => 1, 'skill_id' => $lcSkill->skill_id, 'code' => 'PART1', 'name' => 'Part 1', 'sequence_no' => 1]);
        $part5 = ExamSection::create(['section_id' => 5, 'skill_id' => $rcSkill->skill_id, 'code' => 'PART5', 'name' => 'Part 5', 'sequence_no' => 5]);
        $qType = QuestionType::create(['question_type_id' => 1, 'name' => 'MCQ', 'response_mode' => 'SINGLE_CHOICE']);

        $bp = TestBlueprint::create(['blueprint_id' => 1, 'name' => 'TOEIC LR', 'exam_type' => 'TOEIC_LR', 'status' => 'ACTIVE']);
        $testA = TestForm::create(['blueprint_id' => $bp->blueprint_id, 'title' => 'ETS Filter Test A']);

        $qL1 = Question::create(['section_id' => $part1->section_id, 'question_type_id' => $qType->question_type_id, 'content' => 'Listening Q1', 'difficulty' => 'EASY', 'status' => 'ACTIVE', 'created_at' => now()]);
        $qL2 = Question::create(['section_id' => $part1->section_id, 'question_type_id' => $qType->question_type_id, 'content' => 'Listening Q2', 'difficulty' => 'EASY', 'status' => 'ACTIVE', 'created_at' => now()]);
        $qR1 = Question::create(['section_id' => $part5->section_id, 'question_type_id' => $qType->question_type_id, 'content' => 'Reading Q1 Attached', 'difficulty' => 'MEDIUM', 'status' => 'ACTIVE', 'created_at' => now()]);

        DB::table('test_form_question')->insert([
            'test_form_id' => $testA->test_form_id,
            'question_id' => $qR1->question_id,
            'sequence_no' => 1,
            'point' => 1,
        ]);

        $indexResp = $this->actingAs($admin)->get(route('admin.questions.index'));
        $indexResp->assertOk();
        $indexResp->assertSee('câu L&amp;R', false);
        $indexResp->assertSee('câu S&amp;W', false);
        $indexResp->assertSee('Listening: 2');
        $indexResp->assertSee('Reading: 1');
        $indexResp->assertSee('#0369a1', false);
        $indexResp->assertSee('#047857', false);
        $indexResp->assertDontSee('Đã chuẩn hóa thang điểm 990');
        $indexResp->assertDontSee('qbank-breadcrumb-sub', false);
        $indexResp->assertSee('Thao tác khác');
        $indexResp->assertSee('Thêm câu hỏi mới');

        $filteredResp = $this->actingAs($admin)->get(route('admin.questions.index', ['tag' => $testA->test_form_id]));
        $filteredResp->assertOk();
        $filteredResp->assertSee('Reading Q1 Attached');
        $filteredResp->assertDontSee('Listening Q1');
        $filteredResp->assertDontSee('Listening Q2');
    }

    public function test_part1_docx_with_repeated_question_header_after_audio_and_explanation_merges_into_single_valid_set(): void
    {
        $admin = User::factory()->admin()->create();
        $lcSkill = Skill::create(['skill_id' => 1, 'code' => 'LC', 'skill_name' => 'Listening']);
        ExamSection::create(['section_id' => 1, 'skill_id' => $lcSkill->skill_id, 'code' => 'PART1', 'name' => 'Photographs', 'sequence_no' => 1]);

        $png1 = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $docxPath = tempnam(sys_get_temp_dir(), 'studyprep_p1_merge_').'.docx';
        $zip = new \ZipArchive;
        $zip->open($docxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
        $zip->addFromString('word/_rels/document.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rIdImg1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/image1.png"/></Relationships>');
        $zip->addFromString('word/media/image1.png', $png1);

        $bodyXml = ''
            .'<w:p><w:r><w:t>[Part 1]</w:t></w:r></w:p>'
            .'<w:p><w:r><w:drawing><a:blip r:embed="rIdImg1"/></w:drawing></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 1:</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Audio</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>00:00 / 00:00</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 1 :</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Giải thích chi tiết ▲</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(A) He is typing on a keyboard. (Anh ấy đang gõ bàn phím.)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(B) She is holding a folder. (Cô ấy đang cầm một tập hồ sơ.)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(C) They are shaking hands. (Họ đang bắt tay nhau.)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(D) The desk is empty. (Cái bàn đang trống.)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Đáp án: B</w:t></w:r></w:p>';

        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><w:body>'.$bodyXml.'</w:body></w:document>');
        $zip->close();

        $uploadedFile = new UploadedFile($docxPath, 'Part1_Merge.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);

        $previewResp = $this->actingAs($admin)->post(route('admin.questions.import.preview'), [
            'import_file' => $uploadedFile,
            'import_scope' => 'LISTENING',
            'publish_status' => 'live',
        ]);
        $previewResp->assertRedirect();
        $batchId = basename((string) $previewResp->headers->get('Location'));

        $staging = app(DocxToStandardXmlService::class)->getStagingPayload($batchId);
        $this->assertIsArray($staging);
        $this->assertCount(1, $staging['sets']);
        $this->assertSame(1, $staging['summary']['total_questions']);
        $this->assertSame(1, $staging['summary']['valid_questions']);
        $this->assertSame(0, $staging['summary']['error_questions']);

        $set = $staging['sets'][0];
        $this->assertSame('PART1', $set['part']);
        $this->assertNotEmpty($set['media_url']);
        $this->assertStringContainsString('He is typing on a keyboard.', (string) ($set['transcript'] ?? ''));
        $this->assertStringContainsString('She is holding a folder.', (string) ($set['transcript'] ?? ''));
        $this->assertCount(1, $set['questions']);
        $this->assertSame('B', $set['questions'][0]['correct']);
        $this->assertSame('She is holding a folder.', $set['questions'][0]['options']['B']);

        @unlink($docxPath);
    }

    public function test_admin_can_commit_full_100_reading_questions_via_payload_json_without_truncation(): void
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

        $rcSkill = Skill::firstOrCreate(['code' => 'RC'], ['skill_id' => 2, 'skill_name' => 'Reading']);
        $part5 = ExamSection::firstOrCreate(['code' => 'PART5'], ['section_id' => 5, 'skill_id' => $rcSkill->skill_id, 'name' => 'Part 5', 'sequence_no' => 5]);
        $part6 = ExamSection::firstOrCreate(['code' => 'PART6'], ['section_id' => 6, 'skill_id' => $rcSkill->skill_id, 'name' => 'Part 6', 'sequence_no' => 6]);
        $part7 = ExamSection::firstOrCreate(['code' => 'PART7'], ['section_id' => 7, 'skill_id' => $rcSkill->skill_id, 'name' => 'Part 7', 'sequence_no' => 7]);

        QuestionType::firstOrCreate(['question_type_id' => 1], ['name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $blueprint = TestBlueprint::create([
            'name' => 'TOEIC Reading 100',
            'exam_type' => TestForm::EXAM_TYPE_READING,
            'total_time' => 75,
            'status' => 'ACTIVE',
        ]);
        $testForm = TestForm::create([
            'blueprint_id' => $blueprint->blueprint_id,
            'title' => 'ETS Reading Full 100 Test',
            'exam_type' => TestForm::EXAM_TYPE_READING,
            'status' => 'DRAFT',
        ]);

        $setsPayload = [];

        // 30 PART5 questions (101..130)
        for ($i = 101; $i <= 130; $i++) {
            $setsPayload[] = [
                'part' => 'PART5',
                'title' => "Part 5 Q{$i}",
                'stimulus' => '',
                'transcript' => '',
                'media_url' => '',
                'audio_url' => '',
                'questions' => [[
                    'seq' => $i,
                    'difficulty' => 'MEDIUM',
                    'media_url' => '',
                    'content' => "Question {$i}: The manager ------- the report.",
                    'explanation' => "Explanation for {$i}",
                    'correct' => 'B',
                    'options' => [
                        'A' => 'review',
                        'B' => 'reviewed',
                        'C' => 'reviewing',
                        'D' => 'reviewer',
                    ],
                ]],
            ];
        }

        // 4 PART6 passages x 4 questions = 16 questions (131..146)
        $seq = 131;
        for ($p = 1; $p <= 4; $p++) {
            $qs = [];
            for ($k = 0; $k < 4; $k++) {
                $qs[] = [
                    'seq' => $seq,
                    'difficulty' => 'MEDIUM',
                    'media_url' => '',
                    'content' => "Blank {$seq}",
                    'explanation' => '',
                    'correct' => 'C',
                    'options' => [
                        'A' => 'Option A',
                        'B' => 'Option B',
                        'C' => 'Option C',
                        'D' => 'Option D',
                    ],
                ];
                $seq++;
            }
            $setsPayload[] = [
                'part' => 'PART6',
                'title' => "Part 6 Passage {$p}",
                'stimulus' => "Part 6 reading passage {$p} content.",
                'transcript' => '',
                'media_url' => '',
                'audio_url' => '',
                'questions' => $qs,
            ];
        }

        // 18 PART7 passages x 3 questions = 54 questions (147..200)
        for ($p = 1; $p <= 18; $p++) {
            $qs = [];
            for ($k = 0; $k < 3; $k++) {
                $qs[] = [
                    'seq' => $seq,
                    'difficulty' => 'HARD',
                    'media_url' => '',
                    'content' => "Question {$seq}: What is the main purpose of passage {$p}?",
                    'explanation' => '',
                    'correct' => 'D',
                    'options' => [
                        'A' => 'Option A',
                        'B' => 'Option B',
                        'C' => 'Option C',
                        'D' => 'Option D',
                    ],
                ];
                $seq++;
            }
            $setsPayload[] = [
                'part' => 'PART7',
                'title' => "Part 7 Passage {$p}",
                'stimulus' => "Part 7 reading comprehension passage {$p}.",
                'transcript' => '',
                'media_url' => '',
                'audio_url' => '',
                'questions' => $qs,
            ];
        }

        $batchId = 'test_reading_100_'.uniqid();
        $stagingDir = storage_path('app/public/staging/import_'.$batchId);
        if (! is_dir($stagingDir)) {
            mkdir($stagingDir, 0777, true);
        }
        file_put_contents($stagingDir.'/staging.json', json_encode([
            'file_name' => 'ETS_Reading_100.docx',
            'publish_status' => 'ACTIVE',
            'target_test_id' => $testForm->test_form_id,
            'sets' => $setsPayload,
            'summary' => [
                'total_questions' => 100,
                'valid_questions' => 100,
                'error_questions' => 0,
                'image_count' => 0,
                'transcript_count' => 0,
            ],
        ], JSON_UNESCAPED_UNICODE));

        $reviewResp = $this->actingAs($admin)->get(route('admin.questions.import.review', ['batchId' => $batchId]));
        $reviewResp->assertOk();
        $reviewResp->assertSee('id="commit-payload-json"', false);

        $commitResp = $this->actingAs($admin)->post(route('admin.questions.import.commit', ['batchId' => $batchId]), [
            'payload_json' => json_encode($setsPayload, JSON_UNESCAPED_UNICODE),
            'publish_status' => 'ACTIVE',
            'target_test_id' => $testForm->test_form_id,
            'auto_generate_tts' => '0',
        ]);

        $commitResp->assertRedirect(route('admin.tests.show', $testForm->test_form_id));
        $commitResp->assertSessionHas('success');
        $successMsg = (string) session('success');
        $this->assertStringContainsString('100 câu hỏi', $successMsg);
        $this->assertStringContainsString('gắn 100/100 câu vào đề thi', $successMsg);

        $this->assertSame(100, Question::query()->count());
        $this->assertSame(30, Question::query()->where('section_id', $part5->section_id)->count());
        $this->assertSame(16, Question::query()->where('section_id', $part6->section_id)->count());
        $this->assertSame(54, Question::query()->where('section_id', $part7->section_id)->count());
        $this->assertSame(100, $testForm->questions()->count());
    }

    public function test_listening_docx_explanation_zone_does_not_duplicate_questions_and_part1_has_no_default_text(): void
    {
        $admin = User::factory()->admin()->create();
        $lcSkill = Skill::firstOrCreate(['code' => 'LC'], ['skill_id' => 1, 'skill_name' => 'Listening']);
        ExamSection::firstOrCreate(['code' => 'PART1'], ['section_id' => 1, 'skill_id' => $lcSkill->skill_id, 'name' => 'Photographs', 'sequence_no' => 1]);
        ExamSection::firstOrCreate(['code' => 'PART3'], ['section_id' => 3, 'skill_id' => $lcSkill->skill_id, 'name' => 'Conversations', 'sequence_no' => 3]);

        $png1 = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $docxPath = tempnam(sys_get_temp_dir(), 'studyprep_exp_zone_').'.docx';
        $zip = new \ZipArchive;
        $zip->open($docxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
        $zip->addFromString('word/_rels/document.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rIdImg1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/image1.png"/></Relationships>');
        $zip->addFromString('word/media/image1.png', $png1);

        $bodyXml = ''
            .'<w:p><w:r><w:t>PART 1</w:t></w:r></w:p>'
            .'<w:p><w:r><w:drawing><a:blip r:embed="rIdImg1"/></w:drawing></w:r></w:p>'
            .'<w:p><w:r><w:t>Question 1:</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Giải thích chi tiết ▲</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(A) He is reading a book. (Anh ấy đang đọc sách.)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(B) She is writing notes. (Cô ấy đang ghi chú.)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(C) They are talking. (Họ đang nói chuyện.)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(D) The room is empty. (Căn phòng trống.)</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Đáp án: B</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>PART 3</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Questions 32-34 refer to the following conversation.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>32. Where does the conversation take place?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(A) At a bank</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(B) At a hotel</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(C) At a store</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(D) At an airport</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Đáp án: B</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>33. What does the man want to do?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(A) Book a room</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(B) Cancel a flight</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(C) Buy a ticket</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(D) Rent a car</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Đáp án: A</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>34. What will the woman do next?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(A) Check the schedule</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(B) Print a receipt</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(C) Call a manager</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(D) Send an email</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Đáp án: A</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Giải thích chi tiết ▲</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>32. Cuộc hội thoại diễn ra ở đâu?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(A) Tại ngân hàng (B) Tại khách sạn</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>33. Người đàn ông muốn làm gì?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>34. Người phụ nữ sẽ làm gì tiếp theo?</w:t></w:r></w:p>';

        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><w:body>'.$bodyXml.'</w:body></w:document>');
        $zip->close();

        $uploadedFile = new UploadedFile($docxPath, 'Listening_ExpZone.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);

        $previewResp = $this->actingAs($admin)->post(route('admin.questions.import.preview'), [
            'import_file' => $uploadedFile,
            'import_scope' => 'LISTENING',
            'publish_status' => 'live',
        ]);
        $previewResp->assertRedirect();
        $batchId = basename((string) $previewResp->headers->get('Location'));

        $staging = app(DocxToStandardXmlService::class)->getStagingPayload($batchId);
        $this->assertIsArray($staging);
        $this->assertSame(4, $staging['summary']['total_questions']);

        $part1Set = collect($staging['sets'])->firstWhere('part', 'PART1');
        $this->assertNotNull($part1Set);
        $this->assertSame('', $part1Set['questions'][0]['content']);

        $part3Set = collect($staging['sets'])->firstWhere('part', 'PART3');
        $this->assertNotNull($part3Set);
        $this->assertCount(3, $part3Set['questions']);
        $this->assertSame('At a bank', $part3Set['questions'][0]['options']['A']);
        $this->assertSame('At a hotel', $part3Set['questions'][0]['options']['B']);

        @unlink($docxPath);
    }

    public function test_admin_can_delete_junk_sets_on_review_and_commit_only_remaining_questions(): void
    {
        $admin = User::factory()->admin()->create();
        $rcSkill = Skill::firstOrCreate(['code' => 'RC'], ['skill_id' => 2, 'skill_name' => 'Reading']);
        ExamSection::firstOrCreate(['code' => 'PART5'], ['section_id' => 5, 'skill_id' => $rcSkill->skill_id, 'name' => 'Part 5', 'sequence_no' => 5]);
        QuestionType::firstOrCreate(['question_type_id' => 1], ['name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $keptSet = [
            'part' => 'PART5',
            'title' => 'Valid Set',
            'stimulus' => '',
            'transcript' => '',
            'media_url' => '',
            'audio_url' => '',
            'questions' => [[
                'seq' => 101,
                'difficulty' => 'MEDIUM',
                'media_url' => '',
                'content' => 'Kept valid question 101.',
                'explanation' => '',
                'correct' => 'A',
                'options' => ['A' => 'One', 'B' => 'Two', 'C' => 'Three', 'D' => 'Four'],
            ]],
        ];
        $junkSet = [
            'part' => 'PART5',
            'title' => 'Junk Set',
            'stimulus' => '',
            'transcript' => '',
            'media_url' => '',
            'audio_url' => '',
            'questions' => [[
                'seq' => 102,
                'difficulty' => 'MEDIUM',
                'media_url' => '',
                'content' => 'Junk question 102 that admin deletes.',
                'explanation' => '',
                'correct' => 'A',
                'options' => ['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D'],
            ]],
        ];

        $batchId = 'test_delete_junk_'.uniqid();
        $stagingDir = storage_path('app/public/staging/import_'.$batchId);
        if (! is_dir($stagingDir)) {
            mkdir($stagingDir, 0777, true);
        }
        file_put_contents($stagingDir.'/staging.json', json_encode([
            'file_name' => 'Junk_Filter.docx',
            'publish_status' => 'ACTIVE',
            'target_test_id' => null,
            'sets' => [$keptSet, $junkSet],
            'summary' => [
                'total_questions' => 2,
                'valid_questions' => 2,
                'error_questions' => 0,
                'image_count' => 0,
                'transcript_count' => 0,
            ],
        ], JSON_UNESCAPED_UNICODE));

        $reviewResp = $this->actingAs($admin)->get(route('admin.questions.import.review', ['batchId' => $batchId]));
        $reviewResp->assertOk();
        $reviewResp->assertSee('Xóa cụm');
        $reviewResp->assertSee('Xóa câu');
        $reviewResp->assertSee('recalculateReviewStats', false);

        // Admin deletes junkSet on UI, so payload_json only contains keptSet
        $commitResp = $this->actingAs($admin)->post(route('admin.questions.import.commit', ['batchId' => $batchId]), [
            'payload_json' => json_encode([$keptSet], JSON_UNESCAPED_UNICODE),
            'publish_status' => 'ACTIVE',
            'auto_generate_tts' => '0',
        ]);
        $commitResp->assertRedirect(route('admin.questions.index'));

        $this->assertSame(1, Question::query()->count());
        $this->assertDatabaseHas('questions', ['content' => 'Kept valid question 101.']);
        $this->assertDatabaseMissing('questions', ['content' => 'Junk question 102 that admin deletes.']);
    }

    public function test_part1_six_questions_each_receive_corresponding_media_url(): void
    {
        $admin = User::factory()->admin()->create();
        $lcSkill = Skill::firstOrCreate(['code' => 'LC'], ['skill_id' => 1, 'skill_name' => 'Listening']);
        ExamSection::firstOrCreate(['code' => 'PART1'], ['section_id' => 1, 'skill_id' => $lcSkill->skill_id, 'name' => 'Photographs', 'sequence_no' => 1]);

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $docxPath = tempnam(sys_get_temp_dir(), 'studyprep_p1_six_').'.docx';
        $zip = new \ZipArchive;
        $zip->open($docxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $bodyXml = '<w:p><w:r><w:t>PART 1</w:t></w:r></w:p>';
        for ($i = 1; $i <= 6; $i++) {
            $zip->addFromString("word/media/p1_{$i}.png", $png);
            $rels .= "<Relationship Id=\"rIdImg{$i}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/image\" Target=\"media/p1_{$i}.png\"/>";
            $bodyXml .= "<w:p><w:r><w:drawing><a:blip r:embed=\"rIdImg{$i}\"/></w:drawing></w:r></w:p>"
                ."<w:p><w:r><w:t>Question {$i}:</w:t></w:r></w:p>"
                ."<w:p><w:r><w:t>(A) A{$i}</w:t></w:r></w:p>"
                ."<w:p><w:r><w:t>(B) B{$i}</w:t></w:r></w:p>"
                ."<w:p><w:r><w:t>(C) C{$i}</w:t></w:r></w:p>"
                ."<w:p><w:r><w:t>(D) D{$i}</w:t></w:r></w:p>"
                .'<w:p><w:r><w:t>Đáp án: B</w:t></w:r></w:p>';
        }
        $rels .= '</Relationships>';
        $zip->addFromString('word/_rels/document.xml.rels', $rels);
        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><w:body>'.$bodyXml.'</w:body></w:document>');
        $zip->close();

        $uploadedFile = new UploadedFile($docxPath, 'Part1_Six.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);
        $previewResp = $this->actingAs($admin)->post(route('admin.questions.import.preview'), [
            'import_file' => $uploadedFile,
            'import_scope' => 'LISTENING',
            'publish_status' => 'live',
        ]);
        $previewResp->assertRedirect();
        $batchId = basename((string) $previewResp->headers->get('Location'));

        $staging = app(DocxToStandardXmlService::class)->getStagingPayload($batchId);
        $this->assertIsArray($staging);
        $this->assertSame(6, $staging['summary']['total_questions']);

        foreach ($staging['sets'] as $idx => $set) {
            $this->assertSame('PART1', $set['part']);
            $this->assertNotEmpty($set['media_url'], 'Part 1 question '.($idx + 1).' missing media_url');
            $this->assertNotEmpty($set['questions'][0]['media_url']);
        }

        @unlink($docxPath);
    }

    public function test_listening_part3_and_part4_with_interleaved_graphics_and_watermarks_keep_all_questions(): void
    {
        $parser = app(PdfAnswerScriptParser::class);

        // Simulate LC 1.pdf pages 18-25: 62-64 (graphic), 65-67 (graphic), 68-70 (split page), 71-82 (Part 4 merged STT) + watermarks
        $extractedDoc = [
            'page_count' => 2,
            'pages' => [
                [
                    'page_no' => 1,
                    'blocks' => [],
                    'tables' => [
                        [
                            'rows' => [
                                '62-64 | W: Hi, Suresh. Hien Nhung TOEIC Teach By Heart | | Nữ: Chào Suresh.',
                                ' | 62. Where is the woman? (A) Restaurant (B) Agency (C) Airport (D) Warehouse 63. Look at the graphic. When? (A) Mon (B) Tue (C) Wed (D) Thu 64. What good news? (A) Promo (B) Accepted (C) Upgraded (D) Award | 62. C 63. C 64. D | 62. Ở đâu',
                            ],
                            'text' => '',
                        ],
                        [
                            'rows' => [
                                'Day | Time | Activity',
                                'Tuesday | Noon | Facility tour',
                                'Wednesday | 8:00 A.M. | Meeting with Chicago staff',
                            ],
                            'text' => "Day | Time | Activity\nTuesday | Noon | Facility tour\nWednesday | 8:00 A.M. | Meeting with Chicago staff",
                        ],
                        [
                            'rows' => [
                                '65-67 | M: Marion, parking info? Teach By Heart | | Nam: Marion...',
                                '68-70 | M: Good news! Bicycle racks. | | Nam: Tin tốt...',
                                ' | 68. What news? (A) Road (B) Project (C) Parking (D) Office 69. Look at graphic. Where? (A) Covered (B) Long (C) Short | 68. B 69. A 70. C | 68. Tin tức',
                            ],
                            'text' => '',
                        ],
                    ],
                    'images' => [],
                ],
                [
                    'page_no' => 2,
                    'blocks' => [],
                    'tables' => [
                        [
                            'rows' => [
                                ' | (D) Near overflow parking 70. Why contact? (A) Loan (B) Permit (C) Estimates (D) Proposal | | 70. Tại sao',
                                '71-73 | You have reached Select Repair Service. | | Dịch vụ Select',
                            ],
                            'text' => '',
                        ],
                        [
                            'rows' => [
                                ' | 71. What products? (A) Computers (B) Vehicles (C) Lights (D) Appliances 72. What benefit? (A) Pickup (B) Online (C) Warranties (D) Loyalty 73. Why close? (A) Inventory (B) Training (C) Party (D) Install | 71. B 72. C 73. A | 71. Sản phẩm',
                                '74-76 | Welcome new employees! | | Chào mừng',
                                ' | 74. Who is speaker? (A) Facilities (B) HR (C) Security (D) Exec 75. Find in binder? (A) Map (B) Contract (C) Badge (D) Log-in 76. Department files? (A) Company PC (B) Password (C) Naming (D) Annual | 74. B 75. D 76. A | 74. Ai',
                                '77-79 | Hello from Denville Park. | | Xin chào',
                                ' | 77. Where work? (A) Laundry (B) Park (C) Stadium (D) Gym 78. Item ordered? (A) Late (B) Damaged (C) Help (D) Pleased | 77. B 78. D 79. A | 77. Ở đâu',
                                ' | (A) New game soon (B) Replacement (C) Warranty (D) Contact 79. Confirm what? (A) Available soon (B) Shipped (C) Warranty (D) Contact | | 79. Xác nhận',
                                '80-82 | Board meeting sales report. | | Báo cáo',
                                ' | 80. Company makes? (A) Furniture (B) Luggage (C) Bedding (D) Clothing 81. Recommend? (A) Local (B) Free ship (C) Trade show (D) New line 82. Next meeting? (A) Vote (B) Consultant (C) Contracts (D) Safety | 80. D 81. A 82. B | 80. Sản phẩm',
                            ],
                            'text' => '',
                        ],
                    ],
                    'images' => [],
                ],
            ],
        ];

        $parsed = $parser->parse($extractedDoc);
        $byNum = collect($parsed['fallback_questions'])->keyBy('question_number');

        foreach ([62, 63, 64, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82] as $qNum) {
            $this->assertTrue($byNum->has($qNum), "Missing question {$qNum}");
            $this->assertCount(4, $byNum->get($qNum)['options'], "Question {$qNum} missing 4 options");
            $this->assertArrayHasKey($qNum, $parsed['answers'], "Missing answer for {$qNum}");
            $this->assertStringNotContainsString('Hien Nhung', (string) ($parsed['transcripts'][$qNum] ?? ''));
            $this->assertStringNotContainsString('Teach By Heart', (string) ($parsed['transcripts'][$qNum] ?? ''));
        }

        // Graphic table attached to 62-64 stimulus_text
        $this->assertStringContainsString('Meeting with Chicago staff', (string) $byNum->get(63)['stimulus_text']);
        // Split-page option (D) of 69 and (A..D) of 78 preserved
        $this->assertSame('Near overflow parking', $byNum->get(69)['options']['D']);
        $this->assertSame('Pleased', $byNum->get(78)['options']['D']);
    }

    public function test_commit_part1_image_promotes_to_public_questions_images_and_renders_on_test_show(): void
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
        $lcSkill = Skill::firstOrCreate(['code' => 'LC'], ['skill_id' => 1, 'skill_name' => 'Listening']);
        ExamSection::firstOrCreate(['code' => 'PART1'], ['section_id' => 1, 'skill_id' => $lcSkill->skill_id, 'name' => 'Photographs', 'sequence_no' => 1]);
        QuestionType::firstOrCreate(['question_type_id' => 1], ['name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $bp = TestBlueprint::create(['name' => 'TOEIC L&R', 'exam_type' => TestForm::EXAM_TYPE_LISTENING, 'total_time' => 45, 'status' => 'ACTIVE']);
        $testForm = TestForm::create(['blueprint_id' => $bp->blueprint_id, 'title' => 'Test 009 Image Check', 'exam_type' => TestForm::EXAM_TYPE_LISTENING, 'status' => 'ACTIVE']);

        $batchId = 'test_promote_'.uniqid();
        $stagingImgDir = storage_path('app/public/staging/import_'.$batchId.'/images');
        if (! is_dir($stagingImgDir)) {
            mkdir($stagingImgDir, 0777, true);
        }
        $pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        file_put_contents($stagingImgDir.'/p1_real.png', $pngBytes);

        $setsPayload = [[
            'part' => 'PART1',
            'title' => 'Part 1 Q1',
            'stimulus' => '',
            'transcript' => 'She is typing.',
            'media_url' => 'storage/staging/import_'.$batchId.'/images/p1_real.png',
            'audio_url' => '',
            'questions' => [[
                'seq' => 1,
                'difficulty' => 'EASY',
                'media_url' => 'storage/staging/import_'.$batchId.'/images/p1_real.png',
                'content' => '',
                'explanation' => '',
                'correct' => 'A',
                'options' => ['A' => 'Typing', 'B' => 'Reading', 'C' => 'Eating', 'D' => 'Walking'],
            ]],
        ]];

        app(DocxToStandardXmlService::class)->saveStagingPayload($batchId, [
            'batch_id' => $batchId,
            'file_name' => 'P1_Image.docx',
            'publish_status' => 'ACTIVE',
            'target_test_id' => $testForm->test_form_id,
            'sets' => $setsPayload,
            'summary' => ['total_questions' => 1, 'valid_questions' => 1, 'error_questions' => 0, 'image_count' => 1, 'transcript_count' => 1],
        ]);

        $commitResp = $this->actingAs($admin)->post(route('admin.questions.import.commit', ['batchId' => $batchId]), [
            'payload_json' => json_encode($setsPayload, JSON_UNESCAPED_UNICODE),
            'publish_status' => 'ACTIVE',
            'target_test_id' => $testForm->test_form_id,
            'auto_generate_tts' => '0',
        ]);
        $commitResp->assertRedirect(route('admin.tests.show', $testForm->test_form_id));

        $qSet = QuestionSet::query()->firstOrFail();
        $this->assertStringStartsWith('/storage/questions/images/', (string) $qSet->media_url);
        $this->assertStringNotContainsString('staging/', (string) $qSet->media_url);

        $expectedDiskPath = storage_path('app/public/'.ltrim(str_replace('/storage/', '', (string) $qSet->media_url), '/'));
        $this->assertFileExists($expectedDiskPath);

        $showResp = $this->actingAs($admin)->get(route('admin.tests.show', $testForm->test_form_id));
        $showResp->assertOk();
        $showResp->assertSee('/storage/questions/images/imp_'.$batchId.'_p1_real.png', false);

        @unlink($expectedDiskPath);
    }

    public function test_word_import_extracts_part1_and_part2_directions_without_increasing_question_count_and_generates_tts(): void
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
        $lcSkill = Skill::firstOrCreate(['code' => 'LC'], ['skill_id' => 1, 'skill_name' => 'Listening']);
        ExamSection::firstOrCreate(['code' => 'PART1'], ['section_id' => 1, 'skill_id' => $lcSkill->skill_id, 'name' => 'Photographs', 'sequence_no' => 1]);
        ExamSection::firstOrCreate(['code' => 'PART2'], ['section_id' => 2, 'skill_id' => $lcSkill->skill_id, 'name' => 'Question-Response', 'sequence_no' => 2]);
        QuestionType::firstOrCreate(['question_type_id' => 1], ['name' => 'Multiple Choice', 'response_mode' => 'SINGLE_CHOICE']);

        $bp = TestBlueprint::create(['name' => 'TOEIC L&R', 'exam_type' => TestForm::EXAM_TYPE_LISTENING, 'total_time' => 45, 'status' => 'ACTIVE']);
        $testForm = TestForm::create(['blueprint_id' => $bp->blueprint_id, 'title' => 'Directions Listening Test', 'exam_type' => TestForm::EXAM_TYPE_LISTENING, 'status' => 'ACTIVE']);

        $this->mock(TextToSpeechService::class, function ($mock): void {
            $mock->shouldReceive('synthesize')->andReturnUsing(function (string $text, int $partNum): array {
                return ['relative_path' => "storage/audio/tts/dir_part{$partNum}_".md5($text).'.mp3'];
            });
            $mock->shouldReceive('generateBatchForQuestions')
                ->once()
                ->andReturn([
                    'processed' => 2,
                    'succeeded' => 2,
                    'failed' => 0,
                    'results' => [],
                ]);
        });

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $docxPath = tempnam(sys_get_temp_dir(), 'studyprep_dirs_').'.docx';
        $zip = new \ZipArchive;
        $zip->open($docxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
        $zip->addFromString('word/_rels/document.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rIdImg1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/p1.png"/></Relationships>');
        $zip->addFromString('word/media/p1.png', $png);

        $bodyXml = ''
            .'<w:p><w:r><w:t>PART 1</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Directions: For each question in this part, you will hear four statements.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:drawing><a:blip r:embed="rIdImg1"/></w:drawing></w:r></w:p>'
            .'<w:p><w:r><w:t>1.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(A) A1 (B) B1 (C) C1 (D) D1</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Đáp án: A</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>PART 2</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Directions: You will hear a question or statement and three responses spoken in English.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>7. Where is the bank?</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>(A) Across the street (B) At noon (C) Yes, please</w:t></w:r></w:p>'
            .'<w:p><w:t></w:t><w:r><w:t>Đáp án: A</w:t></w:r></w:p>';

        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><w:body>'.$bodyXml.'</w:body></w:document>');
        $zip->close();

        $uploadedFile = new UploadedFile($docxPath, 'Listening_Directions.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);
        $previewResp = $this->actingAs($admin)->post(route('admin.questions.import.preview'), [
            'import_file' => $uploadedFile,
            'import_scope' => 'LISTENING',
            'target_test_id' => $testForm->test_form_id,
        ]);
        $previewResp->assertRedirect();
        $batchId = basename((string) $previewResp->headers->get('Location'));

        $staging = app(DocxToStandardXmlService::class)->getStagingPayload($batchId);
        $this->assertSame(2, $staging['summary']['total_questions']);
        $this->assertStringContainsString('you will hear four statements', (string) ($staging['part_directions']['PART1'] ?? ''));
        $this->assertStringContainsString('three responses spoken in English', (string) ($staging['part_directions']['PART2'] ?? ''));

        $commitResp = $this->actingAs($admin)->post(route('admin.questions.import.commit', ['batchId' => $batchId]), [
            'payload_json' => json_encode($staging['sets'], JSON_UNESCAPED_UNICODE),
            'part_directions' => $staging['part_directions'],
            'publish_status' => 'ACTIVE',
            'target_test_id' => $testForm->test_form_id,
            'auto_generate_tts' => '1',
        ]);
        $commitResp->assertRedirect(route('admin.tests.show', ['id' => $testForm->test_form_id, 'auto_start_tts' => 1]));
        $commitResp->assertSessionHas('auto_start_tts', true);

        $this->assertSame(2, Question::query()->count());
        $part1Sec = ExamSection::query()->where('code', 'PART1')->firstOrFail();
        $this->assertStringContainsString('you will hear four statements', (string) $part1Sec->directions_text);

        // Trang chi tiết đề thi nhận auto_start_tts=1 và gọi batch TTS
        $showResp = $this->actingAs($admin)->get(route('admin.tests.show', ['id' => $testForm->test_form_id, 'auto_start_tts' => 1]));
        $showResp->assertOk();
        $showResp->assertSee('runTestBatchTts()', false);

        // Gọi API batch-generate-tts (chunked AJAX) -> sinh cả Directions TTS cho PART1 & PART2
        $qIds = Question::query()->pluck('question_id')->all();
        $this->actingAs($admin)->postJson(url('/api/admin/questions/batch-generate-tts'), [
            'question_ids' => $qIds,
        ])->assertOk();

        $part1Sec->refresh();
        $part2Sec = ExamSection::query()->where('code', 'PART2')->firstOrFail();
        $this->assertStringContainsString('/storage/audio/tts/dir_part1_', (string) $part1Sec->directions_audio_url);
        $this->assertStringContainsString('/storage/audio/tts/dir_part2_', (string) $part2Sec->directions_audio_url);

        $examViewResp = $this->actingAs($admin)->get(route('thi-thu-giao-dien-thi', ['test_id' => $testForm->test_form_id]));
        $examViewResp->assertOk();
        $examViewResp->assertSee('PART1 — Directions');
        $examViewResp->assertSee('PART2 — Directions');
        $examViewResp->assertSee('dir_part1_', false);

        @unlink($docxPath);
    }

    public function test_to_standard_xml_escapes_special_characters_and_control_chars_validly(): void
    {
        $service = app(DocxToStandardXmlService::class);
        $xml = $service->toStandardXml([
            [
                'part' => 'PART7',
                'stimulus' => "AT&T and R&D report: budget <= 500 & growth >= 10%.\x00\x07",
                'transcript' => "Speaker A: AT&T < B\x0B",
                'questions' => [
                    [
                        'seq' => 176,
                        'correct' => 'B',
                        'content' => 'Why is R&D budget <= 500?',
                        'options' => [
                            'A' => 'AT&T cut costs',
                            'B' => 'Revenue >= target',
                            'C' => 'Q1 < Q2',
                            'D' => 'N/A & TBD',
                        ],
                        'explanation' => 'R&D >= 10% & <= 500',
                    ],
                ],
            ],
        ]);

        libxml_use_internal_errors(true);
        $parsed = simplexml_load_string($xml);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        $this->assertNotFalse($parsed, 'XML output must be valid for simplexml_load_string()');
        $this->assertEmpty($errors);
        $this->assertStringContainsString('AT&T', (string) $parsed->question_set->stimulus);
        $this->assertSame('AT&T cut costs', (string) $parsed->question_set->question->option[0]);
    }

    public function test_cleanup_expired_staging_directories_removes_only_dirs_older_than_24_hours(): void
    {
        $oldDir = storage_path('app/public/staging/import_test_expired_25h');
        $freshDir = storage_path('app/public/staging/import_test_fresh_1h');
        File::ensureDirectoryExists($oldDir);
        File::ensureDirectoryExists($freshDir);
        @touch($oldDir, time() - (25 * 3600));
        @touch($freshDir, time() - 3600);

        $deleted = app(DocxToStandardXmlService::class)->cleanupExpiredStagingDirectories(24);

        $this->assertGreaterThanOrEqual(1, $deleted);
        $this->assertDirectoryDoesNotExist($oldDir);
        $this->assertDirectoryExists($freshDir);

        File::deleteDirectory($freshDir);
    }

    public function test_word_import_stitches_cross_page_options_and_merges_fragmented_questions_by_seq(): void
    {
        /** @var WordQuestionImportService $wordService */
        $wordService = app(WordQuestionImportService::class);

        $parsed = $wordService->parseLines([
            'PART 5',
            '101. Ms. Tan _______ the contract before noon.',
            '(A) signed',
            '(B) signing',
            'Page 2',
            'ETS TOEIC',
            'GO ON TO THE NEXT PAGE',
            "\f(C) will sign",
            '(D) was signed',
            '102. Which department handles payroll?',
            'A. Finance B. Marketing C. Sales D. Support',
        ], 'READING');

        $this->assertCount(2, $parsed);
        $this->assertCount(4, $parsed[0]['options']);
        $this->assertSame('will sign', $parsed[0]['options']['C']);
        $this->assertSame(['A' => 'Finance', 'B' => 'Marketing', 'C' => 'Sales', 'D' => 'Support'], $parsed[1]['options']);

        $merged = $wordService->mergeFragmentedQuestionsBySeq([
            ['question_number' => 103, 'part_code' => 'PART5', 'content' => 'The meeting starts at 9.', 'options' => ['A' => 'in', 'B' => 'on'], 'correct' => null, 'explanation' => null],
            ['question_number' => 103, 'part_code' => 'PART5', 'content' => '', 'options' => ['C' => 'at', 'D' => 'by'], 'correct' => 'C', 'explanation' => 'Dùng at cho giờ.'],
        ]);
        $this->assertCount(1, $merged);
        $this->assertSame('The meeting starts at 9.', $merged[0]['content']);
        $this->assertCount(4, $merged[0]['options']);
        $this->assertSame('C', $merged[0]['correct']);
    }
}
