<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('vocabularies')) {
            try {
                DB::statement('ALTER TABLE vocabularies MODIFY COLUMN part_of_speech VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');
            } catch (Throwable $e) {
                // Fallback for SQLite / other drivers
                Schema::table('vocabularies', function (Blueprint $table) {
                    $table->string('part_of_speech', 100)->nullable()->change();
                });
            }
        }

        if (Schema::hasTable('question_type')) {
            $types = [
                1 => ['Multiple Choice', 'SINGLE_CHOICE'],
                2 => ['Written Response', 'TEXT'],
                3 => ['Audio Listening', 'AUDIO'],
                4 => ['Speaking Response', 'SPEAKING'],
            ];

            foreach ($types as $id => [$name, $mode]) {
                DB::table('question_type')->updateOrInsert(
                    ['question_type_id' => $id],
                    ['name' => $name, 'response_mode' => $mode]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op
    }
};
