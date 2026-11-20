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
        if (! Schema::hasTable('topics') || ! Schema::hasColumn('topics', 'user_id')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE topics MODIFY COLUMN user_id BIGINT NULL DEFAULT NULL');

            return;
        }

        Schema::table('topics', function (Blueprint $table): void {
            $table->bigInteger('user_id')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('topics') || ! Schema::hasColumn('topics', 'user_id')) {
            return;
        }

        $fallbackUserId = DB::table('users')->orderBy('user_id')->value('user_id');
        if ($fallbackUserId !== null) {
            DB::table('topics')->whereNull('user_id')->update(['user_id' => $fallbackUserId]);
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE topics MODIFY COLUMN user_id BIGINT NOT NULL');

            return;
        }

        Schema::table('topics', function (Blueprint $table): void {
            $table->bigInteger('user_id')->nullable(false)->change();
        });
    }
};
