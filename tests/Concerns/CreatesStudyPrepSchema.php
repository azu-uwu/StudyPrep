<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesStudyPrepSchema
{
    protected function createStudyPrepSchema(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_name');
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->string('full_name');
            $table->string('avatar_url', 500)->nullable();
            $table->string('role', 20)->default('user');
            $table->integer('energy')->default(5);
            $table->timestamp('created_at')->useCurrent();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('status', 20)->default('ACTIVE');
        });

        Schema::create('skills', function (Blueprint $table): void {
            $table->unsignedTinyInteger('skill_id')->primary();
            $table->string('code', 20)->unique();
            $table->string('skill_name', 50);
        });

        Schema::create('exam_section', function (Blueprint $table): void {
            $table->id('section_id');
            $table->unsignedTinyInteger('skill_id');
            $table->string('code', 20);
            $table->string('name', 150);
            $table->smallInteger('sequence_no');
            $table->integer('default_time_limit')->nullable();
        });

        Schema::create('question_type', function (Blueprint $table): void {
            $table->id('question_type_id');
            $table->string('name', 100);
            $table->string('response_mode', 20);
        });

        Schema::create('questions', function (Blueprint $table): void {
            $table->id('question_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('question_type_id');
            $table->unsignedBigInteger('question_set_id')->nullable();
            $table->text('content');
            $table->text('explanation')->nullable();
            $table->string('difficulty', 20);
            $table->decimal('points', 6, 2)->default(1);
            $table->string('status', 20)->default('DRAFT');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('test_blueprint', function (Blueprint $table): void {
            $table->id('blueprint_id');
            $table->string('name', 150);
            $table->string('exam_type', 20);
            $table->string('version', 30)->nullable();
            $table->integer('total_time')->nullable();
            $table->string('status', 20)->default('DRAFT');
        });

        Schema::create('test_form', function (Blueprint $table): void {
            $table->id('test_form_id');
            $table->unsignedBigInteger('blueprint_id');
            $table->string('title', 200);
            $table->timestamp('created_at')->useCurrent();
            $table->string('generation_seed', 100)->nullable();
        });

        Schema::create('attempts', function (Blueprint $table): void {
            $table->id('attempt_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('test_form_id');
            $table->string('session_type', 20);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('status', 20)->default('IN_PROGRESS');
        });

        Schema::create('score_result', function (Blueprint $table): void {
            $table->id('result_id');
            $table->unsignedBigInteger('attempt_id')->unique();
            $table->decimal('listening_score', 6, 2)->nullable();
            $table->decimal('reading_score', 6, 2)->nullable();
            $table->decimal('speaking_score', 6, 2)->nullable();
            $table->decimal('writing_score', 6, 2)->nullable();
            $table->decimal('total_score', 7, 2)->nullable();
            $table->timestamp('graded_at')->nullable();
        });

        Schema::create('topics', function (Blueprint $table): void {
            $table->id('topic_id');
            $table->unsignedBigInteger('user_id');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_topic_id')->nullable();
        });

        Schema::create('vocabularies', function (Blueprint $table): void {
            $table->id('vocabulary_id');
            $table->unsignedBigInteger('topic_id');
            $table->string('word', 150);
            $table->text('meaning_vi');
            $table->string('part_of_speech', 30)->nullable();
            $table->string('pronunciation', 150)->nullable();
            $table->text('example_en')->nullable();
            $table->text('example_vi')->nullable();
        });
    }
}
