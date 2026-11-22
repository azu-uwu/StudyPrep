<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        // 1. Thêm thông tin cá nhân vào users
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'birthday')) {
                $table->date('birthday')->nullable();
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 25)->nullable();
            }
            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 30)->nullable();
            }
            if (! Schema::hasColumn('users', 'city')) {
                $table->string('city', 100)->nullable();
            }
            if (! Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable();
            }
        });

    }

    public function down(): void
    {
        // Không tự động xóa dữ liệu người dùng khi rollback.
        // Nếu cần hoàn tác, phải khôi phục dữ liệu
        // và cấu trúc từ bản sao lưu trước.
    }
};
