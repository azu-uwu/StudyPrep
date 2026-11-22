<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_settings', function (Blueprint $table) {

            $table->id();

            // Mỗi tài khoản có một bộ cài đặt
            $table->unsignedBigInteger('user_id')->unique();

            // Lưu các tùy chọn dưới dạng JSON
            $table->json('preferences');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_settings');
    }
};
