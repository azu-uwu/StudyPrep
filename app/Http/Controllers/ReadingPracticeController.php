<?php

namespace App\Http\Controllers;

use App\Services\ReadingQuestionBank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingPracticeController extends Controller
{
    /**
     * Màn hình làm bài luyện tập theo từng Part (Part 5, Part 6, Part 7)
     */
    public function practice(Request $request): View
    {
        $part = (int) $request->query('part', 5);
        if (! in_array($part, [5, 6, 7])) {
            $part = 5;
        }

        $allQuestions = ReadingQuestionBank::getQuestions($part);

        // Xử lý làm lại các câu bị sai
        if ($request->query('retry_wrong')) {
            $lastResult = session('reading_practice_result');
            if ($lastResult && ! empty($lastResult['details'])) {
                $wrongIds = collect($lastResult['details'])
                    ->where('status', 'incorrect')
                    ->pluck('id')
                    ->all();

                if (! empty($wrongIds)) {
                    $selectedQuestions = array_values(array_filter($allQuestions, function ($q) use ($wrongIds) {
                        return in_array($q['id'], $wrongIds);
                    }));

                    return view('pages.doc-luyen-tap', [
                        'part' => $part,
                        'questions' => $selectedQuestions,
                        'totalCount' => count($selectedQuestions),
                        'targetCount' => count($selectedQuestions),
                    ]);
                }
            }
        }

        $count = (int) $request->query('count', 20);
        if ($count <= 0) {
            $count = 20;
        }

        $selectedQuestions = array_slice($allQuestions, 0, min($count, count($allQuestions)));

        return view('pages.doc-luyen-tap', [
            'part' => $part,
            'questions' => $selectedQuestions,
            'totalCount' => count($selectedQuestions),
            'targetCount' => $count,
        ]);
    }

    /**
     * Xử lý nộp bài thi luyện tập Đọc
     */
    public function submit(Request $request): RedirectResponse
    {
        $part = (int) $request->input('part', 5);
        if (! in_array($part, [5, 6, 7])) {
            $part = 5;
        }

        $submittedAnswers = $request->input('answers', []);
        $timeSpentSeconds = (int) $request->input('time_spent', 165);
        $requestedCount = (int) $request->input('total_count', 20);

        $bank = ReadingQuestionBank::getQuestions($part);
        $questions = array_slice($bank, 0, min($requestedCount, count($bank)));

        $correctCount = 0;
        $incorrectCount = 0;
        $unansweredCount = 0;
        $details = [];

        foreach ($questions as $q) {
            $qid = $q['id'];
            $userPick = $submittedAnswers[$qid] ?? null;

            if (empty($userPick)) {
                $status = 'unanswered';
                $unansweredCount++;
            } elseif ($userPick === $q['correct']) {
                $status = 'correct';
                $correctCount++;
            } else {
                $status = 'incorrect';
                $incorrectCount++;
            }

            $details[] = [
                'id' => $q['id'],
                'part' => $q['part'],
                'question_number' => $q['question_number'],
                'stem' => $q['stem'],
                'passage_title' => $q['passage_title'],
                'passage' => $q['passage'],
                'options' => $q['options'],
                'user_answer' => $userPick,
                'correct_answer' => $q['correct'],
                'status' => $status,
                'explanation' => $q['explanation'],
                'translation' => $q['translation'],
            ];
        }

        $total = count($questions);
        $scorePercent = $total > 0 ? (int) round(($correctCount / $total) * 100) : 0;

        $minutes = floor($timeSpentSeconds / 60);
        $seconds = $timeSpentSeconds % 60;
        $formattedTime = sprintf('%02d:%02d', $minutes, $seconds);
        $timeSpentText = max(1, (int) round($timeSpentSeconds / 60)) . ' phút';

        $resultData = [
            'part' => $part,
            'total' => $total,
            'correct_count' => $correctCount,
            'incorrect_count' => $incorrectCount,
            'unanswered_count' => $unansweredCount,
            'score_percent' => $scorePercent,
            'time_spent_seconds' => $timeSpentSeconds,
            'time_spent_formatted' => $formattedTime,
            'time_spent_text' => $timeSpentText,
            'previous_percent' => '40%',
            'submitted_at' => now()->format('H:i • d/m/Y'),
            'details' => $details,
        ];

        session(['reading_practice_result' => $resultData]);

        return redirect()->route('luyen-tap.doc.ket-qua');
    }

    /**
     * Màn hình kết quả luyện tập Đọc
     */
    public function result(Request $request): View
    {
        $result = session('reading_practice_result');

        // Nếu người dùng vào thẳng không qua submit, tạo dữ liệu thực tế từ ngân hàng câu hỏi
        if (! $result) {
            $bank = array_slice(ReadingQuestionBank::getQuestions(5), 0, 10);
            $correctCount = 8;
            $incorrectCount = 2;
            $unansweredCount = 0;
            $details = [];

            foreach ($bank as $index => $q) {
                if ($index < 8) {
                    $status = 'correct';
                    $userPick = $q['correct'];
                } else {
                    $status = 'incorrect';
                    $wrongKeys = array_diff(array_keys($q['options']), [$q['correct']]);
                    $userPick = reset($wrongKeys);
                }

                $details[] = [
                    'id' => $q['id'],
                    'part' => $q['part'],
                    'question_number' => $q['question_number'],
                    'stem' => $q['stem'],
                    'passage_title' => $q['passage_title'],
                    'passage' => $q['passage'],
                    'options' => $q['options'],
                    'user_answer' => $userPick,
                    'correct_answer' => $q['correct'],
                    'status' => $status,
                    'explanation' => $q['explanation'],
                    'translation' => $q['translation'],
                ];
            }

            $result = [
                'part' => 5,
                'total' => 10,
                'correct_count' => $correctCount,
                'incorrect_count' => $incorrectCount,
                'unanswered_count' => $unansweredCount,
                'score_percent' => 80,
                'time_spent_seconds' => 332,
                'time_spent_formatted' => '05:32',
                'time_spent_text' => '5 phút',
                'previous_percent' => '40%',
                'submitted_at' => now()->format('H:i • d/m/Y'),
                'details' => $details,
            ];
        }

        return view('pages.doc-ket-qua', [
            'result' => $result,
        ]);
    }
}
