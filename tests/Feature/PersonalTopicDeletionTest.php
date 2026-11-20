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

class PersonalTopicDeletionTest extends TestCase
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

        // Tái tạo bảng topics, vocabularies và user_vocabulary có đầy đủ quan hệ khóa ngoại
        // theo đúng chuẩn cấu trúc CSDL MySQL studyprep.sql:
        // - vocabularies.topic_id ON DELETE CASCADE
        // - user_vocabulary.vocabulary_id ON DELETE CASCADE
        // - topics.parent_topic_id ON DELETE SET NULL
        Schema::dropIfExists('user_vocabulary');
        Schema::dropIfExists('vocabularies');
        Schema::dropIfExists('topics');

        Schema::create('topics', function (Blueprint $table): void {
            $table->id('topic_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_topic_id')->nullable();
            $table->foreign('parent_topic_id')->references('topic_id')->on('topics')->nullOnDelete();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });

        Schema::create('vocabularies', function (Blueprint $table): void {
            $table->id('vocabulary_id');
            $table->unsignedBigInteger('topic_id');
            $table->string('word', 150);
            $table->text('meaning_vi');
            $table->string('part_of_speech', 30)->nullable();
            $table->string('pronunciation', 150)->nullable();
            $table->text('example_en')->nullable();
            $table->text('example_vi')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->foreign('topic_id')->references('topic_id')->on('topics')->cascadeOnDelete();
        });

        Schema::create('user_vocabulary', function (Blueprint $table): void {
            $table->id('user_voca_id');
            $table->unsignedBigInteger('vocabulary_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('learned_at')->nullable();
            $table->boolean('is_done')->default(false);
            $table->foreign('vocabulary_id')->references('vocabulary_id')->on('vocabularies')->cascadeOnDelete();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        // Tạo học viên chủ sở hữu
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
            'full_name' => 'Học viên khác Test',
            'email' => 'other_'.uniqid().'@studyprep.test',
            'password_hash' => bcrypt('Password123!'),
            'role' => 'student',
            'status' => 'ACTIVE',
            'energy' => 5,
        ]);

        // Chủ đề cá nhân của $owner
        $this->personalTopic = Topic::create([
            'name' => 'Chủ đề từ vựng cá nhân của tôi',
            'description' => 'Mô tả chủ đề của tôi',
            'user_id' => $this->owner->user_id,
            'parent_topic_id' => null,
        ]);

        // Chủ đề hệ thống (user_id IS NULL)
        $this->systemTopic = Topic::create([
            'name' => 'Chủ đề hệ thống dùng chung',
            'description' => 'Mô tả chủ đề hệ thống',
            'user_id' => null,
            'parent_topic_id' => null,
        ]);

        // Chủ đề cá nhân của học viên khác
        $this->otherUserTopic = Topic::create([
            'name' => 'Chủ đề của học viên khác',
            'description' => 'Mô tả học viên khác',
            'user_id' => $this->otherStudent->user_id,
            'parent_topic_id' => null,
        ]);
    }

    /**
     * Test 1: Học viên xóa chủ đề cá nhân thành công.
     */
    public function test_1_student_can_delete_personal_topic_successfully(): void
    {
        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.destroy', $this->personalTopic->topic_id));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Đã xóa chủ đề thành công!',
                'deleted_topic_id' => $this->personalTopic->topic_id,
                'redirect_url' => route('tu-vung'),
            ]);
    }

    /**
     * Test 2: Bản ghi chủ đề bị xóa khỏi bảng topics trong CSDL.
     */
    public function test_2_topic_record_is_removed_from_database(): void
    {
        $topicId = $this->personalTopic->topic_id;

        $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.destroy', $topicId));

        $this->assertDatabaseMissing('topics', [
            'topic_id' => $topicId,
        ]);
    }

    /**
     * Test 3: Tất cả từ vựng thuộc chủ đề bị xóa theo (thông qua ON DELETE CASCADE).
     */
    public function test_3_all_vocabularies_in_topic_are_deleted_via_cascade(): void
    {
        $vocab1 = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'word_one',
            'meaning_vi' => 'từ số một',
        ]);
        $vocab2 = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'word_two',
            'meaning_vi' => 'từ số hai',
        ]);

        $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.destroy', $this->personalTopic->topic_id));

        $this->assertDatabaseMissing('vocabularies', ['vocabulary_id' => $vocab1->vocabulary_id]);
        $this->assertDatabaseMissing('vocabularies', ['vocabulary_id' => $vocab2->vocabulary_id]);
    }

    /**
     * Test 4: Tiến độ học liên quan trong user_vocabulary bị xóa theo (thông qua ON DELETE CASCADE).
     */
    public function test_4_user_vocabulary_progress_records_are_deleted_via_cascade(): void
    {
        $vocab = Vocabulary::create([
            'topic_id' => $this->personalTopic->topic_id,
            'word' => 'learning_word',
            'meaning_vi' => 'từ đang học',
        ]);

        $progress = UserVocabulary::create([
            'vocabulary_id' => $vocab->vocabulary_id,
            'user_id' => $this->owner->user_id,
            'learned_at' => now(),
            'is_done' => true,
        ]);

        $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.destroy', $this->personalTopic->topic_id));

        $this->assertDatabaseMissing('user_vocabulary', [
            'user_voca_id' => $progress->user_voca_id,
        ]);
    }

    /**
     * Test 5: Chủ đề và từ vựng khác không bị ảnh hưởng khi xóa chủ đề chỉ định.
     */
    public function test_5_other_topics_and_vocabularies_remain_unaffected(): void
    {
        $otherVocab = Vocabulary::create([
            'topic_id' => $this->otherUserTopic->topic_id,
            'word' => 'safe_word',
            'meaning_vi' => 'từ an toàn',
        ]);

        $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.destroy', $this->personalTopic->topic_id));

        // Chủ đề khác và từ vựng khác phải còn nguyên vẹn
        $this->assertDatabaseHas('topics', ['topic_id' => $this->otherUserTopic->topic_id]);
        $this->assertDatabaseHas('topics', ['topic_id' => $this->systemTopic->topic_id]);
        $this->assertDatabaseHas('vocabularies', ['vocabulary_id' => $otherVocab->vocabulary_id]);
    }

    /**
     * Test 6: Không thể xóa chủ đề hệ thống (user_id IS NULL) -> 403 Forbidden.
     */
    public function test_6_cannot_delete_system_topic(): void
    {
        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.destroy', $this->systemTopic->topic_id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('topics', ['topic_id' => $this->systemTopic->topic_id]);
    }

    /**
     * Test 7: Không thể xóa chủ đề cá nhân của người khác -> 403 Forbidden.
     */
    public function test_7_cannot_delete_another_student_topic(): void
    {
        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.destroy', $this->otherUserTopic->topic_id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('topics', ['topic_id' => $this->otherUserTopic->topic_id]);
    }

    /**
     * Test 8: Người chưa đăng nhập không thể xóa chủ đề -> 401 Unauthorized / Chuyển hướng login.
     */
    public function test_8_unauthenticated_user_cannot_delete_topic(): void
    {
        $response = $this->deleteJson(route('tu-vung.topics.destroy', $this->personalTopic->topic_id));

        $response->assertStatus(401);
        $this->assertDatabaseHas('topics', ['topic_id' => $this->personalTopic->topic_id]);
    }

    /**
     * Test 9: Chủ đề không tồn tại được xử lý HTTP 404 Not Found.
     */
    public function test_9_nonexistent_topic_returns_404(): void
    {
        $nonExistentId = 999999;

        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.destroy', $nonExistentId));

        $response->assertStatus(404);
    }

    /**
     * Test 10: Không thể giả mạo user_id trong request payload để xóa chủ đề người khác.
     */
    public function test_10_cannot_spoof_user_id_in_request_to_delete_others_topic(): void
    {
        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.destroy', $this->otherUserTopic->topic_id), [
                'user_id' => $this->otherStudent->user_id,
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('topics', ['topic_id' => $this->otherUserTopic->topic_id]);
    }

    /**
     * Test 11: Xóa chủ đề rỗng (chưa có từ vựng nào) vẫn thành công bình thường.
     */
    public function test_11_deleting_an_empty_topic_succeeds(): void
    {
        $emptyTopic = Topic::create([
            'name' => 'Chủ đề rỗng',
            'user_id' => $this->owner->user_id,
            'parent_topic_id' => null,
        ]);

        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.destroy', $emptyTopic->topic_id));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('topics', ['topic_id' => $emptyTopic->topic_id]);
    }

    /**
     * Test 12: Chủ đề con không bị xóa khi xóa chủ đề cha (parent_topic_id được SET NULL).
     */
    public function test_12_child_topics_are_not_deleted_when_parent_is_deleted_parent_topic_id_set_to_null(): void
    {
        $parentTopic = Topic::create([
            'name' => 'Chủ đề cha',
            'user_id' => $this->owner->user_id,
            'parent_topic_id' => null,
        ]);

        $childTopic = Topic::create([
            'name' => 'Chủ đề con',
            'user_id' => $this->owner->user_id,
            'parent_topic_id' => $parentTopic->topic_id,
        ]);

        $response = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.destroy', $parentTopic->topic_id));

        $response->assertStatus(200);

        // Chủ đề cha bị xóa
        $this->assertDatabaseMissing('topics', ['topic_id' => $parentTopic->topic_id]);

        // Chủ đề con vẫn còn nguyên, parent_topic_id được gán về null
        $this->assertDatabaseHas('topics', [
            'topic_id' => $childTopic->topic_id,
            'name' => 'Chủ đề con',
            'parent_topic_id' => null,
        ]);
    }

    /**
     * Test 13: Phản hồi thành công hoặc thất bại đúng định dạng mà Frontend sử dụng.
     */
    public function test_13_successful_and_failed_responses_match_frontend_expected_json_format(): void
    {
        // 13a. Thành công (JSON)
        $topicToDel = Topic::create([
            'name' => 'Chủ đề định dạng test',
            'user_id' => $this->owner->user_id,
        ]);

        $successRes = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.destroy', $topicToDel->topic_id));

        $successRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'deleted_topic_id',
                'redirect_url',
            ]);

        // 13b. Thất bại (403 Forbidden - khi xóa chủ đề người khác)
        $failRes = $this->actingAs($this->owner)
            ->deleteJson(route('tu-vung.topics.destroy', $this->otherUserTopic->topic_id));

        $failRes->assertStatus(403);
    }

    /**
     * Test 14: Chức năng sửa chủ đề BE-008.2.2 vẫn giữ nguyên hoạt động bình thường.
     */
    public function test_14_topic_renaming_from_be_008_2_2_remains_functional(): void
    {
        $response = $this->actingAs($this->owner)
            ->putJson(route('tu-vung.topics.update', $this->personalTopic->topic_id), [
                'name' => 'Tên chủ đề đã đổi hợp lệ',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cập nhật chủ đề thành công!',
                'topic' => [
                    'topic_id' => $this->personalTopic->topic_id,
                    'name' => 'Tên chủ đề đã đổi hợp lệ',
                ],
            ]);

        $this->assertDatabaseHas('topics', [
            'topic_id' => $this->personalTopic->topic_id,
            'name' => 'Tên chủ đề đã đổi hợp lệ',
        ]);
    }
}
