<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('goals')) {
            return;
        }

        Schema::table('goals', function (Blueprint $table) {
            if (! Schema::hasColumn('goals', 'exam_type')) {
                $table->string('exam_type', 10)
                    ->nullable()
                    ->after('certificate');
            }

            if (! Schema::hasColumn('goals', 'target_date_lr')) {
                $table->date('target_date_lr')
                    ->nullable()
                    ->after('target_date');
            }

            if (! Schema::hasColumn('goals', 'target_date_sw')) {
                $table->date('target_date_sw')
                    ->nullable()
                    ->after('target_date_lr');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('goals')) {
            return;
        }

        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn([
                'exam_type',
                'target_date_lr',
                'target_date_sw',
            ]);
        });
    }
};
