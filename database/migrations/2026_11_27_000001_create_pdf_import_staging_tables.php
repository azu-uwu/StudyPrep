<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo các bảng Staging phục vụ quy trình Bulk Import đề thi TOEIC L&R từ PDF (Admin).
     */
    public function up(): void
    {
        if (! Schema::hasTable('pdf_import_sessions')) {
            Schema::create('pdf_import_sessions', function (Blueprint $table): void {
                $table->string('import_session_id', 64)->primary();
                $table->unsignedBigInteger('admin_user_id')->nullable()->index();
                $table->string('question_file_name');
                $table->string('question_pdf_path');
                $table->string('answer_file_name')->nullable();
                $table->string('answer_pdf_path')->nullable();
                $table->string('import_scope', 20)->default('FULL_LR');
                $table->unsignedBigInteger('target_test_id')->nullable();
                $table->string('publish_status', 20)->default('ACTIVE');
                $table->string('status', 30)->default('PARSING');
                $table->json('summary_json')->nullable();
                $table->json('validation_errors_json')->nullable();
                $table->timestamp('committed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pdf_import_staging_questions')) {
            Schema::create('pdf_import_staging_questions', function (Blueprint $table): void {
                $table->id();
                $table->string('import_session_id', 64)->index();
                $table->unsignedSmallInteger('question_number');
                $table->string('part_code', 20);
                $table->string('group_key', 100)->nullable();
                $table->string('group_title')->nullable();
                $table->longText('stimulus_text')->nullable();
                $table->text('content')->nullable();
                $table->json('options_json')->nullable();
                $table->string('correct_answer', 5)->nullable();
                $table->longText('transcript')->nullable();
                $table->longText('explanation')->nullable();
                $table->string('media_url')->nullable();
                $table->string('validation_status', 20)->default('VALID');
                $table->json('validation_issues_json')->nullable();
                $table->timestamps();

                $table->unique(['import_session_id', 'question_number'], 'pdf_staging_session_qnum_unique');
                $table->foreign('import_session_id')
                    ->references('import_session_id')
                    ->on('pdf_import_sessions')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_import_staging_questions');
        Schema::dropIfExists('pdf_import_sessions');
    }
};
