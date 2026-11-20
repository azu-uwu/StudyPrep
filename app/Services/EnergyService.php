<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnergyService
{
    /**
     * Mức năng lượng tối đa của học viên.
     */
    public const MAX_ENERGY = 5;

    /**
     * Số từ vựng đúng khác nhau cần tích lũy để được cộng 1 năng lượng.
     */
    public const VOCAB_REWARD_THRESHOLD = 10;

    /**
     * Lấy danh sách ID từ vựng đã được tính trong chu kỳ tích lũy hiện tại của người dùng.
     *
     * @return array<int, int>
     */
    public function getCycleVocabIds(int $userId): array
    {
        $sessionKey = "reward_correct_vocab_ids_{$userId}";
        $raw = session()->get($sessionKey, []);

        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $raw)));
    }

    /**
     * Lấy số lượng từ vựng đúng đã tích lũy trong chu kỳ hiện tại.
     */
    public function getAccumulatedCount(int $userId): int
    {
        return count($this->getCycleVocabIds($userId));
    }

    /**
     * Lưu danh sách ID từ vựng của chu kỳ hiện tại vào Session.
     *
     * @param  array<int, int>  $vocabIds
     */
    public function saveCycleVocabIds(int $userId, array $vocabIds): void
    {
        $cleanIds = array_values(array_unique(array_map('intval', $vocabIds)));
        $sessionKey = "reward_correct_vocab_ids_{$userId}";

        session()->put($sessionKey, $cleanIds);
        session()->put('reward_correct_vocab_ids', $cleanIds);
    }

    /**
     * Reset chu kỳ tích lũy sau khi hoàn tất 10 từ.
     */
    public function resetCycle(int $userId): void
    {
        $sessionKey = "reward_correct_vocab_ids_{$userId}";

        session()->put($sessionKey, []);
        session()->put('reward_correct_vocab_ids', []);
    }

    /**
     * Ghi nhận một từ vựng đã hoàn thành đúng vào chu kỳ tích lũy hiện tại.
     *
     * @return array{
     *     accumulated_count: int,
     *     current_energy: int,
     *     energy_rewarded: bool,
     *     energy_awarded_amount: int,
     *     cycle_completed: bool
     * }
     */
    public function recordCorrectVocabulary(int $userId, int $vocabularyId): array
    {
        $cycleVocabIds = $this->getCycleVocabIds($userId);

        // Nếu từ này đã được tính trong chu kỳ hiện tại -> không tính lặp
        if (in_array($vocabularyId, $cycleVocabIds, true)) {
            $user = User::query()->find($userId);

            return [
                'accumulated_count' => count($cycleVocabIds),
                'current_energy' => (int) ($user?->energy ?? 0),
                'energy_rewarded' => false,
                'energy_awarded_amount' => 0,
                'cycle_completed' => false,
            ];
        }

        // Bổ sung từ mới vào chu kỳ hiện tại
        $cycleVocabIds[] = $vocabularyId;
        $count = count($cycleVocabIds);

        // Đạt đủ 10 từ khác nhau -> tiến hành cộng năng lượng và hoàn tất chu kỳ
        if ($count >= self::VOCAB_REWARD_THRESHOLD) {
            $awardResult = $this->awardEnergy($userId);
            $this->resetCycle($userId);

            return [
                'accumulated_count' => 0,
                'current_energy' => $awardResult['current_energy'],
                'energy_rewarded' => $awardResult['awarded'],
                'energy_awarded_amount' => $awardResult['amount'],
                'cycle_completed' => true,
            ];
        }

        // Chưa đủ 10 từ -> cập nhật danh sách vào Session
        $this->saveCycleVocabIds($userId, $cycleVocabIds);
        $user = User::query()->find($userId);

        return [
            'accumulated_count' => $count,
            'current_energy' => (int) ($user?->energy ?? 0),
            'energy_rewarded' => false,
            'energy_awarded_amount' => 0,
            'cycle_completed' => false,
        ];
    }

    /**
     * Thực hiện cộng 1 năng lượng an toàn trong DB transaction với row-level locking.
     *
     * Chống race condition với logic trừ năng lượng của BE-012.
     * Năng lượng sau khi cộng không vượt quá MAX_ENERGY (5).
     *
     * @return array{
     *     awarded: bool,
     *     amount: int,
     *     current_energy: int
     * }
     */
    public function awardEnergy(int $userId): array
    {
        return DB::transaction(function () use ($userId): array {
            /** @var User|null $user */
            $user = User::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if ($user === null) {
                return [
                    'awarded' => false,
                    'amount' => 0,
                    'current_energy' => 0,
                ];
            }

            $currentEnergy = (int) $user->energy;

            if ($currentEnergy < self::MAX_ENERGY) {
                User::query()
                    ->where('user_id', $userId)
                    ->where('energy', '<', self::MAX_ENERGY)
                    ->increment('energy');

                $newEnergy = $currentEnergy + 1;

                return [
                    'awarded' => true,
                    'amount' => 1,
                    'current_energy' => $newEnergy,
                ];
            }

            return [
                'awarded' => false,
                'amount' => 0,
                'current_energy' => $currentEnergy,
            ];
        });
    }

    /**
     * Phục hồi năng lượng về 5 cho các tài khoản học viên có energy < 5 (chạy lúc 00:00 hằng ngày).
     *
     * - Chỉ áp dụng cho tài khoản học viên (không phải admin).
     * - Không làm giảm năng lượng của các tài khoản có energy >= 5.
     */
    public function resetDailyEnergy(): int
    {
        $updatedCount = User::query()
            ->where('energy', '<', self::MAX_ENERGY)
            ->where(function (Builder $query): void {
                $query->whereNull('role')
                    ->orWhereNotIn('role', ['admin', 'super_admin']);
            })
            ->update(['energy' => self::MAX_ENERGY]);

        Log::info('studyprep:reset-daily-energy: Đã phục hồi năng lượng về '.self::MAX_ENERGY." cho {$updatedCount} tài khoản học viên.");

        return $updatedCount;
    }
}
