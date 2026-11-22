<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Thêm thông tin cá nhân vào users
        Schema::table('users', function (Blueprint $table) {
            $table->date('birthday')->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('city', 100)->nullable();
            $table->timestamp('password_changed_at')->nullable();
        });

    }

    public function down(): void
    {
        // Không tự động xóa dữ liệu người dùng khi rollback.
        // Nếu cần hoàn tác, phải khôi phục dữ liệu
        // và cấu trúc từ bản sao lưu trước.
    }
};
