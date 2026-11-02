<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\ScoreResult;
use App\Models\TestAttempt;
use App\Models\User;
use App\Models\Vocabulary;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $weekStart = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd = now()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $userStats = [
            'total' => User::query()->count(),
            'active' => User::query()->active()->count(),
            'locked' => User::query()->locked()->count(),
        ];

        $questionStats = [
            'total' => Question::query()->count(),
            'pending' => Question::query()->pending()->count(),
            'active' => Question::query()->active()->count(),
        ];

        $submittedAttempts = TestAttempt::query()->whereNotNull('submitted_at');
        $attemptStats = [
            'today' => (clone $submittedAttempts)->whereDate('submitted_at', today())->count(),
            'week' => (clone $submittedAttempts)->whereBetween('submitted_at', [$weekStart, $weekEnd])->count(),
        ];

        $feedbackStats = [
            'pending' => Feedback::query()->where('status', 'PENDING')->count(),
        ];

        $recentAttempts = TestAttempt::query()
            ->with([
                'user:user_id,full_name,email,avatar_url',
                'testForm:test_form_id,title',
                'scoreResult:result_id,attempt_id,listening_score,reading_score,total_score,graded_at',
            ])
            ->orderByDesc('submitted_at')
            ->orderByDesc('attempt_id')
            ->limit(5)
            ->get();

        $pendingFeedback = Feedback::query()
            ->with([
                'question.section.skill',
                'user:user_id,full_name,email',
            ])
            ->where('status', 'PENDING')
            ->orderByDesc('created_at')
            ->orderByDesc('feedback_id')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'userStats' => $userStats,
            'questionStats' => $questionStats,
            'attemptStats' => $attemptStats,
            'feedbackStats' => $feedbackStats,
            'vocabularyTotal' => Vocabulary::query()->count(),
            'recentAttempts' => $recentAttempts,
            'pendingFeedback' => $pendingFeedback,
            'weeklyChart' => $this->weeklySubmissionChart($weekStart, $weekEnd),
            'greeting' => $this->greetingFor(now()),
        ]);
    }

    /**
     * @return array{labels: list<string>, listeningReading: list<int>, writingSpeaking: list<int>}
     */
    private function weeklySubmissionChart(CarbonInterface $weekStart, CarbonInterface $weekEnd): array
    {
        $dailyScores = ScoreResult::query()
            ->join('attempts', 'attempts.attempt_id', '=', 'score_result.attempt_id')
            ->whereNotNull('attempts.submitted_at')
            ->whereBetween('attempts.submitted_at', [$weekStart, $weekEnd])
            ->selectRaw('DATE(attempts.submitted_at) AS day')
            ->selectRaw('COUNT(DISTINCT CASE WHEN score_result.listening_score IS NOT NULL OR score_result.reading_score IS NOT NULL THEN attempts.attempt_id END) AS listening_reading')
            ->selectRaw('COUNT(DISTINCT CASE WHEN score_result.speaking_score IS NOT NULL OR score_result.writing_score IS NOT NULL THEN attempts.attempt_id END) AS writing_speaking')
            ->groupByRaw('DATE(attempts.submitted_at)')
            ->get()
            ->keyBy('day');

        $labels = [];
        $listeningReading = [];
        $writingSpeaking = [];

        for ($day = $weekStart->copy(); $day->lte($weekEnd); $day->addDay()) {
            $counts = $dailyScores->get($day->toDateString());
            $labels[] = $this->weekdayLabel($day);
            $listeningReading[] = (int) ($counts->listening_reading ?? 0);
            $writingSpeaking[] = (int) ($counts->writing_speaking ?? 0);
        }

        return [
            'labels' => $labels,
            'listeningReading' => $listeningReading,
            'writingSpeaking' => $writingSpeaking,
        ];
    }

    private function weekdayLabel(CarbonInterface $day): string
    {
        $names = [
            1 => 'Thứ Hai',
            2 => 'Thứ Ba',
            3 => 'Thứ Tư',
            4 => 'Thứ Năm',
            5 => 'Thứ Sáu',
            6 => 'Thứ Bảy',
            7 => 'Chủ Nhật',
        ];

        $label = $names[$day->dayOfWeekIso];

        if ($day->isToday()) {
            return $label.' (Hôm nay)';
        }

        if ($day->isSameDay(now()->startOfWeek(Carbon::MONDAY))) {
            return $label.' ('.$day->format('d/m').')';
        }

        return $label;
    }

    /**
     * @return array{title: string, subtitle: string}
     */
    private function greetingFor(CarbonInterface $now): array
    {
        $hour = (int) $now->format('G');

        return [
            'title' => match (true) {
                $hour < 12 => 'Chào buổi sáng',
                $hour < 18 => 'Chào buổi chiều',
                default => 'Chào buổi tối',
            },
            'subtitle' => 'Tổng quan chỉ số vận hành đào tạo, ngân hàng câu hỏi & hiệu suất luyện thi hôm nay.',
        ];
    }
}
