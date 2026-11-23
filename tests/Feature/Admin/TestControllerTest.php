<?php

namespace Tests\Feature\Admin;

use App\Models\ExamSection;
use App\Models\Question;
use App\Models\TestBlueprint;
use App\Models\TestForm;
use App\Models\User;
use App\Services\TestFormService;
use App\Services\WordQuestionImportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class TestControllerTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.tests.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_admin_tests(): void
    {
        $student = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($student)->get(route('admin.tests.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_tests_index_with_strict_per_part_kpi_and_active_only_counts(): void
    {
        $admin = User::factory()->admin()->create();
        $this->seedSectionsAndBlueprints();

        $lrBlueprint = TestBlueprint::where('exam_type', 'TOEIC_LR')->firstOrFail();
        $writingBlueprint = TestBlueprint::where('exam_type', 'TOEIC_WRITING')->firstOrFail();

        $emptyTest = TestForm::create([
            'blueprint_id' => $lrBlueprint->blueprint_id,
            'title' => 'ETS 2026 Draft Test',
        ]);
        // Câu hỏi DRAFT không được tính vào số câu hợp lệ
        $this->attachQuestionsByCode($emptyTest->test_form_id, 'PART1', 2, Question::STATUS_DRAFT);

        $completedWritingTest = TestForm::create([
            'blueprint_id' => $writingBlueprint->blueprint_id,
            'title' => 'Writing Full Completed Test',
        ]);
        // Đủ chuẩn ETS TOEIC Writing: 5 T1 + 2 T2 + 1 T3 = 8 câu ACTIVE
        $this->attachQuestionsByCode($completedWritingTest->test_form_id, 'WRITING_T1', 5, Question::STATUS_ACTIVE);
        $this->attachQuestionsByCode($completedWritingTest->test_form_id, 'WRITING_T2', 2, Question::STATUS_ACTIVE);
        $this->attachQuestionsByCode($completedWritingTest->test_form_id, 'WRITING_T3', 1, Question::STATUS_ACTIVE);

        $response = $this->actingAs($admin)->get(route('admin.tests.index'));

        $response->assertOk();
        $response->assertSee($emptyTest->title);
        $response->assertSee($completedWritingTest->title);
        $response->assertSee('8 / 8');
        $response->assertSee('0 / 200');
        $response->assertSee('+2 câu chưa duyệt (không tính)');
        $this->assertNotNull($emptyTest->fresh()->created_at);

        $response->assertViewHas('stats', function (array $stats): bool {
            return $stats['total'] === 2
                && $stats['lr_count'] === 1
                && $stats['sw_count'] === 1
                && $stats['completed'] === 1;
        });
    }

    public function test_over_quota_or_wrong_skill_test_is_flagged_invalid_and_excluded_from_completed_kpi(): void
    {
        $admin = User::factory()->admin()->create();
        $this->seedSectionsAndBlueprints();

        $writingBlueprint = TestBlueprint::where('exam_type', 'TOEIC_WRITING')->firstOrFail();

        // Đề Writing (chuẩn 8 câu) nhưng bị gắn 10 câu WRITING_T1 (vượt chỉ tiêu 125.0%)
        $overQuotaTest = TestForm::create([
            'blueprint_id' => $writingBlueprint->blueprint_id,
            'title' => 'Over Quota Writing Test',
        ]);
        $this->attachQuestionsByCode($overQuotaTest->test_form_id, 'WRITING_T1', 10, Question::STATUS_ACTIVE);

        $response = $this->actingAs($admin)->get(route('admin.tests.index'));

        $response->assertOk();
        $response->assertSee('10 / 8');
        $response->assertSee('125.0%');
        $response->assertSee('Vượt chỉ tiêu / Lệch cấu trúc');

        $response->assertViewHas('stats', fn (array $stats): bool => $stats['completed'] === 0);

        $invalidFilter = $this->actingAs($admin)->get(route('admin.tests.index', ['status' => TestForm::STATUS_INVALID]));
        $invalidFilter->assertOk();
        $invalidFilter->assertSee('Over Quota Writing Test');
    }

    public function test_admin_can_filter_tests_by_keyword_type_and_status(): void
    {
        $admin = User::factory()->admin()->create();
        $this->seedSectionsAndBlueprints();

        $lrBlueprint = TestBlueprint::where('exam_type', 'TOEIC_LR')->firstOrFail();
        $swBlueprint = TestBlueprint::where('exam_type', 'TOEIC_SW')->firstOrFail();

        $lrEmpty = TestForm::create([
            'blueprint_id' => $lrBlueprint->blueprint_id,
            'title' => 'Alpha LR Empty',
        ]);

        $swInProgress = TestForm::create([
            'blueprint_id' => $swBlueprint->blueprint_id,
            'title' => 'Beta SW Partial',
        ]);
        $this->attachQuestionsByCode($swInProgress->test_form_id, 'SPK_T1', 2, Question::STATUS_ACTIVE);
        $this->attachQuestionsByCode($swInProgress->test_form_id, 'WRITING_T1', 3, Question::STATUS_ACTIVE);

        $response = $this->actingAs($admin)->get(route('admin.tests.index', [
            'search' => 'Beta',
            'type' => 'TOEIC_SW',
            'status' => 'IN_PROGRESS',
        ]));

        $response->assertOk();
        $response->assertSee('Beta SW Partial');
        $response->assertDontSee('Alpha LR Empty');

        // Kiểm tra lọc theo mã đề (TEST-001)
        $byCode = $this->actingAs($admin)->get(route('admin.tests.index', [
            'keyword' => $lrEmpty->test_code,
        ]));
        $byCode->assertOk();
        $byCode->assertSee('Alpha LR Empty');
        $byCode->assertDontSee('Beta SW Partial');
    }

    public function test_admin_tests_index_paginates_ten_items_per_page(): void
    {
        $admin = User::factory()->admin()->create();
        $this->seedSectionsAndBlueprints();
        $lrBlueprint = TestBlueprint::where('exam_type', 'TOEIC_LR')->firstOrFail();

        for ($i = 1; $i <= 12; $i++) {
            TestForm::create([
                'blueprint_id' => $lrBlueprint->blueprint_id,
                'title' => "Paginated Test {$i}",
            ]);
        }

        $page1 = $this->actingAs($admin)->get(route('admin.tests.index'));
        $page1->assertOk();
        $page1->assertViewHas('tests', fn ($paginator): bool => $paginator->count() === 10 && $paginator->total() === 12);

        $page2 = $this->actingAs($admin)->get(route('admin.tests.index', ['page' => 2]));
        $page2->assertOk();
        $page2->assertViewHas('tests', fn ($paginator): bool => $paginator->count() === 2 && $paginator->total() === 12);
    }

    public function test_admin_can_create_test_with_official_ets_speaking_and_reading_blueprints(): void
    {
        $admin = User::factory()->admin()->create();
        $this->seedSectionsAndBlueprints();

        $readingResp = $this->actingAs($admin)->post(route('admin.tests.store'), [
            'title' => 'TOEIC Reading Intensive Test 01',
            'exam_type' => 'TOEIC_READING',
        ]);

        $readingTest = TestForm::where('title', 'TOEIC Reading Intensive Test 01')->firstOrFail();
        $readingResp->assertRedirect(route('admin.tests.show', $readingTest->test_form_id));
        $this->assertNotNull($readingTest->created_at);

        $readingDetail = $this->actingAs($admin)->get(route('admin.tests.show', $readingTest->test_form_id));
        $readingDetail->assertOk();
        $readingDetail->assertSee('Part 5: Incomplete Sentences');
        $readingDetail->assertSee('Part 6: Text Completion');
        $readingDetail->assertSee('Part 7: Reading Comprehension');
        $readingDetail->assertDontSee('Part 1: Photographs');

        // Tạo đề TOEIC Speaking chuẩn ETS (11 câu - 5 phần)
        $speakingResp = $this->actingAs($admin)->post(route('admin.tests.store'), [
            'title' => 'TOEIC Speaking ETS Standard 01',
            'exam_type' => 'TOEIC_SPEAKING',
        ]);

        $speakingTest = TestForm::where('title', 'TOEIC Speaking ETS Standard 01')->firstOrFail();
        $speakingResp->assertRedirect(route('admin.tests.show', $speakingTest->test_form_id));

        $speakingDetail = $this->actingAs($admin)->get(route('admin.tests.show', $speakingTest->test_form_id));
        $speakingDetail->assertOk();
        $speakingDetail->assertSee('0 / 11 câu hỏi');
        $speakingDetail->assertSee('Part 5 (Speaking): Express an Opinion');
    }

    public function test_store_validates_unique_title_and_exam_type(): void
    {
        $admin = User::factory()->admin()->create();
        $this->seedSectionsAndBlueprints();
        $lrBlueprint = TestBlueprint::where('exam_type', 'TOEIC_LR')->firstOrFail();

        TestForm::create([
            'blueprint_id' => $lrBlueprint->blueprint_id,
            'title' => 'Duplicate Title',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.tests.store'), [
            'title' => 'Duplicate Title',
            'exam_type' => 'INVALID',
        ]);

        $response->assertSessionHasErrors(['title', 'exam_type']);
    }

    public function test_update_exam_type_auto_detaches_incompatible_questions_and_syncs_blueprint(): void
    {
        $admin = User::factory()->admin()->create();
        $this->seedSectionsAndBlueprints();
        $lrBlueprint = TestBlueprint::where('exam_type', 'TOEIC_LR')->firstOrFail();

        $testForm = TestForm::create([
            'blueprint_id' => $lrBlueprint->blueprint_id,
            'title' => 'Mixed LR Test To Convert',
        ]);
        // Gắn 3 câu Listening (PART1) và 2 câu Reading (PART5)
        $this->attachQuestionsByCode($testForm->test_form_id, 'PART1', 3, Question::STATUS_ACTIVE);
        $this->attachQuestionsByCode($testForm->test_form_id, 'PART5', 2, Question::STATUS_ACTIVE);

        $updateResp = $this->actingAs($admin)->put(route('admin.tests.update', $testForm->test_form_id), [
            'title' => 'Converted Reading Only Test',
            'exam_type' => 'TOEIC_READING',
        ]);

        $updateResp->assertRedirect(route('admin.tests.index'));
        $updateResp->assertSessionHas('success');
        $this->assertStringContainsString('tự động gỡ 3 câu hỏi', (string) session('success'));

        $this->assertEquals('Converted Reading Only Test', $testForm->fresh()->title);
        $this->assertEquals('TOEIC_READING', $testForm->fresh()->blueprint->exam_type);
        // 3 câu PART1 đã bị gỡ, còn đúng 2 câu PART5
        $this->assertEquals(2, $testForm->questions()->count());
    }

    public function test_attach_questions_blocks_wrong_skill_and_over_part_limit(): void
    {
        $admin = User::factory()->admin()->create();
        $this->seedSectionsAndBlueprints();
        $writingBlueprint = TestBlueprint::where('exam_type', 'TOEIC_WRITING')->firstOrFail();

        $writingTest = TestForm::create([
            'blueprint_id' => $writingBlueprint->blueprint_id,
            'title' => 'Strict Attach Writing Test',
        ]);

        // Tạo 1 câu PART1 (sai kỹ năng) và 3 câu WRITING_T3 (chỉ tiêu Part 3 Writing chỉ là 1 câu)
        $wrongSkillId = $this->createStandaloneQuestion('PART1');
        $w3a = $this->createStandaloneQuestion('WRITING_T3');
        $w3b = $this->createStandaloneQuestion('WRITING_T3');
        $w3c = $this->createStandaloneQuestion('WRITING_T3');

        $response = $this->actingAs($admin)->post(route('admin.tests.questions.attach', $writingTest->test_form_id), [
            'question_ids' => [$wrongSkillId, $w3a, $w3b, $w3c],
        ]);

        $response->assertRedirect(route('admin.tests.show', $writingTest->test_form_id));
        // Chỉ gắn đúng 1 câu WRITING_T3, chặn 1 câu sai kỹ năng & 2 câu vượt chỉ tiêu Part
        $this->assertEquals(1, $writingTest->questions()->count());
        $this->assertDatabaseHas('test_form_question', [
            'test_form_id' => $writingTest->test_form_id,
            'question_id' => $w3a,
        ]);
        $this->assertDatabaseMissing('test_form_question', [
            'test_form_id' => $writingTest->test_form_id,
            'question_id' => $wrongSkillId,
        ]);
    }

    public function test_delete_is_blocked_when_test_has_student_attempts_and_allowed_otherwise(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create(['role' => 'user']);
        $this->seedSectionsAndBlueprints();
        $lrBlueprint = TestBlueprint::where('exam_type', 'TOEIC_LR')->firstOrFail();

        $attemptedTest = TestForm::create([
            'blueprint_id' => $lrBlueprint->blueprint_id,
            'title' => 'Attempted Exam Must Stay',
        ]);

        DB::table('attempts')->insert([
            'user_id' => $student->user_id,
            'test_form_id' => $attemptedTest->test_form_id,
            'session_type' => 'FULL_TEST',
            'started_at' => now(),
            'status' => 'SUBMITTED',
        ]);

        $blockedResp = $this->actingAs($admin)->delete(route('admin.tests.destroy', $attemptedTest->test_form_id));
        $blockedResp->assertRedirect(route('admin.tests.index'));
        $blockedResp->assertSessionHas('error');
        $this->assertDatabaseHas('test_form', ['test_form_id' => $attemptedTest->test_form_id]);

        $cleanTest = TestForm::create([
            'blueprint_id' => $lrBlueprint->blueprint_id,
            'title' => 'Clean Deletable Exam',
        ]);
        $deleteResp = $this->actingAs($admin)->delete(route('admin.tests.destroy', $cleanTest->test_form_id));
        $deleteResp->assertRedirect(route('admin.tests.index'));
        $deleteResp->assertSessionHas('success');
        $this->assertDatabaseMissing('test_form', ['test_form_id' => $cleanTest->test_form_id]);
    }

    public function test_admin_can_edit_and_detach_question_inside_test_and_save_state(): void
    {
        $admin = User::factory()->admin()->create();
        $this->seedSectionsAndBlueprints();
        $lrBlueprint = TestBlueprint::where('exam_type', 'TOEIC_LR')->firstOrFail();

        $testForm = TestForm::create([
            'blueprint_id' => $lrBlueprint->blueprint_id,
            'title' => 'Editable Questions Test',
        ]);

        $this->attachQuestionsByCode($testForm->test_form_id, 'PART1', 1, Question::STATUS_ACTIVE);
        $questionId = (int) DB::table('test_form_question')
            ->where('test_form_id', $testForm->test_form_id)
            ->value('question_id');

        $editQ = $this->actingAs($admin)->put(route('admin.tests.questions.update', [
            'id' => $testForm->test_form_id,
            'questionId' => $questionId,
        ]), [
            'content' => 'Updated Question Stem Inside Test Editor',
            'difficulty' => 'HARD',
            'opt_a' => 'Choice A',
            'opt_b' => 'Choice B',
            'opt_c' => 'Choice C',
            'opt_d' => 'Choice D',
            'correct_option' => 'C',
        ]);

        $editQ->assertRedirect(route('admin.tests.show', $testForm->test_form_id));
        $this->assertDatabaseHas('questions', [
            'question_id' => $questionId,
            'content' => 'Updated Question Stem Inside Test Editor',
            'difficulty' => 'HARD',
        ]);

        $saveResp = $this->actingAs($admin)->post(route('admin.tests.save', $testForm->test_form_id), [
            'action_type' => 'approve',
        ]);
        $saveResp->assertRedirect(route('admin.tests.index'));

        $detachResp = $this->actingAs($admin)->delete(route('admin.tests.questions.detach', [
            'id' => $testForm->test_form_id,
            'questionId' => $questionId,
        ]));
        $detachResp->assertRedirect(route('admin.tests.show', $testForm->test_form_id));
        $this->assertEquals(0, $testForm->questions()->count());
    }

    public function test_attach_questions_reaches_exact_100_percent_quota_for_speaking_writing_and_listening(): void
    {
        $this->seedSectionsAndBlueprints();
        $service = app(TestFormService::class);

        // 1. TOEIC Speaking: 11/11 (2 + 2 + 3 + 3 + 1)
        $speakingTest = $service->create([
            'title' => 'Full Speaking Quota Check',
            'exam_type' => TestForm::EXAM_TYPE_SPEAKING,
        ]);
        $speakingIds = [];
        foreach (['SPK_T1' => 2, 'SPK_T2' => 2, 'SPK_T3' => 3, 'SPK_T4' => 3, 'SPK_T5' => 1] as $code => $qty) {
            for ($i = 0; $i < $qty; $i++) {
                $speakingIds[] = $this->createStandaloneQuestion($code, Question::STATUS_ACTIVE);
            }
        }
        $spkResult = $service->attachQuestions($speakingTest, $speakingIds);
        $this->assertSame(11, $spkResult['attached']);
        $this->assertSame(0, $spkResult['rejected_limit']);
        $spkDetail = $service->detail($speakingTest->fresh());
        $this->assertSame(11, $spkDetail['current_questions']);
        $this->assertSame(TestForm::STATUS_COMPLETED, $spkDetail['status']);

        // 2. TOEIC Writing: 8/8 (5 + 2 + 1)
        $writingTest = $service->create([
            'title' => 'Full Writing Quota Check',
            'exam_type' => TestForm::EXAM_TYPE_WRITING,
        ]);
        $writingIds = [];
        foreach (['WRITING_T1' => 5, 'WRITING_T2' => 2, 'WRITING_T3' => 1] as $code => $qty) {
            for ($i = 0; $i < $qty; $i++) {
                $writingIds[] = $this->createStandaloneQuestion($code, Question::STATUS_ACTIVE);
            }
        }
        $wrtResult = $service->attachQuestions($writingTest, $writingIds);
        $this->assertSame(8, $wrtResult['attached']);
        $this->assertSame(0, $wrtResult['rejected_limit']);
        $wrtDetail = $service->detail($writingTest->fresh());
        $this->assertSame(8, $wrtDetail['current_questions']);
        $this->assertSame(TestForm::STATUS_COMPLETED, $wrtDetail['status']);

        // 3. TOEIC Listening: 100/100 (6 + 25 + 39 + 30)
        $listeningTest = $service->create([
            'title' => 'Full Listening Quota Check',
            'exam_type' => TestForm::EXAM_TYPE_LISTENING,
        ]);
        $listeningIds = [];
        foreach (['PART1' => 6, 'PART2' => 25, 'PART3' => 39, 'PART4' => 30] as $code => $qty) {
            for ($i = 0; $i < $qty; $i++) {
                $listeningIds[] = $this->createStandaloneQuestion($code, Question::STATUS_ACTIVE);
            }
        }
        $lcResult = $service->attachQuestions($listeningTest, $listeningIds);
        $this->assertSame(100, $lcResult['attached']);
        $this->assertSame(0, $lcResult['rejected_limit']);
        $lcDetail = $service->detail($listeningTest->fresh());
        $this->assertSame(100, $lcDetail['current_questions']);
        $this->assertSame(TestForm::STATUS_COMPLETED, $lcDetail['status']);
        $this->assertTrue($lcDetail['audio_summary']['is_listening_test']);
        $this->assertSame(100, $lcDetail['audio_summary']['missing_audio_count']);
    }

    public function test_test_quality_validation_detects_issues_and_one_click_cleanup_removes_duplicates_and_wrong_skill(): void
    {
        $admin = User::factory()->admin()->create();
        $this->seedSectionsAndBlueprints();
        $service = app(TestFormService::class);

        $listeningTest = $service->create([
            'title' => 'Listening Quality Audit Test',
            'exam_type' => TestForm::EXAM_TYPE_LISTENING,
        ]);

        $part2SectionId = (int) ExamSection::where('code', 'PART2')->value('section_id');
        $part5SectionId = (int) ExamSection::where('code', 'PART5')->value('section_id');

        // 2 câu trùng nội dung thuộc PART2
        $dupKeepId = (int) DB::table('questions')->insertGetId([
            'section_id' => $part2SectionId,
            'question_type_id' => 3,
            'content' => 'Where is the conference room located?',
            'difficulty' => 'MEDIUM',
            'points' => 1.00,
            'status' => Question::STATUS_ACTIVE,
            'created_at' => now(),
        ]);
        $dupExtraId = (int) DB::table('questions')->insertGetId([
            'section_id' => $part2SectionId,
            'question_type_id' => 3,
            'content' => '  where is   the conference room located?  ',
            'difficulty' => 'MEDIUM',
            'points' => 1.00,
            'status' => Question::STATUS_ACTIVE,
            'created_at' => now(),
        ]);
        // 1 câu sai kỹ năng (PART5 gắn lọt vào đề Listening)
        $wrongSkillId = (int) DB::table('questions')->insertGetId([
            'section_id' => $part5SectionId,
            'question_type_id' => 1,
            'content' => 'The manager _____ the report before noon.',
            'difficulty' => 'MEDIUM',
            'points' => 1.00,
            'status' => Question::STATUS_ACTIVE,
            'created_at' => now(),
        ]);

        DB::table('test_form_question')->insert([
            ['test_form_id' => $listeningTest->test_form_id, 'question_id' => $dupKeepId, 'sequence_no' => 1, 'point' => 1.0],
            ['test_form_id' => $listeningTest->test_form_id, 'question_id' => $dupExtraId, 'sequence_no' => 2, 'point' => 1.0],
            ['test_form_id' => $listeningTest->test_form_id, 'question_id' => $wrongSkillId, 'sequence_no' => 3, 'point' => 1.0],
        ]);

        $detail = $service->detail($listeningTest->fresh());
        $quality = $detail['quality'];

        $this->assertTrue($quality['has_warnings']);
        $this->assertSame([$wrongSkillId], $quality['wrong_skill_ids']);
        $this->assertSame([$dupExtraId], $quality['duplicate_ids']);
        $this->assertNotEmpty($quality['format_issues']);
        $this->assertNotEmpty($quality['quota_issues']);

        // Kiểm tra trang chi tiết hiển thị cả nút Sinh Audio TTS hàng loạt lẫn Banner kiểm định
        $showResp = $this->actingAs($admin)->get(route('admin.tests.show', $listeningTest->test_form_id));
        $showResp->assertOk();
        $showResp->assertSee('Sinh Audio TTS hàng loạt');
        $showResp->assertSee('Cảnh báo Kiểm định Chất lượng Đề thi');
        $showResp->assertSee('Xóa nhanh câu trùng / sai kỹ năng');

        // 1-click cleanup
        $cleanupResp = $this->actingAs($admin)->post(route('admin.tests.cleanup-issues', $listeningTest->test_form_id));
        $cleanupResp->assertRedirect(route('admin.tests.show', $listeningTest->test_form_id));
        $cleanupResp->assertSessionHas('success');

        $remainingIds = DB::table('test_form_question')
            ->where('test_form_id', $listeningTest->test_form_id)
            ->pluck('question_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $this->assertSame([$dupKeepId], $remainingIds);
    }

    public function test_word_parser_preserves_bare_question_headers_and_ignores_numbered_bullets_in_passage(): void
    {
        $parser = app(WordQuestionImportService::class);
        $rawText = <<<'TXT'
PART 7: Reading Comprehension
Questions 147-148 refer to the following notice.
Notice to all staff:
1. Please lock the storage room before leaving.
2. Submit expense reports by Friday.
Question 147:
What is the purpose of the notice?
(A) To announce a holiday
(B) To remind staff of office procedures
(C) To introduce a new manager
(D) To schedule a meeting
Answer: B
Question 148:
When are expense reports due?
(A) Monday
(B) Wednesday
(C) Friday
(D) Sunday
Answer: C
TXT;

        $parsed = $parser->parseLines(explode("\n", $rawText), 'READING');
        $this->assertCount(2, $parsed);
        $this->assertSame(147, $parsed[0]['question_number']);
        $this->assertSame('What is the purpose of the notice?', $parsed[0]['content']);
        $this->assertStringContainsString('1. Please lock the storage room before leaving.', (string) $parsed[0]['stimulus_text']);
        $this->assertSame(148, $parsed[1]['question_number']);
        $this->assertSame('When are expense reports due?', $parsed[1]['content']);
    }

    public function test_admin_can_quick_edit_question_with_unset_difficulty_inside_test(): void
    {
        $admin = User::factory()->admin()->create();
        $this->seedSectionsAndBlueprints();
        $lrBlueprint = TestBlueprint::where('exam_type', 'TOEIC_LR')->firstOrFail();

        $testForm = TestForm::create([
            'blueprint_id' => $lrBlueprint->blueprint_id,
            'title' => 'Quick Edit UNSET Difficulty Test',
        ]);

        $this->attachQuestionsByCode($testForm->test_form_id, 'PART5', 1, Question::STATUS_ACTIVE);
        $questionId = (int) DB::table('test_form_question')
            ->where('test_form_id', $testForm->test_form_id)
            ->value('question_id');

        $response = $this->actingAs($admin)->put(route('admin.tests.questions.update', [
            'id' => $testForm->test_form_id,
            'questionId' => $questionId,
        ]), [
            'content' => 'Imported question edited while difficulty remains UNSET.',
            'difficulty' => 'UNSET',
            'opt_a' => 'Option A',
            'opt_b' => 'Option B',
            'opt_c' => 'Option C',
            'opt_d' => 'Option D',
            'correct_option' => 'B',
        ]);

        $response->assertRedirect(route('admin.tests.show', $testForm->test_form_id));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('questions', [
            'question_id' => $questionId,
            'difficulty' => 'UNSET',
        ]);
    }

    public function test_speaking_11_questions_import_attaches_all_11_without_header_pollution_or_resume_bug(): void
    {
        $this->seedSectionsAndBlueprints();
        $service = app(TestFormService::class);
        $parser = app(WordQuestionImportService::class);

        $speakingTest = $service->create([
            'title' => 'TEST-011 Speaking Regression',
            'exam_type' => TestForm::EXAM_TYPE_SPEAKING,
        ]);

        $rawSpeaking = <<<'TXT'
TOEIC Speaking Test câu 1, 2
Question 1: Welcome, everyone. Thanks for coming to this meeting.
Question 2: Members of the press, Dawson Technologies is announcing a product demonstration.
TOEIC Speaking Test câu 3, 4
[SPLIT_IMAGE_CELL]
[IMAGE_URL: storage/uploads/pic1.jpg]
[SPLIT_IMAGE_CELL]
[IMAGE_URL: storage/uploads/pic2.jpg]
Đề TOEIC Speaking câu 5, 6, 7
Imagine that a British marketing firm is doing research in your country.
Question 5: How much do you pay for your cell phone, and how old is it?
Question 6: What's the most important feature you look at when you choose a new cell phone?
Question 7: What do you like or dislike about using your cell phone and why?
TOEIC Speaking Test câu 8, 9, 10
Seminar Schedule: 9:00 AM Opening | 1:00 PM Résumé Writing
What day is the seminar on, and when does it start?
I think I saw that the session on résumé writing was scheduled to start right after lunch. That's right, isn't it?
I have an important appointment in the morning that day, so I'll probably only be able to attend in the afternoon. What sessions will I be able to participate in?
TOEIC Speaking Test câu 11
Question 11: Many people argue that students should have to participate in physical activities at school. Which point of view do you agree with and why?
TXT;

        $parsed = $parser->parseLines(explode("\n", $rawSpeaking), 'SPEAKING');
        $this->assertCount(11, $parsed);

        $partCounts = array_count_values(array_column($parsed, 'part_code'));
        $this->assertSame([
            'SPK_T1' => 2,
            'SPK_T2' => 2,
            'SPK_T3' => 3,
            'SPK_T4' => 3,
            'SPK_T5' => 1,
        ], $partCounts);

        // Không có tiêu đề nào bị nuốt vào explanation
        foreach ($parsed as $q) {
            $this->assertStringNotContainsString('câu 3, 4', (string) ($q['explanation'] ?? ''));
            $this->assertStringNotContainsString('câu 5, 6, 7', (string) ($q['explanation'] ?? ''));
            $this->assertStringNotContainsString('câu 8, 9, 10', (string) ($q['explanation'] ?? ''));
            $this->assertStringNotContainsString('résumé writing', (string) ($q['explanation'] ?? ''));
        }

        // Persist và gắn thẳng vào đề Speaking -> phải gắn đủ 11/11, không rớt test_id
        $ids = $parser->persistQuestionsAndReturnIds($parsed, 'ACTIVE', null, $speakingTest->test_form_id);
        $this->assertCount(11, $ids);
        $attachResult = $parser->getLastAttachResult();
        $this->assertSame(11, $attachResult['attached']);
        $this->assertSame(0, $attachResult['rejected_limit']);

        $detail = $service->detail($speakingTest->fresh());
        $this->assertSame(11, $detail['current_questions']);
        $this->assertSame(TestForm::STATUS_COMPLETED, $detail['status']);
    }

    private function seedSectionsAndBlueprints(): void
    {
        app(TestFormService::class)->ensureStandardSectionsExist();

        TestBlueprint::updateOrCreate(
            ['exam_type' => 'TOEIC_LR'],
            ['name' => 'TOEIC Listening & Reading (Full Test - 200 câu)', 'version' => '2026', 'total_time' => 120, 'status' => 'ACTIVE']
        );
        TestBlueprint::updateOrCreate(
            ['exam_type' => 'TOEIC_SW'],
            ['name' => 'TOEIC Speaking & Writing (Full Test - 19 câu)', 'version' => '2026', 'total_time' => 80, 'status' => 'ACTIVE']
        );
        TestBlueprint::updateOrCreate(
            ['exam_type' => 'TOEIC_WRITING'],
            ['name' => 'TOEIC Writing (8 câu - 3 phần chuẩn ETS)', 'version' => '2026', 'total_time' => 60, 'status' => 'ACTIVE']
        );
    }

    private function createStandaloneQuestion(string $sectionCode, string $status = Question::STATUS_ACTIVE): int
    {
        $sectionId = (int) ExamSection::where('code', $sectionCode)->value('section_id');

        return (int) DB::table('questions')->insertGetId([
            'section_id' => $sectionId,
            'question_type_id' => 1,
            'content' => "Sample Question {$sectionCode} ".uniqid(),
            'difficulty' => 'MEDIUM',
            'points' => 1.00,
            'status' => $status,
            'created_at' => now(),
        ]);
    }

    private function attachQuestionsByCode(int $testFormId, string $sectionCode, int $count, string $status = Question::STATUS_ACTIVE): void
    {
        $maxSeq = (int) DB::table('test_form_question')->where('test_form_id', $testFormId)->max('sequence_no');

        for ($i = 1; $i <= $count; $i++) {
            $questionId = $this->createStandaloneQuestion($sectionCode, $status);

            DB::table('test_form_question')->insert([
                'test_form_id' => $testFormId,
                'question_id' => $questionId,
                'sequence_no' => ++$maxSeq,
                'point' => 1.00,
            ]);
        }
    }

    public function test_spk_t2_questions_with_same_content_but_different_media_url_are_not_flagged_as_duplicates_and_exact_duplicates_are_cleaned_up(): void
    {
        $this->seedSectionsAndBlueprints();
        $admin = User::factory()->admin()->create();
        $service = app(TestFormService::class);

        $testForm = $service->create([
            'title' => 'Speaking Task 2 Fingerprint Test',
            'exam_type' => TestForm::EXAM_TYPE_SPEAKING,
        ]);

        $spkT2SectionId = (int) ExamSection::where('code', 'SPK_T2')->value('section_id');
        $prompt = 'Describe the picture on your screen in as much detail as you can.';

        $set1Id = (int) DB::table('question_set')->insertGetId([
            'title' => 'SPK_T2 Image 1',
            'media_url' => '/storage/import/image1.png',
        ]);
        $set2Id = (int) DB::table('question_set')->insertGetId([
            'title' => 'SPK_T2 Image 2',
            'media_url' => '/storage/import/image2.png',
        ]);

        $q1Id = (int) DB::table('questions')->insertGetId([
            'section_id' => $spkT2SectionId,
            'question_set_id' => $set1Id,
            'question_type_id' => 1,
            'content' => $prompt,
            'difficulty' => 'MEDIUM',
            'points' => 1.00,
            'status' => Question::STATUS_ACTIVE,
            'created_at' => now(),
        ]);
        $q2Id = (int) DB::table('questions')->insertGetId([
            'section_id' => $spkT2SectionId,
            'question_set_id' => $set2Id,
            'question_type_id' => 1,
            'content' => $prompt,
            'difficulty' => 'MEDIUM',
            'points' => 1.00,
            'status' => Question::STATUS_ACTIVE,
            'created_at' => now(),
        ]);

        DB::table('test_form_question')->insert([
            ['test_form_id' => $testForm->test_form_id, 'question_id' => $q1Id, 'sequence_no' => 1, 'point' => 1.00],
            ['test_form_id' => $testForm->test_form_id, 'question_id' => $q2Id, 'sequence_no' => 2, 'point' => 1.00],
        ]);

        // 1. Hai câu SPK_T2 cùng content nhưng khác media_url => 0 cảnh báo trùng lặp, cleanup không xóa nhầm
        $detail = $service->detail($testForm);
        $this->assertSame([], $detail['quality']['duplicate_ids']);

        $cleanupNoop = $this->actingAs($admin)->post(route('admin.tests.cleanup-issues', $testForm->test_form_id));
        $cleanupNoop->assertRedirect(route('admin.tests.show', $testForm->test_form_id));
        $this->assertSame(2, $testForm->questions()->count());

        // 2. Thêm câu thứ 3 giống hệt cả content lẫn media_url (/storage/import/image1.png) => bắt trùng chuẩn xác & xóa đúng 1 câu
        $set1DupId = (int) DB::table('question_set')->insertGetId([
            'title' => 'SPK_T2 Image 1 Duplicate',
            'media_url' => '/storage/import/image1.png',
        ]);
        $q3DupId = (int) DB::table('questions')->insertGetId([
            'section_id' => $spkT2SectionId,
            'question_set_id' => $set1DupId,
            'question_type_id' => 1,
            'content' => $prompt,
            'difficulty' => 'MEDIUM',
            'points' => 1.00,
            'status' => Question::STATUS_ACTIVE,
            'created_at' => now(),
        ]);
        DB::table('test_form_question')->insert([
            'test_form_id' => $testForm->test_form_id,
            'question_id' => $q3DupId,
            'sequence_no' => 3,
            'point' => 1.00,
        ]);

        $detailWithDup = $service->detail($testForm);
        $this->assertSame([$q3DupId], $detailWithDup['quality']['duplicate_ids']);

        $cleanupResp = $this->actingAs($admin)->post(route('admin.tests.cleanup-issues', $testForm->test_form_id));
        $cleanupResp->assertRedirect(route('admin.tests.show', $testForm->test_form_id));
        $this->assertSame(2, $testForm->questions()->count());
        $this->assertDatabaseHas('test_form_question', ['test_form_id' => $testForm->test_form_id, 'question_id' => $q1Id]);
        $this->assertDatabaseHas('test_form_question', ['test_form_id' => $testForm->test_form_id, 'question_id' => $q2Id]);
        $this->assertDatabaseMissing('test_form_question', ['test_form_id' => $testForm->test_form_id, 'question_id' => $q3DupId]);
    }
}
