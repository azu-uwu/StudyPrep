<?php

namespace Tests\Feature\Admin;

use App\Models\ExamSection;
use App\Models\Question;
use App\Models\TestBlueprint;
use App\Models\TestForm;
use App\Models\User;
use App\Services\TestFormService;
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
        $saveResp->assertRedirect(route('admin.tests.show', $testForm->test_form_id));

        $detachResp = $this->actingAs($admin)->delete(route('admin.tests.questions.detach', [
            'id' => $testForm->test_form_id,
            'questionId' => $questionId,
        ]));
        $detachResp->assertRedirect(route('admin.tests.show', $testForm->test_form_id));
        $this->assertEquals(0, $testForm->questions()->count());
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
}
