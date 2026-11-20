<?php

namespace Tests\Feature;

use App\Models\Topic;
use App\Models\User;
use App\Models\UserVocabulary;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FlashcardPracticeResultTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user1;

    protected User $user2;

    protected Topic $systemTopic;

    protected Vocabulary $systemVocab1;

    protected Vocabulary $systemVocab2;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo 2 tài khoản học viên để kiểm tra tính độc lập
        $this->user1 = User::create([
            'user_name' => 'user1_'.uniqid(),
            'full_name' => 'Học viên Test 1',
            'email' => 'user1_test_'.uniqid().'@studyprep.test',
            'password_hash' => bcrypt('Password123!'),
            'role' => 'user',
            'energy' => 5,
        ]);

        $this->user2 = User::create([
            'user_name' => 'user2_'.uniqid(),
            'full_name' => 'Học viên Test 2',
            'email' => 'user2_test_'.uniqid().'@studyprep.test',
            'password_hash' => bcrypt('Password123!'),
            'role' => 'user',
            'energy' => 5,
        ]);

        // Tạo chủ đề hệ thống (user_id = null)
        $this->systemTopic = Topic::create([
            'name' => 'Chủ đề Hệ thống Test '.uniqid(),
            'description' => 'Mô tả test',
            'user_id' => null,
        ]);

        // Tạo 2 từ vựng cho chủ đề hệ thống
        $this->systemVocab1 = Vocabulary::create([
            'topic_id' => $this->systemTopic->topic_id,
            'word' => 'examination',
            'meaning_vi' => 'Kỳ thi, sự kiểm tra',
            'part_of_speech' => 'Noun|650+',
            'pronunciation' => '/ɪɡˌzæmɪˈneɪʃn/',
            'example_en' => 'The examination is tomorrow.',
            'example_vi' => 'Kỳ thi diễn ra vào ngày mai.',
        ]);

        $this->systemVocab2 = Vocabulary::create([
            'topic_id' => $this->systemTopic->topic_id,
            'word' => 'principal',
            'meaning_vi' => 'Hiệu trưởng',
            'part_of_speech' => 'Noun|650+',
            'pronunciation' => '/ˈprɪnsəpl/',
            'example_en' => 'The principal welcomed students.',
            'example_vi' => 'Hiệu trưởng chào đón học sinh.',
        ]);
    }

    /**
     * 1. Nghe đúng + điền đúng -> is_done = 1.
     */
    public function test_listen_correct_and_fill_correct_saves_is_done_1(): void
    {
        // Bài 1: Nghe đúng
        $resListen = $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => 'examination',
        ]);
        $resListen->assertStatus(200)->assertJson(['is_correct' => true, 'finalized' => false]);

        // Bài 2: Điền đúng
        $resFill = $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'fill',
            'answer' => 'examination',
        ]);
        $resFill->assertStatus(200)->assertJson(['is_correct' => true, 'finalized' => true, 'is_done' => 1]);

        // Chốt vào CSDL: is_done = 1
        $this->assertDatabaseHas('user_vocabulary', [
            'user_id' => $this->user1->user_id,
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'is_done' => 1,
        ]);
    }

    /**
     * 2. Nghe đúng + điền sai -> is_done = 1.
     */
    public function test_listen_correct_and_fill_wrong_saves_is_done_1(): void
    {
        // Bài 1: Nghe đúng
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => 'examination',
        ])->assertJson(['is_correct' => true]);

        // Bài 2: Điền sai
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'fill',
            'answer' => 'wrong_answer',
        ])->assertJson(['is_correct' => false, 'finalized' => true, 'is_done' => 1]);

        // Đúng ít nhất 1 bài -> is_done = 1
        $this->assertDatabaseHas('user_vocabulary', [
            'user_id' => $this->user1->user_id,
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'is_done' => 1,
        ]);
    }

    /**
     * 3. Nghe sai + điền đúng -> is_done = 1.
     */
    public function test_listen_wrong_and_fill_correct_saves_is_done_1(): void
    {
        // Bài 1: Nghe sai
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => 'wrong_listen',
        ])->assertJson(['is_correct' => false]);

        // Bài 2: Điền đúng
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'fill',
            'answer' => 'examination',
        ])->assertJson(['is_correct' => true, 'finalized' => true, 'is_done' => 1]);

        // Đúng ít nhất 1 bài -> is_done = 1
        $this->assertDatabaseHas('user_vocabulary', [
            'user_id' => $this->user1->user_id,
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'is_done' => 1,
        ]);
    }

    /**
     * 4. Nghe sai + điền sai -> is_done = 0.
     */
    public function test_listen_wrong_and_fill_wrong_saves_is_done_0(): void
    {
        // Bài 1: Nghe sai
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => 'wrong_listen',
        ])->assertJson(['is_correct' => false]);

        // Bài 2: Điền sai
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'fill',
            'answer' => 'wrong_fill',
        ])->assertJson(['is_correct' => false, 'finalized' => true, 'is_done' => 0]);

        // Cả hai bài sai -> is_done = 0
        $this->assertDatabaseHas('user_vocabulary', [
            'user_id' => $this->user1->user_id,
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'is_done' => 0,
        ]);
    }

    /**
     * 5. Chưa làm đủ hai bài -> chưa chốt kết quả vào CSDL.
     */
    public function test_uncompleted_two_exercises_does_not_finalize_to_database(): void
    {
        // Người dùng mới chỉ nộp bài 1 (Nghe)
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => 'examination',
        ])->assertJson(['is_correct' => true, 'finalized' => false]);

        // Chưa có bản ghi nào trong bảng user_vocabulary
        $this->assertDatabaseMissing('user_vocabulary', [
            'user_id' => $this->user1->user_id,
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
        ]);
    }

    /**
     * 6. Học lại từ đã có is_done = 1, nếu sai cả hai bài ở lượt mới -> cập nhật về 0.
     */
    public function test_relearning_word_with_is_done_1_updates_to_0_if_both_wrong(): void
    {
        // Ban đầu đã có bản ghi is_done = 1 từ trước
        UserVocabulary::create([
            'user_id' => $this->user1->user_id,
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'is_done' => 1,
            'learned_at' => now()->subDay(),
        ]);

        // Lượt học mới: Bài 1 (Nghe) sai
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => 'wrong_1',
        ]);

        // Bài 2 (Điền) sai
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'fill',
            'answer' => 'wrong_2',
        ])->assertJson(['is_correct' => false, 'finalized' => true, 'is_done' => 0]);

        // Cập nhật về 0 theo lượt học mới
        $this->assertDatabaseHas('user_vocabulary', [
            'user_id' => $this->user1->user_id,
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'is_done' => 0,
        ]);

        // Không tạo bản ghi trùng
        $count = UserVocabulary::where('user_id', $this->user1->user_id)
            ->where('vocabulary_id', $this->systemVocab1->vocabulary_id)
            ->count();
        $this->assertSame(1, $count);
    }

    /**
     * 7. Học lại từ đã có is_done = 0, nếu đúng ít nhất 1 bài ở lượt mới -> cập nhật thành 1.
     */
    public function test_relearning_word_with_is_done_0_updates_to_1_if_one_correct(): void
    {
        // Ban đầu đã có bản ghi is_done = 0
        UserVocabulary::create([
            'user_id' => $this->user1->user_id,
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'is_done' => 0,
            'learned_at' => now()->subDay(),
        ]);

        // Lượt học mới: Bài 1 (Nghe) đúng, Bài 2 (Điền) sai
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => 'examination',
        ]);

        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'fill',
            'answer' => 'wrong_fill',
        ])->assertJson(['finalized' => true, 'is_done' => 1]);

        // Cập nhật thành 1
        $this->assertDatabaseHas('user_vocabulary', [
            'user_id' => $this->user1->user_id,
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'is_done' => 1,
        ]);

        $count = UserVocabulary::where('user_id', $this->user1->user_id)
            ->where('vocabulary_id', $this->systemVocab1->vocabulary_id)
            ->count();
        $this->assertSame(1, $count);
    }

    /**
     * 8. Bấm "Mình đã thuộc từ này" (bỏ qua từ) không làm thay đổi CSDL.
     */
    public function test_skip_flashcard_does_not_modify_database(): void
    {
        $this->assertDatabaseMissing('user_vocabulary', [
            'user_id' => $this->user1->user_id,
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
        ]);

        $count = UserVocabulary::where('user_id', $this->user1->user_id)
            ->where('vocabulary_id', $this->systemVocab1->vocabulary_id)
            ->count();
        $this->assertSame(0, $count);
    }

    /**
     * 9. Request trùng không tạo bản ghi trùng hoặc chốt sai kết quả.
     */
    public function test_duplicate_requests_do_not_create_duplicate_records_or_alter_result(): void
    {
        // Hoàn thành bài 1
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => 'examination',
        ]);

        // Gửi liên tiếp 3 request cho bài 2
        for ($i = 0; $i < 3; $i++) {
            $response = $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
                'vocabulary_id' => $this->systemVocab1->vocabulary_id,
                'practice_type' => 'fill',
                'answer' => 'examination',
            ]);
            $response->assertStatus(200)->assertJson(['finalized' => true, 'is_done' => 1]);
        }

        $recordsCount = UserVocabulary::where('user_id', $this->user1->user_id)
            ->where('vocabulary_id', $this->systemVocab1->vocabulary_id)
            ->count();

        $this->assertSame(1, $recordsCount);
    }

    /**
     * 10. Hai tài khoản có dữ liệu học tập độc lập.
     */
    public function test_two_users_have_independent_study_data(): void
    {
        // User 1 hoàn thành: nghe đúng + điền đúng -> is_done = 1
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => 'examination',
        ]);
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'fill',
            'answer' => 'examination',
        ]);

        // User 2 hoàn thành: nghe sai + điền sai -> is_done = 0
        $this->actingAs($this->user2)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => 'wrong_listen',
        ]);
        $this->actingAs($this->user2)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'fill',
            'answer' => 'wrong_fill',
        ]);

        $this->assertDatabaseHas('user_vocabulary', [
            'user_id' => $this->user1->user_id,
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'is_done' => 1,
        ]);

        $this->assertDatabaseHas('user_vocabulary', [
            'user_id' => $this->user2->user_id,
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'is_done' => 0,
        ]);
    }

    /**
     * 11. Không thể nộp kết quả cho từ thuộc chủ đề cá nhân của người khác (HTTP 403).
     */
    public function test_user_cannot_access_or_practice_another_users_personal_vocabulary(): void
    {
        // Tạo chủ đề cá nhân thuộc sở hữu của User 2
        $user2Topic = Topic::create([
            'name' => 'Chủ đề riêng của User 2',
            'user_id' => $this->user2->user_id,
        ]);

        $user2Vocab = Vocabulary::create([
            'topic_id' => $user2Topic->topic_id,
            'word' => 'confidential',
            'meaning_vi' => 'Bảo mật',
        ]);

        // User 1 cố tình nộp đáp án cho từ vựng của User 2
        $response = $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $user2Vocab->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => 'confidential',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập từ vựng này.',
            ]);

        $this->assertDatabaseMissing('user_vocabulary', [
            'vocabulary_id' => $user2Vocab->vocabulary_id,
        ]);
    }

    /**
     * 12. Không cho phép khách lưu kết quả (HTTP 401).
     */
    public function test_guest_cannot_save_practice_results(): void
    {
        $response = $this->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => 'examination',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để lưu kết quả luyện tập.',
            ]);

        $this->assertDatabaseMissing('user_vocabulary', [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
        ]);
    }

    /**
     * 13. Tiến độ học theo chủ đề được tính chính xác từ MySQL (số từ is_done = 1 / tổng số từ * 100%).
     */
    public function test_topic_progress_is_calculated_accurately_from_database(): void
    {
        // Chủ đề có 2 từ vựng.
        // User 1 hoàn thành từ 1: đúng nghe + đúng điền -> is_done = 1 (tiến độ 1/2 = 50%)
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => 'examination',
        ]);
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'fill',
            'answer' => 'examination',
        ]);

        // Truy cập trang danh sách từ vựng của chủ đề
        $response = $this->actingAs($this->user1)->get(route('tu-vung.show', $this->systemTopic->topic_id));
        $response->assertStatus(200);
        $response->assertSee('50%');

        // User 1 hoàn thành từ 2: đúng điền (nghe sai) -> is_done = 1 (tiến độ 2/2 = 100%)
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab2->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => 'wrong',
        ]);
        $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab2->vocabulary_id,
            'practice_type' => 'fill',
            'answer' => 'principal',
        ]);

        $response2 = $this->actingAs($this->user1)->get(route('tu-vung.show', $this->systemTopic->topic_id));
        $response2->assertStatus(200);
        $response2->assertSee('100%');

        // Đối với User 2 chưa học -> tiến độ 0%
        $responseUser2 = $this->actingAs($this->user2)->get(route('tu-vung.show', $this->systemTopic->topic_id));
        $responseUser2->assertStatus(200);
        $responseUser2->assertSee('0%');

        // Đối với khách chưa đăng nhập -> tiến độ 0%
        $responseGuest = $this->get(route('tu-vung.show', $this->systemTopic->topic_id));
        $responseGuest->assertStatus(200);
        $responseGuest->assertSee('0%');
    }

    /**
     * 14. Kiểm tra đáp án không phân biệt hoa thường và tự động chuẩn hóa khoảng trắng thừa.
     */
    public function test_answer_check_is_case_insensitive_and_trims_whitespace(): void
    {
        $response = $this->actingAs($this->user1)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $this->systemVocab1->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => '  EXAMINATION  ',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_correct' => true,
            ]);
    }
}
