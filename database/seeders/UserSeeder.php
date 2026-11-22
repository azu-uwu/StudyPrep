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
                'created_at' => now()->subMonths(3),
                'email_verified_at' => now()->subMonths(3),
                'locked_until' => null,
            ],
            [
                'user_name' => 'admin.local',
                'email' => 'admin@studyprep.local',
                'full_name' => 'System Administrator',
                'role' => 'admin',
                'status' => 'ACTIVE',
                'energy' => 999,
                'created_at' => now()->subMonths(2),
                'email_verified_at' => now()->subMonths(2),
                'locked_until' => null,
            ],
            [
                'user_name' => 'tuan.hoang',
                'email' => 'tuan.hoang@studyprep.edu.vn',
                'full_name' => 'ThS. Hoàng Minh Tuấn',
                'role' => 'teacher',
                'status' => 'ACTIVE',
                'energy' => 100,
                'created_at' => now()->subMonths(2),
                'email_verified_at' => now()->subMonths(2),
                'locked_until' => null,
            ],
            [
                'user_name' => 'david.johnson',
                'email' => 'david.johnson@studyprep.edu.vn',
                'full_name' => 'David Johnson (TOEIC 990)',
                'role' => 'teacher',
                'status' => 'ACTIVE',
                'energy' => 100,
                'created_at' => now()->subMonths(1),
                'email_verified_at' => now()->subMonths(1),
                'locked_until' => null,
            ],
            [
                'user_name' => 'thaotran',
                'email' => 'thaotran@studyprep.edu.vn',
                'full_name' => 'Trần Thị Phương Thảo',
                'role' => 'student',
                'status' => 'ACTIVE',
                'energy' => 45,
                'created_at' => now()->subDays(28),
                'email_verified_at' => now()->subDays(28),
                'locked_until' => null,
            ],
            [
                'user_name' => 'alex.wilson',
                'email' => 'alex.wilson@studyprep.edu.vn',
                'full_name' => 'Alex Wilson',
                'role' => 'student',
                'status' => 'ACTIVE',
                'energy' => 30,
                'created_at' => now()->subDays(15),
                'email_verified_at' => now()->subDays(15),
                'locked_until' => null,
            ],
            [
                'user_name' => 'uyen.dang',
                'email' => 'uyen.dang@outlook.com',
                'full_name' => 'Đặng Phương Uyên',
                'role' => 'student',
                'status' => 'PENDING',
                'energy' => 20,
                'created_at' => now()->subDays(3),
                'email_verified_at' => null,
                'locked_until' => null,
            ],
            [
                'user_name' => 'bao.le',
                'email' => 'bao.le@gmail.com',
                'full_name' => 'Lê Quốc Bảo',
                'role' => 'student',
                'status' => 'LOCKED',
                'energy' => 0,
                'created_at' => now()->subDays(40),
                'email_verified_at' => now()->subDays(40),
                'locked_until' => now()->addDays(20),
            ],
            [
                'user_name' => 'michael.brown',
                'email' => 'michael.brown@gmail.com',
                'full_name' => 'Michael Brown',
                'role' => 'student',
                'status' => 'ACTIVE',
                'energy' => 35,
                'created_at' => now()->subDays(10),
                'email_verified_at' => now()->subDays(10),
                'locked_until' => null,
            ],
            [
                'user_name' => 'emma.watson',
                'email' => 'emma.watson@studyprep.edu.vn',
                'full_name' => 'Emma Watson',
                'role' => 'student',
                'status' => 'ACTIVE',
                'energy' => 50,
                'created_at' => now()->subDays(5),
                'email_verified_at' => now()->subDays(5),
                'locked_until' => null,
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    ...$user,
                    'password_hash' => Hash::make('StudyPrep@2026'),
                    'failed_login_attempts' => 0,
                ]
            );
        }
    }
}
