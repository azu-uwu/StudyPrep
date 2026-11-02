<?php

namespace Database\Seeders;

use App\Models\AnswerOption;
use App\Models\Question;
use Illuminate\Database\Seeder;

class AnswerOptionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (QuestionSeeder::SAMPLES as $sample) {
            $question = Question::query()
                ->where('content', $sample['content'])
                ->firstOrFail();

            foreach ($sample['options'] as $label => $content) {
                AnswerOption::query()->updateOrCreate(
                    [
                        'question_id' => $question->question_id,
                        'label' => $label,
                    ],
                    [
                        'content' => $content,
                        'is_correct' => $label === $sample['correct'],
                    ]
                );
            }
        }
    }
}
