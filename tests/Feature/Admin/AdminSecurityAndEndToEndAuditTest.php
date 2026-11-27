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
use App\Models\Topic;
use App\Models\User;
use App\Models\Vocabulary;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class AdminSecurityAndEndToEndAuditTest extends TestCase
{
    use CreatesStudyPrepSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createStudyPrepSchema();

        if (! Schema::hasTable('test_form_question')) {
            Schema::create('test_form_question', function (Blueprint $table): void {
                $table->unsignedBigInteger('test_form_id');
                $table->unsignedBigInteger('question_id');
                $table->integer('sequence_no');
                $table->decimal('point', 6, 2)->default(1);
                $table->primary(['test_form_id', 'question_id']);
            });
        }
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function adminGetRoutesProvider(): array
    {
        return [
            'dashboard' => ['/admin/dashboard'],
            'tests_index' => ['/admin/tests'],
            'questions_index' => ['/admin/questions'],
            'questions_create' => ['/admin/questions/create'],
            'questions_bulk_import' => ['/admin/questions/bulk-import'],
            'vocabularies_index' => ['/admin/vocabularies'],
            'vocabularies_create' => ['/admin/vocabularies/create'],
            'users_index' => ['/admin/users'],
        ];
    }

    #[DataProvider('adminGetRoutesProvider')]
    public function test_guest_access_to_any_admin_module_redirects_to_login(string $uri): void
    {
        $response = $this->get($uri);

        $response->assertRedirect(route('login'));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function nonAdminMethodMatrixProvider(): array
    {
        return [
            'user_get_dashboard' => ['user', 'GET', '/admin/dashboard'],
            'student_get_tests' => ['student', 'GET', '/admin/tests'],
            'user_post_tests' => ['user', 'POST', '/admin/tests'],
            'student_put_tests' => ['student', 'PUT', '/admin/tests/1'],
            'user_delete_tests' => ['user', 'DELETE', '/admin/tests/1'],
            'student_get_questions' => ['student', 'GET', '/admin/questions'],
            'user_post_questions' => ['user', 'POST', '/admin/questions'],
            'student_put_questions' => ['student', 'PUT', '/admin/questions/1'],
            'user_patch_questions' => ['user', 'PATCH', '/admin/questions/1/status'],
            'student_delete_questions' => ['student', 'DELETE', '/admin/questions/1'],
            'user_get_vocabularies' => ['user', 'GET', '/admin/vocabularies'],
            'student_post_vocabularies' => ['student', 'POST', '/admin/vocabularies'],
            'user_put_vocabularies' => ['user', 'PUT', '/admin/vocabularies/1'],
            'student_delete_vocabularies' => ['student', 'DELETE', '/admin/vocabularies/1'],
            'user_get_users' => ['user', 'GET', '/admin/users'],
            'student_post_users' => ['student', 'POST', '/admin/users'],
            'user_patch_users' => ['user', 'PATCH', '/admin/users/1/toggle-status'],
            'student_delete_users' => ['student', 'DELETE', '/admin/users/1'],
        ];
    }

    #[DataProvider('nonAdminMethodMatrixProvider')]
    public function test_student_and_user_roles_are_blocked_with_403_across_all_http_methods(string $role, string $method, string $uri): void
    {
        $account = User::factory()->create(['role' => $role, 'status' => 'ACTIVE']);

        $response = $this->actingAs($account)->call($method, $uri, ['title' => 'Forbidden Attempt']);

        $response->assertForbidden();
    }

    public function test_state_changing_admin_requests_without_csrf_token_return_419_page_expired(): void
    {
        $admin = User::factory()->admin()->create();
        $originalEnv = $this->app['env'];

        try {
            $this->app['env'] = 'local';

            $this->actingAs($admin)->post('/admin/tests', [
                'title' => 'Missing CSRF Test',
                'exam_type' => 'TOEIC_LR',
            ])->assertStatus(419);

            $this->actingAs($admin)->post('/admin/questions', [
                'content' => 'Missing CSRF Question',
            ])->assertStatus(419);

            $this->actingAs($admin)->post('/admin/vocabularies', [
                'word' => 'csrfword',
                'meaning_vi' => 'Thiếu CSRF',
            ])->assertStatus(419);

            $this->actingAs($admin)->post('/admin/users', [
                'full_name' => 'Missing CSRF User',
            ])->assertStatus(419);
        } finally {
            $this->app['env'] = $originalEnv;
        }
    }

    public function test_xss_and_sql_injection_payloads_are_escaped_and_neutralized_across_all_modules(): void
    {
        $admin = User::factory()->admin()->create();
        $xssPayload = '<script>alert(1)</script>';
        $sqliPayload = "1' OR '1'='1' --";

        Topic::create(['topic_id' => 1, 'user_id' => null, 'name' => 'Core System Topic']);
        Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        $part5 = ExamSection::create(['section_id' => 5, 'skill_id' => 2, 'code' => 'PART5', 'name' => 'Part 5', 'sequence_no' => 5]);
        $qType = QuestionType::create(['question_type_id' => 1, 'name' => 'MCQ', 'response_mode' => 'SINGLE_CHOICE']);
        TestBlueprint::create(['blueprint_id' => 1, 'name' => 'TOEIC LR', 'exam_type' => 'TOEIC_LR', 'status' => 'ACTIVE']);

        // 1. Tạo dữ liệu có chuỗi XSS hợp lệ độ dài -> phải escape khi render ra HTML
        TestForm::create(['blueprint_id' => 1, 'title' => "Exam {$xssPayload}"]);
        Question::create([
            'section_id' => $part5->section_id,
            'question_type_id' => $qType->question_type_id,
            'content' => "Stem {$xssPayload}",
            'difficulty' => 'EASY',
            'status' => 'ACTIVE',
            'created_at' => now(),
        ]);
        Vocabulary::create([
            'topic_id' => 1,
            'word' => 'xss_entry',
            'meaning_vi' => "Meaning {$xssPayload}",
        ]);
        User::factory()->create([
            'full_name' => "Learner {$xssPayload}",
            'status' => 'ACTIVE',
        ]);

        foreach (['/admin/tests', '/admin/questions', '/admin/vocabularies', '/admin/users'] as $moduleUri) {
            // Kiểm tra render dữ liệu chứa XSS không bao giờ xuất thẻ <script>alert(1)</script> trần
            $renderResp = $this->actingAs($admin)->get($moduleUri);
            $renderResp->assertOk();
            $this->assertStringNotContainsString($xssPayload, $renderResp->getContent());
            $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $renderResp->getContent());

            // Kiểm tra truyền SQL Injection & XSS trên thanh tìm kiếm không gây lỗi SQL và không dội XSS trần
            $sqliResp = $this->actingAs($admin)->get($moduleUri.'?'.http_build_query(['search' => $sqliPayload]));
            $sqliResp->assertOk();

            $xssSearchResp = $this->actingAs($admin)->get($moduleUri.'?'.http_build_query(['search' => $xssPayload]));
            $xssSearchResp->assertOk();
            $this->assertStringNotContainsString($xssPayload, $xssSearchResp->getContent());
        }

        // Đảm bảo các bảng dữ liệu vẫn nguyên vẹn sau truy vấn SQLi
        $this->assertSame(1, TestForm::count());
        $this->assertSame(1, Question::count());
        $this->assertSame(1, Vocabulary::count());
    }

    public function test_pagination_per_page_and_page_boundaries_clamp_safely_across_modules(): void
    {
        $admin = User::factory()->admin()->create();
        Topic::create(['topic_id' => 1, 'user_id' => null, 'name' => 'Default']);

        $expectedDefaults = [
            '/admin/tests' => ['view_key' => 'tests', 'default' => 10],
            '/admin/questions' => ['view_key' => 'questions', 'default' => 10],
            '/admin/vocabularies' => ['view_key' => 'vocabularies', 'default' => 25],
            '/admin/users' => ['view_key' => 'users', 'default' => 20],
        ];

        foreach ($expectedDefaults as $uri => $meta) {
            foreach ([0, -15, 500] as $invalidPerPage) {
                $resp = $this->actingAs($admin)->get($uri.'?per_page='.$invalidPerPage.'&page=-3');
                $resp->assertOk();
                $resp->assertViewHas($meta['view_key'], fn ($paginator): bool => $paginator->perPage() === $meta['default']);
            }

            $overflowPageResp = $this->actingAs($admin)->get($uri.'?page=9999');
            $overflowPageResp->assertOk();
        }
    }

    public function test_end_to_end_published_test_and_active_questions_reach_student_without_leaking_answers_or_explanations(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create(['role' => 'student', 'status' => 'ACTIVE']);

        $lcSkill = Skill::create(['skill_id' => 1, 'code' => 'LC', 'skill_name' => 'Listening']);
        $rcSkill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        $part1 = ExamSection::create(['section_id' => 1, 'skill_id' => $lcSkill->skill_id, 'code' => 'PART1', 'name' => 'Photographs', 'sequence_no' => 1]);
        $part5 = ExamSection::create(['section_id' => 5, 'skill_id' => $rcSkill->skill_id, 'code' => 'PART5', 'name' => 'Incomplete Sentences', 'sequence_no' => 5]);
        $mcqType = QuestionType::create(['question_type_id' => 1, 'name' => 'Single Choice', 'response_mode' => 'SINGLE_CHOICE']);
        $lrBp = TestBlueprint::create(['blueprint_id' => 1, 'name' => 'TOEIC LR', 'exam_type' => 'TOEIC_LR', 'total_time' => 120, 'status' => 'ACTIVE']);

        $testForm = TestForm::create([
            'blueprint_id' => $lrBp->blueprint_id,
            'title' => 'ETS 2026 End-to-End Official Published Test',
        ]);

        // Tạo 1 câu Part 5 DRAFT và 1 câu Part 1 DRAFT (chứa giải thích bí mật), gắn vào đề rồi bấm Duyệt & Xuất bản
        $secretReadingExp = 'SECRET_READING_EXPLANATION_DO_NOT_LEAK_777';
        $secretListeningExp = 'SECRET_LISTENING_EXPLANATION_DO_NOT_LEAK_888';

        $readingQ = Question::create([
            'section_id' => $part5->section_id,
            'question_type_id' => $mcqType->question_type_id,
            'content' => 'The board of directors ------- the annual budget yesterday.',
            'explanation' => $secretReadingExp,
            'difficulty' => 'MEDIUM',
            'status' => Question::STATUS_DRAFT,
            'created_at' => now(),
        ]);
        foreach (['A' => 'approve', 'B' => 'approved', 'C' => 'approving', 'D' => 'approval'] as $lbl => $txt) {
            AnswerOption::create([
                'question_id' => $readingQ->question_id,
                'label' => $lbl,
                'content' => $txt,
                'is_correct' => $lbl === 'B',
            ]);
        }

        $listeningSet = QuestionSet::create([
            'title' => 'Part 1 Photo',
            'media_url' => 'storage/questions/images/sample_p1.png',
            'media_type' => 'IMAGE',
            'transcript' => 'SECRET_LISTENING_TRANSCRIPT_888',
        ]);
        $listeningQ = Question::create([
            'section_id' => $part1->section_id,
            'question_set_id' => $listeningSet->question_set_id,
            'question_type_id' => $mcqType->question_type_id,
            'content' => 'Look at the picture and choose the best statement.',
            'explanation' => $secretListeningExp,
            'difficulty' => 'EASY',
            'status' => Question::STATUS_DRAFT,
            'created_at' => now(),
        ]);
        foreach (['A' => 'He is typing.', 'B' => 'She is presenting.', 'C' => 'They are dining.', 'D' => 'The room is dark.'] as $lbl => $txt) {
            AnswerOption::create([
                'question_id' => $listeningQ->question_id,
                'label' => $lbl,
                'content' => $txt,
                'is_correct' => $lbl === 'C',
            ]);
        }

        // Tạo thêm 1 câu DRAFT lẻ bên ngoài (không được xuất hiện ở phía học viên)
        Question::create([
            'section_id' => $part5->section_id,
            'question_type_id' => $mcqType->question_type_id,
            'content' => 'UNPUBLISHED_DRAFT_QUESTION_NEVER_SHOW',
            'difficulty' => 'EASY',
            'status' => Question::STATUS_DRAFT,
            'created_at' => now(),
        ]);

        DB::table('test_form_question')->insert([
            ['test_form_id' => $testForm->test_form_id, 'question_id' => $listeningQ->question_id, 'sequence_no' => 1, 'point' => 1],
            ['test_form_id' => $testForm->test_form_id, 'question_id' => $readingQ->question_id, 'sequence_no' => 2, 'point' => 1],
        ]);

        // Admin bấm "Duyệt & Xuất bản" -> chuyển toàn bộ câu hỏi trong đề sang ACTIVE và redirect về danh sách đề
        $publishResp = $this->actingAs($admin)->post(route('admin.tests.save', $testForm->test_form_id), [
            'action_type' => 'approve',
        ]);
        $publishResp->assertRedirect(route('admin.tests.index'));
        $this->assertSame(Question::STATUS_ACTIVE, $readingQ->fresh()->status);
        $this->assertSame(Question::STATUS_ACTIVE, $listeningQ->fresh()->status);

        // 1. Học viên truy cập danh sách đề thi thử (/thi-thu) & giao diện thi (/thi-thu/giao-dien-thi?test_id=...)
        $examCenterResp = $this->actingAs($student)->get('/thi-thu');
        $examCenterResp->assertOk();
        $examCenterResp->assertSee('ETS 2026 End-to-End Official Published Test');

        $examRoomResp = $this->actingAs($student)->get('/thi-thu/giao-dien-thi?test_id='.$testForm->test_form_id);
        $examRoomResp->assertOk();
        $examRoomResp->assertSee('The board of directors ------- the annual budget yesterday.');
        $examRoomResp->assertDontSee('UNPUBLISHED_DRAFT_QUESTION_NEVER_SHOW');
        $examRoomHtml = $examRoomResp->getContent();
        $this->assertStringNotContainsString($secretReadingExp, $examRoomHtml);
        $this->assertStringNotContainsString($secretListeningExp, $examRoomHtml);
        $this->assertStringNotContainsString('is_correct', $examRoomHtml);

        // 2. Học viên vào Luyện Đọc Part 5 trước khi nộp bài -> thấy câu ACTIVE, tuyệt đối KHÔNG lộ is_correct hay explanation
        $readingPracticeResp = $this->actingAs($student)->get(route('luyen-tap.doc.hoan-thanh-cau', ['part' => 5, 'count' => 10]));
        $readingPracticeResp->assertOk();
        $readingPracticeResp->assertSee('The board of directors ------- the annual budget yesterday.');
        $readingPracticeResp->assertDontSee('UNPUBLISHED_DRAFT_QUESTION_NEVER_SHOW');
        $readingHtml = $readingPracticeResp->getContent();
        $this->assertStringNotContainsString($secretReadingExp, $readingHtml);
        $this->assertStringNotContainsString('is_correct', $readingHtml);
        $this->assertStringNotContainsString('"correct":"', $readingHtml);
        $readingPracticeResp->assertViewHas('questions', fn (array $qs): bool => ($qs[0]['correct'] ?? null) === null && ($qs[0]['explanation'] ?? null) === null);

        // 3. Học viên vào Luyện Nghe trước khi nộp bài -> thấy câu ACTIVE, tuyệt đối KHÔNG lộ correct, transcript hay explanation
        $listeningPracticeResp = $this->actingAs($student)->get(route('luyen-tap.nghe.lam-bai', ['part' => 1, 'count' => 10]));
        $listeningPracticeResp->assertOk();
        $listeningHtml = $listeningPracticeResp->getContent();
        $this->assertStringNotContainsString($secretListeningExp, $listeningHtml);
        $this->assertStringNotContainsString('SECRET_LISTENING_TRANSCRIPT_888', $listeningHtml);
        $this->assertStringNotContainsString('is_correct', $listeningHtml);
        $this->assertStringNotContainsString('"correct":', $listeningHtml);
    }

    public function test_database_transaction_rolls_back_cleanly_without_orphan_records_and_admin_lists_avoid_n_plus_1_queries(): void
    {
        $admin = User::factory()->admin()->create();
        $rcSkill = Skill::create(['skill_id' => 2, 'code' => 'RC', 'skill_name' => 'Reading']);
        $part6 = ExamSection::create(['section_id' => 6, 'skill_id' => $rcSkill->skill_id, 'code' => 'PART6', 'name' => 'Text Completion', 'sequence_no' => 6]);
        $qType = QuestionType::create(['question_type_id' => 1, 'name' => 'MCQ', 'response_mode' => 'SINGLE_CHOICE']);
        Topic::create(['topic_id' => 1, 'user_id' => null, 'name' => 'System Vocab']);
        TestBlueprint::create(['blueprint_id' => 1, 'name' => 'TOEIC LR', 'exam_type' => 'TOEIC_LR', 'status' => 'ACTIVE']);

        // 1. Kiểm chứng DB::transaction() rollback an toàn khi phát sinh ngoại lệ giữa chừng
        $initialSets = QuestionSet::count();
        $initialQuestions = Question::count();
        $initialOptions = AnswerOption::count();

        try {
            DB::transaction(function () use ($part6, $qType): void {
                $set = QuestionSet::create([
                    'title' => 'Rollback Passage',
                    'stimulus_text' => 'This set must be rolled back completely.',
                    'media_type' => 'READING_PASSAGE',
                ]);
                $q = Question::create([
                    'section_id' => $part6->section_id,
                    'question_set_id' => $set->question_set_id,
                    'question_type_id' => $qType->question_type_id,
                    'content' => 'Partial question before crash',
                    'difficulty' => 'MEDIUM',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                ]);
                AnswerOption::create([
                    'question_id' => $q->question_id,
                    'label' => 'A',
                    'content' => 'Option A',
                    'is_correct' => true,
                ]);

                throw new RuntimeException('Simulated mid-transaction failure');
            });
        } catch (RuntimeException) {
            // Expected
        }

        $this->assertSame($initialSets, QuestionSet::count());
        $this->assertSame($initialQuestions, Question::count());
        $this->assertSame($initialOptions, AnswerOption::count());

        // 2. Kiểm chứng chống N+1 Query (Eager Loading): tăng từ 2 bản ghi lên 12 bản ghi, số lượng SQL query giữ hằng số O(1)
        for ($i = 1; $i <= 2; $i++) {
            $q = Question::create(['section_id' => $part6->section_id, 'question_type_id' => $qType->question_type_id, 'content' => "Seed Q {$i}", 'difficulty' => 'EASY', 'status' => 'ACTIVE', 'created_at' => now()]);
            AnswerOption::create(['question_id' => $q->question_id, 'label' => 'A', 'content' => 'Opt A', 'is_correct' => true]);
            Vocabulary::create(['topic_id' => 1, 'word' => "word_small_{$i}", 'meaning_vi' => 'Nghĩa']);
            User::factory()->create();
        }

        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->actingAs($admin)->get('/admin/questions')->assertOk();
        $qCountSmall = count(DB::getQueryLog());

        DB::flushQueryLog();
        $this->actingAs($admin)->get('/admin/vocabularies')->assertOk();
        $vCountSmall = count(DB::getQueryLog());

        DB::flushQueryLog();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $uCountSmall = count(DB::getQueryLog());

        // Bơm thêm 8 bản ghi nữa (gấp 5 lần số dòng hiển thị trên 1 trang)
        for ($i = 3; $i <= 10; $i++) {
            $q = Question::create(['section_id' => $part6->section_id, 'question_type_id' => $qType->question_type_id, 'content' => "Seed Q {$i}", 'difficulty' => 'EASY', 'status' => 'ACTIVE', 'created_at' => now()]);
            AnswerOption::create(['question_id' => $q->question_id, 'label' => 'A', 'content' => 'Opt A', 'is_correct' => true]);
            Vocabulary::create(['topic_id' => 1, 'word' => "word_large_{$i}", 'meaning_vi' => 'Nghĩa']);
            User::factory()->create();
        }

        DB::flushQueryLog();
        $this->actingAs($admin)->get('/admin/questions')->assertOk();
        $qCountLarge = count(DB::getQueryLog());

        DB::flushQueryLog();
        $this->actingAs($admin)->get('/admin/vocabularies')->assertOk();
        $vCountLarge = count(DB::getQueryLog());

        DB::flushQueryLog();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $uCountLarge = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Không được tăng tuyến tính theo số dòng (+8 dòng -> chênh lệch tối đa <= 2 query phụ trợ nếu có)
        $this->assertLessThanOrEqual($qCountSmall + 2, $qCountLarge, 'Phát hiện N+1 query tại /admin/questions');
        $this->assertLessThanOrEqual($vCountSmall + 2, $vCountLarge, 'Phát hiện N+1 query tại /admin/vocabularies');
        $this->assertLessThanOrEqual($uCountSmall + 2, $uCountLarge, 'Phát hiện N+1 query tại /admin/users');
    }
}
