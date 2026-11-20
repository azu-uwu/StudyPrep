<?php

namespace Tests\Feature;

use App\Models\Topic;
use App\Models\User;
use App\Models\Vocabulary;
use App\Services\EnergyService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class EnergyRecoveryTest extends TestCase
{
    use DatabaseTransactions;

    protected User $student;

    protected User $otherStudent;

    protected User $adminUser;

    protected Topic $systemTopic;

    protected Topic $otherUserTopic;

    protected function setUp(): void
    {
        parent::setUp();

        // Học viên chính tham gia bài kiểm tra
        $this->student = User::create([
            'user_name' => 'student_'.uniqid(),
            'full_name' => 'Học viên Test Energy',
            'email' => 'student_'.uniqid().'@studyprep.test',
            'password_hash' => bcrypt('Password123!'),
            'role' => 'student',
            'energy' => 4,
        ]);

        // Học viên khác để kiểm tra tính độc lập
        $this->otherStudent = User::create([
            'user_name' => 'other_'.uniqid(),
            'full_name' => 'Học viên Khác',
            'email' => 'other_'.uniqid().'@studyprep.test',
            'password_hash' => bcrypt('Password123!'),
            'role' => 'user',
            'energy' => 3,
        ]);

        // Quản trị viên
        $this->adminUser = User::create([
            'user_name' => 'admin_'.uniqid(),
            'full_name' => 'Admin Test',
            'email' => 'admin_'.uniqid().'@studyprep.test',
            'password_hash' => bcrypt('Password123!'),
            'role' => 'admin',
            'energy' => 999,
        ]);

        // Chủ đề hệ thống
        $this->systemTopic = Topic::create([
            'name' => 'Chủ đề Hệ thống '.uniqid(),
            'description' => 'Mô tả chủ đề hệ thống',
            'user_id' => null,
        ]);

        // Chủ đề cá nhân của học viên khác
        $this->otherUserTopic = Topic::create([
            'name' => 'Chủ đề Riêng của User Khác '.uniqid(),
            'description' => 'Chủ đề cá nhân',
            'user_id' => $this->otherStudent->user_id,
        ]);
    }

    /**
     * Tạo từ vựng mẫu thuộc chủ đề hệ thống hoặc chủ đề chỉ định.
     */
    protected function createVocab(string $word = 'word', ?Topic $topic = null): Vocabulary
    {
        return Vocabulary::create([
            'topic_id' => ($topic ?? $this->systemTopic)->topic_id,
            'word' => $word.'_'.uniqid(),
            'meaning_vi' => 'Nghĩa của '.$word,
            'part_of_speech' => 'Noun|650+',
            'pronunciation' => '/'.$word.'/',
            'example_en' => 'Example sentence for '.$word,
            'example_vi' => 'Câu ví dụ cho '.$word,
        ]);
    }

    /**
     * Thực hiện luồng làm 2 bài kiểm tra cho 1 từ vựng.
     */
    protected function completePractice(User $user, Vocabulary $vocab, bool $listenCorrect = true, bool $fillCorrect = true): TestResponse
    {
        $this->actingAs($user)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $vocab->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => $listenCorrect ? $vocab->word : 'wrong_listen',
        ]);

        return $this->actingAs($user)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $vocab->vocabulary_id,
            'practice_type' => 'fill',
            'answer' => $fillCorrect ? $vocab->word : 'wrong_fill',
        ]);
    }

    /**
     * 1. Đúng một bài trong hai bài -> từ được tính 1 lần.
     */
    public function test_01_correct_in_one_of_two_exercises_counts_as_one_word(): void
    {
        $vocab = $this->createVocab('listen_only');

        $response = $this->completePractice($this->student, $vocab, listenCorrect: true, fillCorrect: false);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'finalized' => true,
                'is_done' => 1,
                'reward_accumulated_count' => 1,
                'energy_rewarded' => false,
            ]);

        $this->assertSame(1, app(EnergyService::class)->getAccumulatedCount($this->student->user_id));
    }

    /**
     * 2. Đúng cả hai bài -> vẫn chỉ tính 1 lần.
     */
    public function test_02_correct_in_both_exercises_still_counts_as_one_word(): void
    {
        $vocab = $this->createVocab('both_correct');

        $response = $this->completePractice($this->student, $vocab, listenCorrect: true, fillCorrect: true);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'finalized' => true,
                'is_done' => 1,
                'reward_accumulated_count' => 1,
                'energy_rewarded' => false,
            ]);

        $this->assertSame(1, app(EnergyService::class)->getAccumulatedCount($this->student->user_id));
    }

    /**
     * 3. Sai cả hai -> không tính vào bộ đếm.
     */
    public function test_03_wrong_in_both_exercises_does_not_count(): void
    {
        $vocab = $this->createVocab('both_wrong');

        $response = $this->completePractice($this->student, $vocab, listenCorrect: false, fillCorrect: false);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'finalized' => true,
                'is_done' => 0,
                'reward_accumulated_count' => 0,
                'energy_rewarded' => false,
            ]);

        $this->assertSame(0, app(EnergyService::class)->getAccumulatedCount($this->student->user_id));
    }

    /**
     * 4. Bấm "Mình đã thuộc từ này" -> không tính (không gửi request check-answer).
     */
    public function test_04_skip_button_does_not_count(): void
    {
        $vocab = $this->createVocab('skipped_word');

        // Phía Frontend, nút "Mình đã thuộc từ này" chỉ chuyển sang từ tiếp theo mà không gọi API checkAnswer
        // Kiểm tra chắc chắn DB và Session không có bản ghi nào
        $this->assertDatabaseMissing('user_vocabulary', [
            'user_id' => $this->student->user_id,
            'vocabulary_id' => $vocab->vocabulary_id,
        ]);
        $this->assertSame(0, app(EnergyService::class)->getAccumulatedCount($this->student->user_id));
    }

    /**
     * 5. Chưa hoàn thành cả hai bài -> không tính vào bộ đếm.
     */
    public function test_05_uncompleted_exercises_does_not_count(): void
    {
        $vocab = $this->createVocab('half_done');

        // Chỉ gửi bài 1 (Nghe)
        $resListen = $this->actingAs($this->student)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $vocab->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => $vocab->word,
        ]);

        $resListen->assertStatus(200)
            ->assertJson([
                'success' => true,
                'finalized' => false,
                'reward_accumulated_count' => 0,
                'energy_rewarded' => false,
            ]);

        // CSDL chưa chốt
        $this->assertDatabaseMissing('user_vocabulary', [
            'user_id' => $this->student->user_id,
            'vocabulary_id' => $vocab->vocabulary_id,
        ]);

        // Bộ đếm Session vẫn là 0
        $this->assertSame(0, app(EnergyService::class)->getAccumulatedCount($this->student->user_id));
    }

    /**
     * 6. Đúng 9 từ khác nhau -> chưa cộng năng lượng.
     */
    public function test_06_nine_different_words_does_not_award_energy(): void
    {
        $this->student->update(['energy' => 3]);

        for ($i = 1; $i <= 9; $i++) {
            $vocab = $this->createVocab("word_{$i}");
            $res = $this->completePractice($this->student, $vocab, listenCorrect: true, fillCorrect: true);
            $res->assertJson([
                'reward_accumulated_count' => $i,
                'energy_rewarded' => false,
                'energy_awarded_amount' => 0,
            ]);
        }

        $this->assertSame(9, app(EnergyService::class)->getAccumulatedCount($this->student->user_id));
        $this->assertSame(3, (int) $this->student->fresh()->energy);
    }

    /**
     * 7. Đúng từ thứ 10 -> cộng chính xác 1 năng lượng.
     */
    public function test_07_tenth_different_word_awards_exact_one_energy(): void
    {
        $this->student->update(['energy' => 3]);

        // Học 9 từ đầu
        for ($i = 1; $i <= 9; $i++) {
            $vocab = $this->createVocab("word_{$i}");
            $this->completePractice($this->student, $vocab, listenCorrect: true, fillCorrect: false);
        }

        // Học từ thứ 10
        $tenthVocab = $this->createVocab('tenth_word');
        $resTenth = $this->completePractice($this->student, $tenthVocab, listenCorrect: false, fillCorrect: true);

        $resTenth->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_done' => 1,
                'current_energy' => 4,
                'energy_rewarded' => true,
                'energy_awarded_amount' => 1,
                'reward_accumulated_count' => 0, // Chu kỳ mới bắt đầu
            ]);

        $this->assertSame(4, (int) $this->student->fresh()->energy);
    }

    /**
     * 8. Tích lũy đúng qua nhiều lượt học trong cùng Session.
     */
    public function test_08_accumulates_correctly_across_multiple_learning_sessions_in_same_session(): void
    {
        // Lượt 1: Học 4 từ
        for ($i = 1; $i <= 4; $i++) {
            $vocab = $this->createVocab("session1_word_{$i}");
            $this->completePractice($this->student, $vocab, listenCorrect: true, fillCorrect: true);
        }
        $this->assertSame(4, app(EnergyService::class)->getAccumulatedCount($this->student->user_id));

        // Lượt 2: Người học chuyển trang hoặc học thêm 3 từ nữa
        for ($i = 5; $i <= 7; $i++) {
            $vocab = $this->createVocab("session2_word_{$i}");
            $this->completePractice($this->student, $vocab, listenCorrect: true, fillCorrect: false);
        }
        $this->assertSame(7, app(EnergyService::class)->getAccumulatedCount($this->student->user_id));
    }

    /**
     * 9. Cùng một từ không được tính trùng trong chu kỳ.
     */
    public function test_09_same_word_is_not_counted_twice_in_same_cycle(): void
    {
        $vocab = $this->createVocab('single_vocab');

        // Lần học 1 của từ này
        $res1 = $this->completePractice($this->student, $vocab, listenCorrect: true, fillCorrect: true);
        $res1->assertJson(['reward_accumulated_count' => 1]);
        $this->assertSame(1, app(EnergyService::class)->getAccumulatedCount($this->student->user_id));

        // Học lại chính từ này trong cùng chu kỳ
        $res2 = $this->completePractice($this->student, $vocab, listenCorrect: true, fillCorrect: true);
        $res2->assertJson(['reward_accumulated_count' => 1]);
        $this->assertSame(1, app(EnergyService::class)->getAccumulatedCount($this->student->user_id));
    }

    /**
     * 10. Gửi request trùng hoặc tải lại trang không cộng thưởng lặp.
     */
    public function test_10_replayed_request_does_not_give_duplicate_reward(): void
    {
        $vocab = $this->createVocab('replay_vocab');

        // Làm bài Nghe
        $this->actingAs($this->student)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $vocab->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => $vocab->word,
        ]);

        // Làm bài Điền từ lần 1 -> chốt thành công
        $resFill1 = $this->actingAs($this->student)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $vocab->vocabulary_id,
            'practice_type' => 'fill',
            'answer' => $vocab->word,
        ]);
        $resFill1->assertJson(['finalized' => true, 'is_done' => 1, 'reward_accumulated_count' => 1]);

        // Gửi lại chính xác request Điền từ lần 2 (tải lại hoặc click liên tiếp)
        $resFill2 = $this->actingAs($this->student)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $vocab->vocabulary_id,
            'practice_type' => 'fill',
            'answer' => $vocab->word,
        ]);

        $resFill2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'finalized' => true,
                'reward_accumulated_count' => 1,
                'energy_rewarded' => false,
                'energy_awarded_amount' => 0,
            ]);

        $this->assertSame(1, app(EnergyService::class)->getAccumulatedCount($this->student->user_id));
    }

    /**
     * 11. Đang có 4 năng lượng -> cộng thành 5.
     */
    public function test_11_four_energy_increases_to_five(): void
    {
        $this->student->update(['energy' => 4]);

        for ($i = 1; $i <= 10; $i++) {
            $vocab = $this->createVocab("four_to_five_{$i}");
            $this->completePractice($this->student, $vocab, listenCorrect: true, fillCorrect: true);
        }

        $this->assertSame(5, (int) $this->student->fresh()->energy);
    }

    /**
     * 12. Đang có 5 năng lượng -> vẫn là 5 (không vượt giới hạn 5/5).
     */
    public function test_12_five_energy_remains_five_on_tenth_word(): void
    {
        $this->student->update(['energy' => 5]);

        for ($i = 1; $i <= 9; $i++) {
            $vocab = $this->createVocab("full_energy_{$i}");
            $this->completePractice($this->student, $vocab, listenCorrect: true, fillCorrect: true);
        }

        $tenthVocab = $this->createVocab('full_energy_10');
        $res = $this->completePractice($this->student, $tenthVocab, listenCorrect: true, fillCorrect: true);

        $res->assertStatus(200)
            ->assertJson([
                'success' => true,
                'current_energy' => 5,
                'energy_rewarded' => false,
                'energy_awarded_amount' => 0,
                'reward_accumulated_count' => 0,
            ]);

        $this->assertSame(5, (int) $this->student->fresh()->energy);
    }

    /**
     * 13. Sau khi thưởng, chu kỳ mới bắt đầu đúng.
     */
    public function test_13_new_cycle_starts_correctly_after_reward(): void
    {
        $this->student->update(['energy' => 3]);

        // Chu kỳ 1: hoàn thành 10 từ -> nhận +1 năng lượng (3 -> 4)
        for ($i = 1; $i <= 10; $i++) {
            $vocab = $this->createVocab("c1_word_{$i}");
            $this->completePractice($this->student, $vocab, listenCorrect: true, fillCorrect: true);
        }
        $this->assertSame(4, (int) $this->student->fresh()->energy);
        $this->assertSame(0, app(EnergyService::class)->getAccumulatedCount($this->student->user_id));

        // Chu kỳ 2: bắt đầu từ vựng mới -> đếm = 1
        $c2Vocab1 = $this->createVocab('c2_word_1');
        $resC2 = $this->completePractice($this->student, $c2Vocab1, listenCorrect: true, fillCorrect: true);
        $resC2->assertJson([
            'reward_accumulated_count' => 1,
            'energy_rewarded' => false,
        ]);
        $this->assertSame(1, app(EnergyService::class)->getAccumulatedCount($this->student->user_id));
    }

    /**
     * 14. Không ảnh hưởng dữ liệu của người dùng khác.
     */
    public function test_14_does_not_affect_other_users_data(): void
    {
        $vocab = $this->createVocab('shared_vocab');

        // Học viên 1 học từ
        $this->completePractice($this->student, $vocab, listenCorrect: true, fillCorrect: true);
        $this->assertSame(1, app(EnergyService::class)->getAccumulatedCount($this->student->user_id));

        // Học viên 2 ban đầu phải có bộ đếm riêng là 0
        $this->assertSame(0, app(EnergyService::class)->getAccumulatedCount($this->otherStudent->user_id));

        // Năng lượng của học viên 2 không bị thay đổi
        $this->assertSame(3, (int) $this->otherStudent->fresh()->energy);
    }

    /**
     * 15. Người chưa đăng nhập không thể nhận thưởng.
     */
    public function test_15_unauthenticated_user_cannot_earn_rewards(): void
    {
        $vocab = $this->createVocab('guest_vocab');

        $response = $this->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $vocab->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => $vocab->word,
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để lưu kết quả luyện tập.',
            ]);
    }

    /**
     * 16. Không thể dùng từ thuộc chủ đề cá nhân của người khác để nhận thưởng.
     */
    public function test_16_cannot_use_another_users_personal_topic_vocabulary_to_earn_rewards(): void
    {
        // Từ vựng thuộc chủ đề của otherStudent
        $privateVocab = $this->createVocab('private_word', $this->otherUserTopic);

        // Học viên chính cố tình làm từ này
        $response = $this->actingAs($this->student)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $privateVocab->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => $privateVocab->word,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập từ vựng này.',
            ]);
    }

    /**
     * 17. Command phục hồi đúng các tài khoản học viên dưới 5 năng lượng.
     */
    public function test_17_command_resets_students_with_energy_below_five(): void
    {
        $this->student->update(['energy' => 2]);
        $this->otherStudent->update(['energy' => 1]);

        $this->artisan('studyprep:reset-daily-energy')
            ->expectsOutputToContain('Hoàn tất: Đã phục hồi năng lượng về 5')
            ->assertSuccessful();

        $this->assertSame(5, (int) $this->student->fresh()->energy);
        $this->assertSame(5, (int) $this->otherStudent->fresh()->energy);
    }

    /**
     * 18. Command không giảm tài khoản có năng lượng từ 5 trở lên.
     */
    public function test_18_command_does_not_decrease_accounts_with_energy_five_or_above(): void
    {
        $this->student->update(['energy' => 5]);
        $this->adminUser->update(['energy' => 999]);

        $this->artisan('studyprep:reset-daily-energy')
            ->assertSuccessful();

        $this->assertSame(5, (int) $this->student->fresh()->energy);
        $this->assertSame(999, (int) $this->adminUser->fresh()->energy);
    }

    /**
     * 19. Scheduler sử dụng đúng múi giờ Việt Nam.
     */
    public function test_19_scheduler_is_configured_with_vietnam_timezone(): void
    {
        $schedule = app(Schedule::class);
        $events = collect($schedule->events());

        $dailyResetEvent = $events->first(function ($event): bool {
            return is_string($event->command) && str_contains($event->command, 'studyprep:reset-daily-energy');
        });

        $this->assertNotNull($dailyResetEvent, 'Lệnh studyprep:reset-daily-energy chưa được đăng ký trong Laravel Scheduler.');
        $this->assertSame('Asia/Ho_Chi_Minh', $dailyResetEvent->timezone);
        $this->assertSame('0 0 * * *', $dailyResetEvent->expression);
    }

    /**
     * 20. Các test BE-008.1 trước đó tiếp tục pass đầy đủ.
     */
    public function test_20_previous_be_008_1_logic_remains_functional(): void
    {
        $vocab = $this->createVocab('be_008_check');

        // Bài 1: Nghe đúng
        $resListen = $this->actingAs($this->student)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $vocab->vocabulary_id,
            'practice_type' => 'listen',
            'answer' => $vocab->word,
        ]);
        $resListen->assertStatus(200)->assertJson(['is_correct' => true, 'finalized' => false]);

        // Bài 2: Điền sai -> Đúng ít nhất 1 bài nên is_done vẫn là 1
        $resFill = $this->actingAs($this->student)->postJson(route('tu-vung.check-answer'), [
            'vocabulary_id' => $vocab->vocabulary_id,
            'practice_type' => 'fill',
            'answer' => 'wrong',
        ]);
        $resFill->assertStatus(200)->assertJson(['is_correct' => false, 'finalized' => true, 'is_done' => 1]);

        $this->assertDatabaseHas('user_vocabulary', [
            'user_id' => $this->student->user_id,
            'vocabulary_id' => $vocab->vocabulary_id,
            'is_done' => 1,
        ]);
    }
}
