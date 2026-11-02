<?php

namespace Database\Seeders;

use App\Models\ExamSection;
use App\Models\QuestionType;
use App\Models\Skill;
use App\Models\TestForm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            1 => ['LC', 'Listening'],
            2 => ['RC', 'Reading'],
            3 => ['SW', 'Speaking & Writing'],
        ] as $skillId => [$code, $name]) {
            Skill::query()->updateOrCreate(
                ['skill_id' => $skillId],
                ['code' => $code, 'skill_name' => $name]
            );
        }

        $sections = [
            1 => [1, 'PART1', 'Part 1: Photographs'],
            2 => [1, 'PART2', 'Part 2: Question-Response'],
            3 => [1, 'PART3', 'Part 3: Conversations'],
            4 => [1, 'PART4', 'Part 4: Short Talks'],
            5 => [2, 'PART5', 'Part 5: Incomplete Sentences'],
            6 => [2, 'PART6', 'Part 6: Text Completion'],
            7 => [2, 'PART7', 'Part 7: Reading Comprehension'],
            8 => [3, 'WRITING_T1', 'Writing Task 1: Write a Sentence'],
            9 => [3, 'WRITING_T2', 'Writing Task 2: Respond to Email'],
        ];

        foreach ($sections as $sectionId => [$skillId, $code, $name]) {
            ExamSection::query()->updateOrCreate(
                ['section_id' => $sectionId],
                [
                    'skill_id' => $skillId,
                    'code' => $code,
                    'name' => $name,
                    'sequence_no' => $sectionId,
                ]
            );
        }

        foreach ([
            1 => ['Multiple Choice', 'SINGLE_CHOICE'],
            2 => ['Written Response', 'TEXT'],
            3 => ['Audio Listening', 'AUDIO'],
        ] as $typeId => [$name, $responseMode]) {
            QuestionType::query()->updateOrCreate(
                ['question_type_id' => $typeId],
                ['name' => $name, 'response_mode' => $responseMode]
            );
        }

        DB::table('test_blueprint')->updateOrInsert(
            ['name' => 'StudyPrep Admin Sample'],
            [
                'exam_type' => 'TOEIC',
                'version' => '2026',
                'total_time' => 120,
                'status' => 'ACTIVE',
            ]
        );

        $blueprintId = DB::table('test_blueprint')
            ->where('name', 'StudyPrep Admin Sample')
            ->value('blueprint_id');

        foreach ([
            'TOEIC Listening & Reading Sample',
            'TOEIC Writing & Speaking Sample',
        ] as $title) {
            TestForm::query()->updateOrCreate(
                ['title' => $title],
                [
                    'blueprint_id' => $blueprintId,
                    'generation_seed' => 'studyprep-admin-sample',
                ]
            );
        }
    }
}
