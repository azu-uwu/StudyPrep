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
        if (Schema::hasTable('question_set') && ! Schema::hasColumn('question_set', 'audio_url')) {
            Schema::table('question_set', function (Blueprint $table): void {
                $table->string('audio_url', 500)->nullable()->after('media_url');
            });

            DB::table('question_set')
                ->where('media_type', 'AUDIO')
                ->whereNotNull('media_url')
                ->where('media_url', '!=', '')
                ->update([
                    'audio_url' => DB::raw('media_url'),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('question_set') && Schema::hasColumn('question_set', 'audio_url')) {
            Schema::table('question_set', function (Blueprint $table): void {
                $table->dropColumn('audio_url');
            });
        }
    }
};
