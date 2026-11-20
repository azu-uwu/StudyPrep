<?php

namespace App\Console\Commands;

use App\Services\EnergyService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('studyprep:reset-daily-energy')]
#[Description('Tự động phục hồi năng lượng về 5 lúc 00:00 mỗi ngày cho các tài khoản học viên có energy < 5')]
class ResetDailyEnergyCommand extends Command
{
    /**
     * Thực thi lệnh artisan phục hồi năng lượng qua ngày mới (BE-017.2).
     */
    public function handle(EnergyService $energyService): int
    {
        $this->info('Đang tiến hành phục hồi năng lượng qua ngày mới...');

        $updatedCount = $energyService->resetDailyEnergy();

        $this->info("Hoàn tất: Đã phục hồi năng lượng về 5 cho {$updatedCount} tài khoản học viên.");

        return Command::SUCCESS;
    }
}
