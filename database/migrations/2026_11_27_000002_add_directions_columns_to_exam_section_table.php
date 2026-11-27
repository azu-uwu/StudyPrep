<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung 2 cột directions_text và directions_audio_url vào bảng exam_section
     * để lưu lời dẫn đầu mỗi Part TOEIC mà không làm lệch tổng số câu hỏi.
     */
    public function up(): void
    {
        if (Schema::hasTable('exam_section')) {
            Schema::table('exam_section', function (Blueprint $table): void {
                if (! Schema::hasColumn('exam_section', 'directions_text')) {
                    $table->text('directions_text')->nullable()->after('default_time_limit');
                }
                if (! Schema::hasColumn('exam_section', 'directions_audio_url')) {
                    $table->string('directions_audio_url', 500)->nullable()->after('directions_text');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('exam_section')) {
            Schema::table('exam_section', function (Blueprint $table): void {
                if (Schema::hasColumn('exam_section', 'directions_audio_url')) {
                    $table->dropColumn('directions_audio_url');
                }
                if (Schema::hasColumn('exam_section', 'directions_text')) {
                    $table->dropColumn('directions_text');
                }
            });
        }
    }
};
