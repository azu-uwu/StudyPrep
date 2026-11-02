<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->call([
                UserSeeder::class,
                ReferenceDataSeeder::class,
                QuestionSeeder::class,
                AnswerOptionSeeder::class,
                VocabularySeeder::class,
                TestAttemptSeeder::class,
            ]);
        });
    }
}
