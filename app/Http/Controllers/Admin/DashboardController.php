<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\User;
use App\Models\Vocabulary;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Trang chủ quản trị: tổng hợp chỉ số vận hành từ CSDL StudyPrep.
     */
    public function index(): View
    {
        $weekStart = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd = now()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $learnerStats = [
            'total' => User::query()->count(),
            'active' => User::query()->active()->count(),
            'locked' => User::query()->locked()->count(),
        ];

        $questionStats = [
            'total' => Question::query()->count(),
            'pending' => Question::query()->pending()->count(),
            'active' => Question::query()->active()->count(),
        ];

        $attemptStats = [
            'today' => TestAttempt::query()->whereDate('started_at', today())->count(),
            'week' => TestAttempt::query()
                ->whereBetween('started_at', [$weekStart, $weekEnd])
                ->count(),
        ];

        $vocabularyTotal = Vocabulary::query()->count();

        $recentAttempts = TestAttempt::query()
            ->with([
                'user:user_id,full_name,email,avatar_url',
                'testForm:test_form_id,title',
                'scoreResult:result_id,attempt_id,listening_score,reading_score,total_score,graded_at',
            ])
            ->orderByDesc('started_at')
            ->orderByDesc('attempt_id')
            ->limit(5)
            ->get();

        $pendingQuestions = Question::query()
            ->pending()
            ->with([
                'section:section_id,code,name,skill_id',
                'section.skill:skill_id,code,skill_name',
                'questionType:question_type_id,name',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('question_id')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'learnerStats' => $learnerStats,
            'questionStats' => $questionStats,
            'attemptStats' => $attemptStats,
            'vocabularyTotal' => $vocabularyTotal,
            'recentAttempts' => $recentAttempts,
            'pendingQuestions' => $pendingQuestions,
            'weeklyChart' => $this->weeklyAttemptChart($weekStart, $weekEnd),
            'greeting' => $this->greetingFor(now()),
        ]);
    }

    /**
     * @return array{labels: list<string>, started: list<int>, submitted: list<int>}
     */
    private function weeklyAttemptChart(CarbonInterface $weekStart, CarbonInterface $weekEnd): array
    {
        $startedByDay = $this->countAttemptsByDate('started_at', $weekStart, $weekEnd);
        $submittedByDay = $this->countAttemptsByDate('submitted_at', $weekStart, $weekEnd);

        $labels = [];
        $started = [];
        $submitted = [];

        for ($day = $weekStart->copy(); $day->lte($weekEnd); $day->addDay()) {
            $key = $day->toDateString();
            $labels[] = $this->vietnameseWeekdayLabel($day);
            $started[] = (int) $startedByDay->get($key, 0);
            $submitted[] = (int) $submittedByDay->get($key, 0);
        }

        return [
            'labels' => $labels,
            'started' => $started,
            'submitted' => $submitted,
        ];
    }

    /**
     * @return Collection<string, int>
     */
    private function countAttemptsByDate(string $column, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $allowed = ['started_at', 'submitted_at'];
        $column = in_array($column, $allowed, true) ? $column : 'started_at';

        return TestAttempt::query()
            ->whereNotNull($column)
            ->whereBetween($column, [$from, $to])
            ->selectRaw("DATE({$column}) as day, COUNT(*) as aggregate")
            ->groupByRaw("DATE({$column})")
            ->orderBy('day')
            ->pluck('aggregate', 'day')
            ->map(fn (mixed $value): int => (int) $value);
    }

    private function vietnameseWeekdayLabel(CarbonInterface $day): string
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

        $label = $names[$day->dayOfWeekIso] ?? $day->isoFormat('dddd');

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

        $title = match (true) {
            $hour < 12 => 'Chào buổi sáng',
            $hour < 18 => 'Chào buổi chiều',
            default => 'Chào buổi tối',
        };

        return [
            'title' => $title,
            'subtitle' => 'Tổng quan chỉ số vận hành đào tạo, ngân hàng câu hỏi & hiệu suất luyện thi hôm nay.',
        ];
    }
}
