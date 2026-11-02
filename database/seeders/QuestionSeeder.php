<?php

namespace Database\Seeders;

use App\Models\ExamSection;
use App\Models\Question;
use App\Models\QuestionType;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public const SAMPLES = [
        [
            'part' => 'PART1',
            'content' => 'A woman is arranging files on a desk near the window.',
            'explanation' => 'The sentence describes the action and location shown in the photograph.',
            'difficulty' => 'EASY',
            'status' => 'ACTIVE',
            'options' => [
                'A' => 'A woman is arranging files on a desk.',
                'B' => 'A woman is opening a window.',
                'C' => 'Some files are being delivered.',
                'D' => 'The desk is being moved outside.',
            ],
            'correct' => 'A',
        ],
        [
            'part' => 'PART2',
            'content' => 'Where should I leave the signed contract?',
            'explanation' => 'The response gives a location, which answers the question beginning with “Where”.',
            'difficulty' => 'EASY',
            'status' => 'ACTIVE',
            'options' => [
                'A' => 'On Ms. Lee’s desk, please.',
                'B' => 'The contract was signed yesterday.',
                'C' => 'We have a meeting at two.',
                'D' => 'Yes, the printer is working.',
            ],
            'correct' => 'A',
        ],
        [
            'part' => 'PART3',
            'content' => 'What are the speakers discussing?',
            'explanation' => 'The speakers are coordinating the schedule for a client presentation.',
            'difficulty' => 'MEDIUM',
            'status' => 'ACTIVE',
            'options' => [
                'A' => 'A client presentation',
                'B' => 'A hotel renovation',
                'C' => 'A product return',
                'D' => 'A staff recruitment plan',
            ],
            'correct' => 'A',
        ],
        [
            'part' => 'PART4',
            'content' => 'What should listeners do before entering the conference room?',
            'explanation' => 'The announcement asks attendees to register at the reception desk first.',
            'difficulty' => 'MEDIUM',
            'status' => 'PENDING',
            'options' => [
                'A' => 'Collect a visitor badge',
                'B' => 'Submit an expense report',
                'C' => 'Call the event organizer',
                'D' => 'Reserve a parking space',
            ],
            'correct' => 'A',
        ],
        [
            'part' => 'PART5',
            'content' => 'The company will ------- its annual report before the end of the month.',
            'explanation' => '“Publish” correctly describes making the annual report available.',
            'difficulty' => 'MEDIUM',
            'status' => 'DRAFT',
            'options' => [
                'A' => 'publish',
                'B' => 'publication',
                'C' => 'published',
                'D' => 'publishing',
            ],
            'correct' => 'A',
        ],
        [
            'part' => 'PART6',
            'content' => 'Please review the attached schedule ------- the planning meeting on Friday.',
            'explanation' => '“Before” is the appropriate preposition for an action that must happen earlier.',
            'difficulty' => 'MEDIUM',
            'status' => 'ARCHIVED',
            'options' => [
                'A' => 'before',
                'B' => 'among',
                'C' => 'during',
                'D' => 'unless',
            ],
            'correct' => 'A',
        ],
        [
            'part' => 'PART7',
            'content' => 'According to the notice, when will the new office open?',
            'explanation' => 'The notice states that the office will open on the first business day of June.',
            'difficulty' => 'HARD',
            'status' => 'REPORTED',
            'options' => [
                'A' => 'On the first business day of June',
                'B' => 'At the end of the current month',
                'C' => 'After the annual conference',
                'D' => 'When the renovation begins',
            ],
            'correct' => 'A',
        ],
    ];

    public function run(): void
    {
        $multipleChoiceTypeId = QuestionType::query()
            ->where('name', 'Multiple Choice')
            ->value('question_type_id');

        foreach (self::SAMPLES as $index => $sample) {
            $sectionId = ExamSection::query()
                ->where('code', $sample['part'])
                ->value('section_id');

            Question::query()->updateOrCreate(
                ['content' => $sample['content']],
                [
                    'section_id' => $sectionId,
                    'question_type_id' => $multipleChoiceTypeId,
                    'explanation' => $sample['explanation'],
                    'difficulty' => $sample['difficulty'],
                    'points' => 1,
                    'status' => $sample['status'],
                    'created_at' => now()->subDays($index + 1),
                ]
            );
        }
    }
}
