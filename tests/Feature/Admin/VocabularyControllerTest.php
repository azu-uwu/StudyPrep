<?php

namespace Tests\Feature\Admin;

use App\Models\Topic;
use App\Models\User;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
            'image_url' => 'https://example.test/extraordinarily.jpg',
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
        $response->assertSee('https://example.test/extraordinarily.jpg');
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
            'part_of_speech' => 'Adverb (Phó từ / Trạng từ)|800+',
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
            'image_url' => 'https://example.test/substantial.jpg',
        ]);

        $response->assertRedirect(route('admin.vocabularies.index'));
        $this->assertDatabaseHas('vocabularies', [
            'word' => 'substantial',
            'part_of_speech' => 'Adj|800+',
            'image_url' => 'https://example.test/substantial.jpg',
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
            'user_id' => null,
        ]);
    }

    public function test_admin_topic_list_excludes_personal_student_topics(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();

        Topic::create([
            'user_id' => null,
            'name' => 'System TOEIC Core',
        ]);
        Topic::create([
            'user_id' => $student->user_id,
            'name' => 'Student Personal Deck',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.vocabularies.index'));

        $response->assertOk();
        $response->assertViewHas('topics', function ($topics): bool {
            return $topics->count() === 1 && $topics->first()->name === 'System TOEIC Core';
        });
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
        $response->assertSee('Nhập từ vựng hàng loạt');
        $response->assertSee('Chọn tệp từ vựng cần nhập');
        $response->assertSee('Cấu hình Bộ thẻ &amp; Xử lý trùng lặp', false);
        $response->assertSee('Tải file Excel/CSV mẫu');
        $response->assertSee('Tải lên &amp; Nhập từ vựng', false);
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

    public function test_admin_can_import_csv_and_render_imported_table_in_view(): void
    {
        $admin = User::factory()->admin()->create();
        $topic = Topic::create([
            'user_id' => null,
            'name' => 'ETS TOEIC Import Test',
            'description' => 'Test bộ thẻ',
        ]);

        $csv = "Word,POS,IPA,Definition,Meaning,Example,Translation,Level\naccelerate,Verb,/əkˈsel.ə.reɪt/,Increase speed,Tăng tốc độ,The firm accelerated growth,Công ty tăng trưởng nhanh,750+";
        $file = UploadedFile::fake()->createWithContent('import.csv', $csv);

        $response = $this->actingAs($admin)->post(route('admin.vocabularies.import'), [
            'file' => $file,
            'topic_id' => $topic->topic_id,
            'import_mode' => 'skip',
            'return_to_import_view' => 1,
        ]);

        $response->assertRedirect(route('admin.vocabularies.import-view'));
        $response->assertSessionHas('imported_ids');
        $response->assertSessionHas('success');

        $viewResponse = $this->actingAs($admin)->get(route('admin.vocabularies.import-view'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('accelerate');
        $viewResponse->assertSee('/əkˈsel.ə.reɪt/');
        $viewResponse->assertSee('Tăng tốc độ');
        $viewResponse->assertSee('Danh sách từ vựng vừa Import');
    }

    public function test_admin_can_quick_edit_imported_vocab_with_redirect_back(): void
    {
        $admin = User::factory()->admin()->create();
        $topic = Topic::create([
            'user_id' => null,
            'name' => 'Topic Quick Edit',
            'description' => 'Mô tả',
        ]);
        $vocab = Vocabulary::create([
            'word' => 'quick_edit_me',
            'meaning_vi' => 'Nghĩa ban đầu',
            'topic_id' => $topic->topic_id,
        ]);

        $response = $this->actingAs($admin)->from(route('admin.vocabularies.import-view'))->put(route('admin.vocabularies.update', $vocab->vocabulary_id), [
            'word' => 'quick_edited_done',
            'meaning_vi' => 'Nghĩa đã sửa',
            'topic_id' => $topic->topic_id,
            'redirect_back' => 1,
        ]);

        $response->assertRedirect(route('admin.vocabularies.import-view'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('vocabularies', [
            'vocabulary_id' => $vocab->vocabulary_id,
            'word' => 'quick_edited_done',
        ]);
    }

    public function test_admin_can_quick_delete_imported_vocab_with_redirect_back(): void
    {
        $admin = User::factory()->admin()->create();
        $topic = Topic::create([
            'user_id' => null,
            'name' => 'Topic Quick Del',
            'description' => 'Mô tả',
        ]);
        $vocab = Vocabulary::create([
            'word' => 'quick_delete_me',
            'meaning_vi' => 'Nghĩa xóa',
            'topic_id' => $topic->topic_id,
        ]);

        $response = $this->actingAs($admin)->from(route('admin.vocabularies.import-view'))->delete(route('admin.vocabularies.destroy', $vocab->vocabulary_id), [
            'redirect_back' => 1,
        ]);

        $response->assertRedirect(route('admin.vocabularies.import-view'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('vocabularies', [
            'vocabulary_id' => $vocab->vocabulary_id,
        ]);
    }

    public function test_blocks_duplicate_word_in_same_topic_allows_different_topic_and_renders_flash_alerts(): void
    {
        $admin = User::factory()->admin()->create();
        $topicA = Topic::create(['user_id' => null, 'name' => 'Contracts', 'description' => 'System topic A']);
        $topicB = Topic::create(['user_id' => null, 'name' => 'Marketing', 'description' => 'System topic B']);

        // Tạo lần 1 thành công & kiểm tra Flash Alert hiển thị thực tế trên HTML
        $firstResp = $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.vocabularies.store'), [
                'word' => 'Stipulate',
                'meaning_vi' => 'Quy định rõ trong hợp đồng',
                'topic_id' => $topicA->topic_id,
            ]);
        $firstResp->assertOk();
        $firstResp->assertSee('Đã thêm từ vựng mới');
        $firstResp->assertSee('Stipulate');

        // Tạo trùng từ (không phân biệt hoa thường) trong cùng Topic A -> bị chặn
        $dupResp = $this->actingAs($admin)->post(route('admin.vocabularies.store'), [
            'word' => '  stipulate  ',
            'meaning_vi' => 'Nghĩa trùng',
            'topic_id' => $topicA->topic_id,
        ]);
        $dupResp->assertSessionHasErrors('word');
        $this->assertSame(1, Vocabulary::query()->where('topic_id', $topicA->topic_id)->count());

        // Cùng từ đó nhưng ở Topic B khác -> cho phép
        $otherTopicResp = $this->actingAs($admin)->post(route('admin.vocabularies.store'), [
            'word' => 'stipulate',
            'meaning_vi' => 'Quy định điều khoản tiếp thị',
            'topic_id' => $topicB->topic_id,
        ]);
        $otherTopicResp->assertRedirect(route('admin.vocabularies.index'));
        $this->assertSame(1, Vocabulary::query()->where('topic_id', $topicB->topic_id)->count());
    }

    public function test_vocabularies_index_renders_edit_topic_options_real_pagination_and_no_mobile_preview_bar(): void
    {
        $admin = User::factory()->admin()->create();
        $topicA = Topic::create(['user_id' => null, 'name' => 'Business Core', 'description' => 'System A']);
        $topicB = Topic::create(['user_id' => null, 'name' => 'Travel Core', 'description' => 'System B']);

        for ($i = 1; $i <= 10; $i++) {
            Vocabulary::create([
                'word' => "word_real_{$i}",
                'meaning_vi' => "Nghĩa {$i}",
                'topic_id' => $topicA->topic_id,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.vocabularies.index', ['per_page' => 25]));

        $response->assertOk();
        $response->assertSee('edit_vocab_topic', false);
        $response->assertSee('Business Core');
        $response->assertSee('Travel Core');
        $response->assertDontSee('1,845');
        $response->assertDontSee('Xem trước thẻ:');
        $response->assertDontSee('Mở xem trước');
        // Thanh phân trang luôn hiển thị kể cả khi chỉ có 1 trang (10 bản ghi / 25 dòng)
        $response->assertSee('user-pagination', false);
        $response->assertSee('<span class="page-btn active">1</span>', false);
        $response->assertSee('aria-disabled="true"', false);
        $response->assertDontSee('<th>HỌC VIÊN</th>', false);
        $response->assertSee('Thao tác khác');
        $response->assertSee('Thêm từ vựng');
        $response->assertSee('repeat(4, minmax(0, 1fr))', false);
        $response->assertSee('modalPrevVocabBtn', false);
        $response->assertSee('modalNextVocabBtn', false);
    }
}
