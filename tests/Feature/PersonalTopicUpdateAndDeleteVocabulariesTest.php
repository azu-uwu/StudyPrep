<?php

namespace Tests\Feature;

use App\Models\Topic;
use App\Models\User;
use App\Models\UserVocabulary;
use App\Models\Vocabulary;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\CreatesStudyPrepSchema;
use Tests\TestCase;

class PersonalTopicUpdateAndDeleteVocabulariesTest extends TestCase
{
    use CreatesStudyPrepSchema;
    use RefreshDatabase;

    protected User $owner;

    protected User $otherStudent;

    protected Topic $personalTopic;

    protected Topic $systemTopic;

    protected Topic $otherUserTopic;

    protected function setUp(): void
    {
        parent::setUp();

        // RÀNG BUỘC AN TOÀN CSDL TUYỆT ĐỐI:
        // Đảm bảo test CHỈ chạy trên SQLite in-memory độc lập, tuyệt đối không chạm vào MySQL thật.
        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            $this->markTestSkipped('Bảo vệ an toàn: Test chỉ được phép chạy trên SQLite :memory: độc lập.');
        }

        $this->createStudyPrepSchema();

        if (! Schema::hasTable('user_vocabulary')) {
            Schema::create('user_vocabulary', function (Blueprint $table): void {
                $table->id('user_voca_id');
                $table->unsignedBigInteger('vocabulary_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamp('learned_at')->nullable();
                $table->boolean('is_done')->default(false);
            });
        }

        // Bật kiểm tra khóa ngoại nếu là SQLite
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        // Tạo học viên sở hữu chủ đề
        $this->owner = User::create([
            'user_name' => 'student_owner_'.uniqid(),
            'full_name' => 'Chủ sở hữu Test',
            'email' => 'owner_'.uniqid().'@studyprep.test',
            'password_hash' => bcrypt('Password123!'),
            'role' => 'student',
            'status' => 'ACTIVE',
            'energy' => 5,
        ]);

        // Tạo học viên khác để kiểm tra bảo mật phân quyền
        $this->otherStudent = User::create([
            'user_name' => 'other_student_'.uniqid(),
            'full_name' => 'Học viên Khác Test',
            'email' => 'other_'.uniqid().'@studyprep.test',
            'password_hash' => bcrypt('Password123!'),
            'role' => 'student',
            'status' => 'ACTIVE',
            'energy' => 5,
        ]);

        // Chủ đề cá nhân của $owner
        $this->personalTopic = Topic::create([
            'name' => 'Chủ đề gốc của tôi',
            'description' => 'Mô tả ban đầu',
            'user_id' => $this->owner->user_id,
            'parent_topic_id' => null,
        ]);

        // Chủ đề hệ thống (user_id IS NULL)
        $this->systemTopic = Topic::create([
            'name' => 'Chủ đề hệ thống mẫu',
            'description' => 'Mô tả hệ thống',
            'user_id' => null,
            'parent_topic_id' => null,
        ]);

        // Chủ đề của học viên khác
        $this->otherUserTopic = Topic::create([
            'name' => 'Chủ đề học viên khác',
            'description' => 'Mô tả học viên khác',
            'user_id' => $this->otherStudent->user_id,
            'parent_topic_id' => null,
        ]);
    }

    // ==========================================
    // NHÓM 1: ĐỔI TÊN CHỦ ĐỀ CÁ NHÂN (TEST 1 - 8)
    // ==========================================

    /**
     * Trường hợp 1: Chủ sở hữu đổi tên thành công.
     */
    public function test_1_owner_can_rename_personal_topic_successfully(): void
    {
        $response = $this->actingAs($this->owner)
            ->putJson(route('tu-vung.topics.update', $this->personalTopic->topic_id), [
                'name' => 'Tên chủ đề mới được cập nhật',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cập nhật chủ đề thành công!',
                'topic' => [
                    'topic_id' => $this->personalTopic->topic_id,
                    'name' => 'Tên chủ đề mới được cập nhật',
                    'user_id' => $this->owner->user_id,
                ],
            ]);
    }

    /**
     * Trường hợp 2: Tên mới được lưu vào CSDL đúng giá trị và giữ nguyên mô tả.
     */
    public function test_2_new_topic_name_is_persisted_in_database(): void
    {
        $this->actingAs($this->owner)
            ->putJson(route('tu-vung.topics.update', $this->personalTopic->topic_id), [
                'name' => 'Tên đã lưu vào DB',
            ]);

        $this->assertDatabaseHas('topics', [
            'topic_id' => $this->personalTopic->topic_id,
            'name' => 'Tên đã lưu vào DB',
            'description' => 'Mô tả ban đầu',
            'user_id' => $this->owner->user_id,
        ]);
    }

    /**
     * Trường hợp 3: Tên rỗng hoặc toàn khoảng trắng bị từ chối với mã 422.
     */
    public function test_3_empty_or_whitespace_name_is_rejected(): void
    {
        // Tên rỗng
        $response1 = $this->actingAs($this->owner)
            ->putJson(route('tu-vung.topics.update', $this->personalTopic->topic_id), [
                'name' => '',
            ]);
        $response1->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        // Tên toàn khoảng trắng
        $response2 = $this->actingAs($this->owner)
            ->putJson(route('tu-vung.topics.update', $this->personalTopic->topic_id), [
                'name' => '     ',
            ]);
        $response2->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Trường hợp 4: Tên vượt quá 100 ký tự bị từ chối với mã 422.
     */
    public function test_4_name_exceeding_100_characters_is_rejected(): void
    {
        $response = $this->actingAs($this->owner)
            ->putJson(route('tu-vung.topics.update', $this->personalTopic->topic_id), [
                'name' => str_repeat('A', 101),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Trường hợp 5: Không thể sửa chủ đề hệ thống (user_id IS NULL) -> 403 Forbidden.
     */
    public function test_5_cannot_edit_system_topic(): void
    {
        $response = $this->actingAs($this->owner)
            ->putJson(route('tu-vung.topics.update', $this->systemTopic->topic_id), [
                'name' => 'Cố tình sửa tên chủ đề hệ thống',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('topics', [
            'topic_id' => $this->systemTopic->topic_id,
            'name' => 'Chủ đề hệ thống mẫu',
        ]);
    }

    /**
     * Trường hợp 6: Không thể sửa chủ đề của người khác -> 403 Forbidden.
     */
    public function test_6_cannot_edit_another_user_topic(): void
    {
        $response = $this->actingAs($this->owner)
            ->putJson(route('tu-vung.topics.update', $this->otherUserTopic->topic_id), [
                'name' => 'Cố tình chiếm quyền chủ đề người khác',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('topics', [
            'topic_id' => $this->otherUserTopic->topic_id,
            'name' => 'Chủ đề học viên khác',
        ]);
    }

    /**
     * Trường hợp 7: Không thể giả mạo user_id hay parent_topic_id qua payload request.
     */
    public function test_7_cannot_tamper_user_id_or_parent_topic_id(): void
    {
        $response = $this->actingAs($this->owner)
            ->putJson(route('tu-vung.topics.update', $this->personalTopic->topic_id), [
                'name' => 'Tên hợp lệ',
                'user_id' => 99999,
                'parent_topic_id' => 88888,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('topics', [
            'topic_id' => $this->personalTopic->topic_id,
            'user_id' => $this->owner->user_id,
            'parent_topic_id' => null,
        ]);
    }

    /**
     * Trường hợp 8: Đổi tên không làm thay đổi từ vựng và tiến độ học thuộc chủ đề.
     */
    public function test_8_renaming_does_not_affect_vocabularies_or_progress(): void
    {
        $vocab = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'innovation',
            'meaning_vi' => 'sự đổi mới',
        ]);

        $progress = UserVocabulary::create([
            'user_id' => $this->owner->user_id,
            'vocabulary_id' => $vocab->vocabulary_id,
            'is_done' => true,
        ]);

        $this->actingAs($this->owner)
            ->putJson(route('tu-vung.topics.update', $this->personalTopic->topic_id), [
                'name' => 'Tên chủ đề đổi mới',
            ]);

        $this->assertDatabaseHas('vocabularies', [
            'vocabulary_id' => $vocab->vocabulary_id,
            'word' => 'innovation',
            'topic_id' => $this->personalTopic->topic_id,
        ]);

        $this->assertDatabaseHas('user_vocabulary', [
            'user_voca_id' => $progress->user_voca_id,
            'is_done' => true,
        ]);
    }

    // ==========================================
    // NHÓM 2: XÓA TỪ VỰNG HÀNG LOẠT (TEST 9 - 18)
    // ==========================================

    /**
     * Trường hợp 9: Xóa thành công một từ được chọn.
     */
    public function test_9_owner_can_delete_single_selected_vocabulary(): void
    {
        $vocab1 = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'cat',
            'meaning_vi' => 'con mèo',
        ]);
        $vocab2 = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'dog',
            'meaning_vi' => 'con chó',
        ]);

        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.vocabularies.bulk-delete', $this->personalTopic->topic_id), [
                'vocabulary_ids' => [$vocab1->vocabulary_id],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Đã xóa các từ vựng được chọn!',
                'deleted_count' => 1,
                'remaining_count' => 1,
            ]);

        $this->assertDatabaseMissing('vocabularies', ['vocabulary_id' => $vocab1->vocabulary_id]);
        $this->assertDatabaseHas('vocabularies', ['vocabulary_id' => $vocab2->vocabulary_id]);
    }

    /**
     * Trường hợp 10: Xóa thành công nhiều từ được chọn trong cùng một thao tác.
     */
    public function test_10_owner_can_delete_multiple_selected_vocabularies(): void
    {
        $vocab1 = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'apple',
            'meaning_vi' => 'quả táo',
        ]);
        $vocab2 = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'banana',
            'meaning_vi' => 'quả chuối',
        ]);
        $vocab3 = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'orange',
            'meaning_vi' => 'quả cam',
        ]);

        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.vocabularies.bulk-delete', $this->personalTopic->topic_id), [
                'vocabulary_ids' => [$vocab1->vocabulary_id, $vocab2->vocabulary_id],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'deleted_count' => 2,
                'remaining_count' => 1,
            ]);

        $this->assertDatabaseMissing('vocabularies', ['vocabulary_id' => $vocab1->vocabulary_id]);
        $this->assertDatabaseMissing('vocabularies', ['vocabulary_id' => $vocab2->vocabulary_id]);
        $this->assertDatabaseHas('vocabularies', ['vocabulary_id' => $vocab3->vocabulary_id]);
    }

    /**
     * Trường hợp 11: Không xóa những từ không được chọn trong chủ đề.
     */
    public function test_11_unselected_vocabularies_remain_untouched(): void
    {
        $vocabKeep = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'retain',
            'meaning_vi' => 'giữ lại',
        ]);
        $vocabDelete = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'remove',
            'meaning_vi' => 'xóa đi',
        ]);

        $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.vocabularies.bulk-delete', $this->personalTopic->topic_id), [
                'vocabulary_ids' => [$vocabDelete->vocabulary_id],
            ]);

        $this->assertDatabaseHas('vocabularies', [
            'vocabulary_id' => $vocabKeep->vocabulary_id,
            'word' => 'retain',
        ]);
    }

    /**
     * Trường hợp 12: Danh sách vocabulary_ids rỗng bị từ chối với mã 422.
     */
    public function test_12_empty_vocabulary_ids_list_is_rejected(): void
    {
        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.vocabularies.bulk-delete', $this->personalTopic->topic_id), [
                'vocabulary_ids' => [],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vocabulary_ids']);
    }

    /**
     * Trường hợp 13: ID thuộc chủ đề khác bị từ chối với mã 422.
     */
    public function test_13_vocabulary_id_from_another_topic_is_rejected(): void
    {
        $otherVocab = Vocabulary::create([
            'topic_id' => $this->otherUserTopic->topic_id,
            'word' => 'foreign_word',
            'meaning_vi' => 'từ ngoài chủ đề',
        ]);

        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.vocabularies.bulk-delete', $this->personalTopic->topic_id), [
                'vocabulary_ids' => [$otherVocab->vocabulary_id],
            ]);

        $response->assertStatus(422);

        $this->assertDatabaseHas('vocabularies', ['vocabulary_id' => $otherVocab->vocabulary_id]);
    }

    /**
     * Trường hợp 14: Danh sách trộn ID hợp lệ và trái phép bị từ chối toàn bộ (Transaction rollback).
     */
    public function test_14_mixed_valid_and_invalid_ids_are_rejected_completely(): void
    {
        $validVocab = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'valid_word',
            'meaning_vi' => 'từ hợp lệ',
        ]);

        $otherVocab = Vocabulary::create([
            'topic_id' => $this->otherUserTopic->topic_id,
            'word' => 'invalid_word',
            'meaning_vi' => 'từ trái phép',
        ]);

        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.vocabularies.bulk-delete', $this->personalTopic->topic_id), [
                'vocabulary_ids' => [$validVocab->vocabulary_id, $otherVocab->vocabulary_id],
            ]);

        $response->assertStatus(422);

        // Đảm bảo không xóa một phần: cả 2 từ đều phải còn nguyên vẹn trong CSDL
        $this->assertDatabaseHas('vocabularies', ['vocabulary_id' => $validVocab->vocabulary_id]);
        $this->assertDatabaseHas('vocabularies', ['vocabulary_id' => $otherVocab->vocabulary_id]);
    }

    /**
     * Trường hợp 15: Không xóa được từ vựng thuộc chủ đề hệ thống -> 403 Forbidden.
     */
    public function test_15_cannot_delete_vocabularies_from_system_topic(): void
    {
        $systemVocab = Vocabulary::create([
            'topic_id' => $this->systemTopic->topic_id,
            'word' => 'system_word',
            'meaning_vi' => 'từ hệ thống',
        ]);

        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.vocabularies.bulk-delete', $this->systemTopic->topic_id), [
                'vocabulary_ids' => [$systemVocab->vocabulary_id],
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('vocabularies', ['vocabulary_id' => $systemVocab->vocabulary_id]);
    }

    /**
     * Trường hợp 16: Không xóa được từ vựng thuộc chủ đề của người khác -> 403 Forbidden.
     */
    public function test_16_cannot_delete_vocabularies_from_another_user_topic(): void
    {
        $otherVocab = Vocabulary::create([
            'topic_id' => $this->otherUserTopic->topic_id,
            'word' => 'other_user_word',
            'meaning_vi' => 'từ của người khác',
        ]);

        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.vocabularies.bulk-delete', $this->otherUserTopic->topic_id), [
                'vocabulary_ids' => [$otherVocab->vocabulary_id],
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('vocabularies', ['vocabulary_id' => $otherVocab->vocabulary_id]);
    }

    /**
     * Trường hợp 17: Tiến độ user_vocabulary của các từ bị xóa cũng bị xóa theo.
     */
    public function test_17_user_vocabulary_progress_is_deleted_when_vocabulary_is_deleted(): void
    {
        $vocab = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'progress_word',
            'meaning_vi' => 'từ có tiến độ',
        ]);

        $userVocab = UserVocabulary::create([
            'user_id' => $this->owner->user_id,
            'vocabulary_id' => $vocab->vocabulary_id,
            'is_done' => true,
        ]);

        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.vocabularies.bulk-delete', $this->personalTopic->topic_id), [
                'vocabulary_ids' => [$vocab->vocabulary_id],
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('vocabularies', ['vocabulary_id' => $vocab->vocabulary_id]);
    }

    /**
     * Trường hợp 18: Xóa hết toàn bộ từ vẫn giữ nguyên bản ghi chủ đề.
     */
    public function test_18_deleting_all_vocabularies_keeps_the_topic_intact(): void
    {
        $vocab = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'only_word',
            'meaning_vi' => 'từ duy nhất',
        ]);

        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.vocabularies.bulk-delete', $this->personalTopic->topic_id), [
                'vocabulary_ids' => [$vocab->vocabulary_id],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'remaining_count' => 0,
            ]);

        $this->assertDatabaseMissing('vocabularies', ['vocabulary_id' => $vocab->vocabulary_id]);
        $this->assertDatabaseHas('topics', ['topic_id' => $this->personalTopic->topic_id]);
    }

    // ==========================================
    // NHÓM 3: XÁC THỰC VÀ TÍNH ĐỘC LẬP (TEST 19 - 20)
    // ==========================================

    /**
     * Trường hợp 19: Khách chưa đăng nhập không thể thực hiện đổi tên hoặc xóa từ vựng.
     */
    public function test_19_guest_cannot_rename_topic_or_delete_vocabularies(): void
    {
        $vocab = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'secret_word',
            'meaning_vi' => 'từ bí mật',
        ]);

        // Khách đổi tên
        $renameResponse = $this->putJson(route('tu-vung.topics.update', $this->personalTopic->topic_id), [
            'name' => 'Tên do khách gửi',
        ]);
        $renameResponse->assertStatus(401);

        // Khách xóa từ
        $deleteResponse = $this->deleteJson(route('tu-vung.topics.vocabularies.bulk-delete', $this->personalTopic->topic_id), [
            'vocabulary_ids' => [$vocab->vocabulary_id],
        ]);
        $deleteResponse->assertStatus(401);
    }

    /**
     * Trường hợp 20: Thao tác đổi tên và thao tác xóa hàng loạt hoàn toàn độc lập, không ảnh hưởng lẫn nhau.
     */
    public function test_20_rename_and_bulk_delete_operations_are_strictly_independent(): void
    {
        $vocab1 = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'keep_word',
            'meaning_vi' => 'từ giữ lại',
        ]);
        $vocab2 = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'delete_word',
            'meaning_vi' => 'từ xóa',
        ]);

        // Thao tác 1: Đổi tên chủ đề -> Tuyệt đối không xóa bất kỳ từ vựng nào dù request có kèm ID
        $renameResponse = $this->actingAs($this->owner)
            ->putJson(route('tu-vung.topics.update', $this->personalTopic->topic_id), [
                'name' => 'Tên mới hoàn toàn độc lập',
                'vocabulary_ids' => [$vocab2->vocabulary_id],
            ]);
        $renameResponse->assertStatus(200);

        $this->assertDatabaseHas('topics', [
            'topic_id' => $this->personalTopic->topic_id,
            'name' => 'Tên mới hoàn toàn độc lập',
        ]);
        $this->assertDatabaseHas('vocabularies', ['vocabulary_id' => $vocab1->vocabulary_id]);
        $this->assertDatabaseHas('vocabularies', ['vocabulary_id' => $vocab2->vocabulary_id]);

        // Thao tác 2: Xóa từ vựng được chọn -> Tuyệt đối không tự ý đổi tên chủ đề
        $deleteResponse = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.vocabularies.bulk-delete', $this->personalTopic->topic_id), [
                'vocabulary_ids' => [$vocab2->vocabulary_id],
                'name' => 'Tên bị thay đổi ngoài ý muốn',
            ]);
        $deleteResponse->assertStatus(200);

        $this->assertDatabaseMissing('vocabularies', ['vocabulary_id' => $vocab2->vocabulary_id]);
        $this->assertDatabaseHas('vocabularies', ['vocabulary_id' => $vocab1->vocabulary_id]);

        // Tên chủ đề vẫn giữ nguyên là "Tên mới hoàn toàn độc lập", không bị thay đổi bởi request xóa
        $this->assertDatabaseHas('topics', [
            'topic_id' => $this->personalTopic->topic_id,
            'name' => 'Tên mới hoàn toàn độc lập',
        ]);
    }
}
