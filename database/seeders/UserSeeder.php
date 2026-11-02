<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'user_name' => 'admin',
                'email' => 'admin@studyprep.com',
                'full_name' => 'Nguyễn Admin',
                'role' => 'admin',
                'status' => 'ACTIVE',
                'energy' => 999,
                'created_at' => now(),
                'email_verified_at' => now(),
                'locked_until' => null,
            ],
            [
                'user_name' => 'thaotran',
                'email' => 'thaotran@studyprep.edu.vn',
                'full_name' => 'Thảo Trần',
                'role' => 'learner',
                'status' => 'ACTIVE',
                'energy' => 15,
                'created_at' => now()->subDays(45),
                'email_verified_at' => now()->subDays(45),
                'locked_until' => null,
            ],
            [
                'user_name' => 'uyen.dang',
                'email' => 'uyen.dang@outlook.com',
                'full_name' => 'Đặng Phương Uyên',
                'role' => 'learner',
                'status' => 'PENDING',
                'energy' => 5,
                'created_at' => now()->subDays(5),
                'email_verified_at' => null,
                'locked_until' => null,
            ],
            [
                'user_name' => 'bao.le',
                'email' => 'bao.le@gmail.com',
                'full_name' => 'Lê Quốc Bảo',
                'role' => 'learner',
                'status' => 'LOCKED',
                'energy' => 0,
                'created_at' => now()->subDays(60),
                'email_verified_at' => now()->subDays(60),
                'locked_until' => now()->addDays(30),
            ],
            [
                'user_name' => 'tuan.hoang',
                'email' => 'tuan.hoang@studyprep.edu.vn',
                'full_name' => 'ThS. Hoàng Minh Tuấn',
                'role' => 'teacher',
                'status' => 'ACTIVE',
                'energy' => 100,
                'created_at' => now()->subMonths(6),
                'email_verified_at' => now()->subMonths(6),
                'locked_until' => null,
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    ...$user,
                    'password_hash' => Hash::make($user['role'] === 'admin' ? 'admin123' : 'password123'),
                    'failed_login_attempts' => 0,
                ]
            );
        }
    }
}
