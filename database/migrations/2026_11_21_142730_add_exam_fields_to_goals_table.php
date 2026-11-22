<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->string('exam_type', 10)
                ->nullable()
                ->after('certificate');

            $table->date('target_date_lr')
                ->nullable()
                ->after('target_date');

            $table->date('target_date_sw')
                ->nullable()
                ->after('target_date_lr');
        });
    }

    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn([
                'exam_type',
                'target_date_lr',
                'target_date_sw',
            ]);
        });
    }
};
