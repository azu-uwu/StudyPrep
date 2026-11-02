<?php

namespace Database\Seeders;

use App\Models\AttemptResponse;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\ScoreResult;
use App\Models\TestAttempt;
use App\Models\TestForm;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestAttemptSeeder extends Seeder
{
    public function run(): void
    {
        $learner = User::query()
            ->whereIn('role', ['learner', 'student', 'user'])
            ->where('status', 'ACTIVE')
            ->orderBy('user_id')
            ->firstOrFail();

        $listeningReadingForm = TestForm::query()
            ->where('title', 'TOEIC Listening & Reading Sample')
            ->firstOrFail();
        $writingSpeakingForm = TestForm::query()
            ->where('title', 'TOEIC Writing & Speaking Sample')
            ->firstOrFail();

        $attempts = [
            [
                'form' => $listeningReadingForm,
                'session_type' => 'FULL',
                'started_at' => today()->setTime(8, 0),
                'submitted_at' => today()->setTime(9, 0),
                'scores' => [
                    'listening_score' => 360,
                    'reading_score' => 340,
                    'speaking_score' => null,
                    'writing_score' => null,
                    'total_score' => 700,
                ],
            ],
            [
                'form' => $writingSpeakingForm,
                'session_type' => 'PRACTICE',
                'started_at' => today()->subDay()->setTime(14, 0),
                'submitted_at' => today()->subDay()->setTime(14, 30),
                'scores' => [
                    'listening_score' => null,
                    'reading_score' => null,
                    'speaking_score' => 150,
                    'writing_score' => 160,
                    'total_score' => 310,
                ],
            ],
        ];

        foreach ($attempts as $data) {
            $attempt = TestAttempt::query()->updateOrCreate(
                [
                    'user_id' => $learner->user_id,
                    'test_form_id' => $data['form']->test_form_id,
                    'session_type' => $data['session_type'],
                ],
                [
                    'started_at' => $data['started_at'],
                    'ended_at' => $data['submitted_at'],
                    'submitted_at' => $data['submitted_at'],
                    'status' => 'SUBMITTED',
                ]
            );

            ScoreResult::query()->updateOrCreate(
                ['attempt_id' => $attempt->attempt_id],
                [
                    ...$data['scores'],
                    'graded_at' => $data['submitted_at'],
                ]
            );
        }

        $listeningAttempt = TestAttempt::query()
            ->where('user_id', $learner->user_id)
            ->where('test_form_id', $listeningReadingForm->test_form_id)
            ->where('session_type', 'FULL')
            ->firstOrFail();

        foreach (['PART1', 'PART5'] as $partCode) {
            $question = Question::query()
                ->whereHas('section', fn ($query) => $query->where('code', $partCode))
                ->firstOrFail();

            AttemptResponse::query()->updateOrCreate(
                [
                    'attempt_id' => $listeningAttempt->attempt_id,
                    'question_id' => $question->question_id,
                ],
                [
                    'text_response' => 'A',
                    'score' => $question->points,
                    'submitted_at' => $listeningAttempt->submitted_at,
                ]
            );
        }

        $reportedQuestion = Question::query()
            ->where('status', 'REPORTED')
            ->firstOrFail();

        Feedback::query()->updateOrCreate(
            [
                'user_id' => $learner->user_id,
                'question_id' => $reportedQuestion->question_id,
            ],
            [
                'content' => 'Vui lòng kiểm tra lại lời giải và đáp án của câu hỏi này.',
                'status' => 'PENDING',
                'created_at' => now()->subMinutes(42),
                'updated_at' => now()->subMinutes(42),
            ]
        );
    }
}
