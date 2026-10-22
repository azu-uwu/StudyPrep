<?php

namespace Tests\Feature\Admin;

use App\Models\Topic;
use App\Models\User;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class VocabularyControllerTest extends TestCase
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
        $response = $this->get(route('admin.vocabularies.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_vocabulary_management(): void
    {
        $student = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($student)->get(route('admin.vocabularies.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_vocabulary_management_screen(): void
    {
        $admin = User::factory()->admin()->create();

        $topic = Topic::create([
            'user_id' => $admin->user_id,
            'name' => 'Finance & Budget',
            'description' => 'Tài chính doanh nghiệp',
        ]);

        Vocabulary::create([
            'topic_id' => $topic->topic_id,
            'word' => 'extraordinarily',
            'part_of_speech' => 'Adv|750+',
            'pronunciation' => '/ɪkˈstrɔːr.dən.er.əl.i/',
            'meaning_vi' => 'Đặc biệt, phi thường || To a very unusual or remarkable degree',
            'example_en' => 'The quarter report demonstrated an extraordinarily high return on investment.',
            'example_vi' => 'Báo cáo quý tăng trưởng vượt trội...',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.vocabularies.index'));

        $response->assertOk();
        $response->assertViewIs('admin.vocabularies.index');
        $response->assertSee('Quản lý Từ vựng & Flashcards');
        $response->assertSee('TỪ HỆ THỐNG');
        $response->assertSee('BỘ THẺ FLASHCARDS');
        $response->assertSee('Tổng số từ vựng');
        $response->assertSee('Bộ thẻ Flashcards mở');
        $response->assertSee('Xem trước Flashcard');
        $response->assertSee('LIVE PREVIEW');
        $response->assertSee('extraordinarily');
        $response->assertSee('Finance & Budget');
    }

    public function test_admin_can_search_and_filter_vocabularies(): void
    {
        $admin = User::factory()->admin()->create();

        $topic = Topic::create([
            'user_id' => $admin->user_id,
            'name' => 'Contracts & Finance',
        ]);

        Vocabulary::create([
            'topic_id' => $topic->topic_id,
            'word' => 'fiscal quarter',
            'part_of_speech' => 'Noun Phrase|800+',
            'pronunciation' => '/ˈfɪs.kəl ˈkwɔːr.tər/',
            'meaning_vi' => 'Quý tài chính',
        ]);

        Vocabulary::create([
            'topic_id' => $topic->topic_id,
            'word' => 'commute',
            'part_of_speech' => 'Verb|450+',
            'pronunciation' => '/kəˈmjuːt/',
            'meaning_vi' => 'Đi lại hàng ngày',
        ]);

        // Search for 'fiscal'
        $response = $this->actingAs($admin)->get(route('admin.vocabularies.index', ['search' => 'fiscal']));
        $response->assertOk();
        $response->assertSee('fiscal quarter');
        $response->assertDontSee('commute');
    }

    public function test_admin_can_view_create_vocabulary_screen(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.vocabularies.create'));

        $response->assertOk();
        $response->assertViewIs('admin.vocabularies.create');
        $response->assertSee('Thêm từ vựng & Thẻ học mới', false);
        $response->assertSee('1. Thông tin cơ bản');
        $response->assertSee('2. Nghĩa, Giải thích');
        $response->assertSee('Mô phỏng Thẻ học (Live Preview)');
        $response->assertSee('Xuất bản vào bộ thẻ');
        $response->assertSee('Lưu bản nháp');
    }

    public function test_validation_errors_when_creating_vocabulary_without_required_fields(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.vocabularies.store'), []);

        $response->assertSessionHasErrors(['word', 'meaning_vi']);
    }

    public function test_admin_can_save_vocabulary_as_draft(): void
    {
        $admin = User::factory()->admin()->create();

        $topic = Topic::create([
            'user_id' => $admin->user_id,
            'name' => 'ETS 2024 Business & Finance',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.vocabularies.store'), [
            'word' => 'substantially',
            'meaning_vi' => 'Đáng kể, về căn bản',
            'definition_en' => 'To a great or significant degree',
            'part_of_speech' => 'Adverb (Phó từ / Trạng từ)',
            'level' => 'Target 800+ (High B2 / C1)',
            'pronunciation' => '/səbˈstæn.ʃəl.i/',
            'topic_id' => $topic->topic_id,
            'action_type' => 'draft',
        ]);

        $response->assertRedirect(route('admin.vocabularies.index'));
        $response->assertSessionHas('info');
        $this->assertDatabaseHas('vocabularies', [
            'word' => 'substantially',
        ]);
    }

    public function test_admin_can_create_new_vocabulary(): void
    {
        $admin = User::factory()->admin()->create();

        $topic = Topic::create([
            'user_id' => $admin->user_id,
            'name' => 'Office & Workplaces',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.vocabularies.store'), [
            'word' => 'substantial',
            'meaning_vi' => 'Đáng kể, trọng yếu',
            'definition_en' => 'Large in amount, value, or importance',
            'part_of_speech' => 'Adj',
            'level' => '800+',
            'pronunciation' => '/səbˈstæn.ʃəl/',
            'topic_id' => $topic->topic_id,
            'example_en' => 'The corporation gained a substantial market share.',
            'example_vi' => 'Tập đoàn đạt thị phần đáng kể.',
        ]);

        $response->assertRedirect(route('admin.vocabularies.index'));
        $this->assertDatabaseHas('vocabularies', [
            'word' => 'substantial',
            'part_of_speech' => 'Adj|800+',
        ]);
    }

    public function test_admin_can_create_new_topic(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.vocabularies.topics.store'), [
            'name' => 'ETS 2024 Test 3',
            'description' => 'Bộ đề thi chuẩn',
        ]);

        $response->assertRedirect(route('admin.vocabularies.index'));
        $this->assertDatabaseHas('topics', [
            'name' => 'ETS 2024 Test 3',
        ]);
    }

    public function test_admin_can_delete_vocabulary(): void
    {
        $admin = User::factory()->admin()->create();

        $topic = Topic::create([
            'user_id' => $admin->user_id,
            'name' => 'General Topic',
        ]);

        $vocab = Vocabulary::create([
            'topic_id' => $topic->topic_id,
            'word' => 'accommodate',
            'meaning_vi' => 'Chứa đựng, đáp ứng',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.vocabularies.destroy', $vocab));

        $response->assertRedirect(route('admin.vocabularies.index'));
        $this->assertDatabaseMissing('vocabularies', [
            'vocabulary_id' => $vocab->vocabulary_id,
        ]);
    }

    public function test_admin_can_bulk_delete_vocabularies(): void
    {
        $admin = User::factory()->admin()->create();

        $topic = Topic::create([
            'user_id' => $admin->user_id,
            'name' => 'General Topic',
        ]);

        $v1 = Vocabulary::create(['topic_id' => $topic->topic_id, 'word' => 'word_one', 'meaning_vi' => 'Từ một']);
        $v2 = Vocabulary::create(['topic_id' => $topic->topic_id, 'word' => 'word_two', 'meaning_vi' => 'Từ hai']);

        $response = $this->actingAs($admin)->post(route('admin.vocabularies.bulk-action'), [
            'action' => 'delete',
            'selected_ids' => [$v1->vocabulary_id, $v2->vocabulary_id],
        ]);

        $response->assertRedirect(route('admin.vocabularies.index'));
        $this->assertDatabaseMissing('vocabularies', ['vocabulary_id' => $v1->vocabulary_id]);
        $this->assertDatabaseMissing('vocabularies', ['vocabulary_id' => $v2->vocabulary_id]);
    }

    public function test_admin_can_export_vocabularies_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $topic = Topic::create([
            'user_id' => $admin->user_id,
            'name' => 'General Topic',
        ]);

        Vocabulary::create([
            'topic_id' => $topic->topic_id,
            'word' => 'renovation',
            'meaning_vi' => 'Sự đổi mới, trùng tu',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.vocabularies.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    public function test_admin_can_view_bulk_import_screen(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.vocabularies.import-view'));

        $response->assertOk();
        $response->assertViewIs('admin.vocabularies.import');
        $response->assertSee('Nhập từ vựng TOEIC hàng loạt từ file');
        $response->assertSee('Tài nguyên dữ liệu đầu vào');
        $response->assertSee('Trạng thái ánh xạ trường dữ liệu');
        $response->assertSee('Cấu hình bộ thẻ đích đến');
        $response->assertSee('Bắt đầu Nhập 242 từ vựng vào hệ thống');
    }

    public function test_admin_can_download_sample_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.vocabularies.sample-csv'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('Từ vựng (Word)', $response->streamedContent());
    }

    public function test_admin_can_confirm_import_batch(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.vocabularies.import-confirm'));

        $response->assertRedirect(route('admin.vocabularies.index'));
        $response->assertSessionHas('info');
        $this->assertDatabaseHas('vocabularies', [
            'word' => 'negotiate',
        ]);
    }
}
