<?php

namespace App\Services;

use App\Models\ExamSection;
use App\Models\TestAttempt;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HistoryService
{
    /**
     * Phân tích và trả về phạm vi ngày của bộ lọc cùng kỳ đối chiếu liền trước.
     *
     * @return array{start: ?Carbon, end: Carbon, prev_start: ?Carbon, prev_end: ?Carbon, label: string}
     */
    public function parseDateRange(string $range): array
    {
        $now = now();

        return match ($range) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'prev_start' => $now->copy()->subDay()->startOfDay(),
                'prev_end' => $now->copy()->subDay()->endOfDay(),
                'label' => 'Hôm nay',
            ],
            '7days' => [
                'start' => $now->copy()->subDays(7),
                'end' => $now,
                'prev_start' => $now->copy()->subDays(14),
                'prev_end' => $now->copy()->subDays(7),
                'label' => '7 ngày qua',
            ],
            '90days' => [
                'start' => $now->copy()->subDays(90),
                'end' => $now,
                'prev_start' => $now->copy()->subDays(180),
                'prev_end' => $now->copy()->subDays(90),
                'label' => '90 ngày qua',
            ],
            'all' => [
                'start' => null,
                'end' => $now,
                'prev_start' => null,
                'prev_end' => null,
                'label' => 'Tất cả thời gian',
            ],
            default => [ // '30days'
                'start' => $now->copy()->subDays(30),
                'end' => $now,
                'prev_start' => $now->copy()->subDays(60),
                'prev_end' => $now->copy()->subDays(30),
                'label' => '30 ngày qua',
            ],
        };
    }

    /**
     * Thống kê 4 thẻ số liệu tổng hợp của người dùng.
     *
     * @return array{
     *     activity_count: int,
     *     accuracy_percent: ?int,
     *     accuracy_delta: ?float,
     *     accuracy_delta_direction: ?string,
     *     study_time_seconds: int,
     *     study_time_text: string,
     *     accumulated_wrong_count: int,
     *     can_review_weak_points: bool
     * }
     */
    public function getStatistics(int $userId, string $range): array
    {
        $dateRange = $this->parseDateRange($range);

        // 1. Hoạt động ghi nhận
        $activityQuery = TestAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED']);

        if ($dateRange['start'] !== null) {
            $activityQuery->where('started_at', '>=', $dateRange['start']);
        }
        $activityCount = $activityQuery->count();

        // 2. Tỉ lệ đúng trung bình trắc nghiệm
        $currentAccuracy = $this->calculateAccuracyForWindow($userId, $dateRange['start'], $dateRange['end']);
        $accuracyPercent = $currentAccuracy !== null ? (int) round($currentAccuracy) : null;

        $accuracyDelta = null;
        $accuracyDeltaDirection = null;

        if ($dateRange['prev_start'] !== null && $currentAccuracy !== null) {
            $prevAccuracy = $this->calculateAccuracyForWindow($userId, $dateRange['prev_start'], $dateRange['prev_end']);
            if ($prevAccuracy !== null) {
                $accuracyDelta = round($currentAccuracy - $prevAccuracy, 1);
                $accuracyDeltaDirection = $accuracyDelta > 0 ? 'up' : ($accuracyDelta < 0 ? 'down' : 'flat');
            }
        }

        // 3. Thời gian học tập
        $timeQuery = TestAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED']);

        if ($dateRange['start'] !== null) {
            $timeQuery->where('started_at', '>=', $dateRange['start']);
        }

        $attempts = $timeQuery->get(['started_at', 'ended_at', 'submitted_at']);
        $totalSeconds = 0;

        foreach ($attempts as $attempt) {
            $end = $attempt->ended_at ?? $attempt->submitted_at;
            if ($attempt->started_at && $end) {
                $diff = $attempt->started_at->diffInSeconds($end, false);
                if ($diff > 0) {
                    $totalSeconds += $diff;
                }
            }
        }

        if ($totalSeconds <= 0) {
            $studyTimeText = '0 phút';
        } else {
            $hours = intdiv($totalSeconds, 3600);
            $minutes = intdiv($totalSeconds % 3600, 60);
            if ($hours > 0) {
                $studyTimeText = "{$hours}h {$minutes}m";
            } else {
                $studyTimeText = "{$minutes}m";
            }
        }

        // 4. Câu sai tích lũy trắc nghiệm (toàn bộ lịch sử luyện tập)
        $accumulatedWrongCount = $this->countAccumulatedWrongQuestions($userId);

        return [
            'activity_count' => $activityCount,
            'accuracy_percent' => $accuracyPercent,
            'accuracy_delta' => $accuracyDelta,
            'accuracy_delta_direction' => $accuracyDeltaDirection,
            'study_time_seconds' => $totalSeconds,
            'study_time_text' => $studyTimeText,
            'accumulated_wrong_count' => $accumulatedWrongCount,
            'can_review_weak_points' => $accumulatedWrongCount > 0,
        ];
    }

    /**
     * Tính tỉ lệ đúng trung bình (MCQ) trong một khoảng thời gian.
     */
    private function calculateAccuracyForWindow(int $userId, ?Carbon $start, ?Carbon $end): ?float
    {
        $query = DB::table('attempt_response')
            ->join('attempts', 'attempt_response.attempt_id', '=', 'attempts.attempt_id')
            ->join('questions', 'attempt_response.question_id', '=', 'questions.question_id')
            ->join('question_type', 'questions.question_type_id', '=', 'question_type.question_type_id')
            ->leftJoin('answer_option', function ($join): void {
                $join->on('questions.question_id', '=', 'answer_option.question_id')
                    ->where('answer_option.is_correct', '=', 1);
            })
            ->where('attempts.user_id', $userId)
            ->whereIn('attempts.status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->where('question_type.response_mode', 'SINGLE_CHOICE');

        if ($start !== null) {
            $query->where('attempts.started_at', '>=', $start);
        }
        if ($end !== null) {
            $query->where('attempts.started_at', '<=', $end);
        }

        $stats = $query->selectRaw('
            COUNT(attempt_response.response_id) as total_questions,
            SUM(CASE
                WHEN attempt_response.text_response IS NOT NULL
                     AND UPPER(TRIM(attempt_response.text_response)) = UPPER(TRIM(answer_option.label))
                THEN 1
                ELSE 0
            END) as correct_questions
        ')->first();

        $total = (int) ($stats->total_questions ?? 0);
        $correct = (int) ($stats->correct_questions ?? 0);

        if ($total === 0) {
            return null;
        }

        return ($correct / $total) * 100;
    }

    /**
     * Đếm tổng số câu hỏi trắc nghiệm đã từng làm sai (distinct question_id).
     */
    public function countAccumulatedWrongQuestions(int $userId): int
    {
        return DB::table('attempt_response')
            ->join('attempts', 'attempt_response.attempt_id', '=', 'attempts.attempt_id')
            ->join('questions', 'attempt_response.question_id', '=', 'questions.question_id')
            ->join('question_type', 'questions.question_type_id', '=', 'question_type.question_type_id')
            ->leftJoin('answer_option', function ($join): void {
                $join->on('questions.question_id', '=', 'answer_option.question_id')
                    ->where('answer_option.is_correct', '=', 1);
            })
            ->where('attempts.user_id', $userId)
            ->whereIn('attempts.status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->where('attempts.session_type', TestAttempt::SESSION_TYPE_PRACTICE)
            ->where('question_type.response_mode', 'SINGLE_CHOICE')
            ->whereNotNull('attempt_response.text_response')
            ->whereRaw("TRIM(attempt_response.text_response) != ''")
            ->where(function ($sub): void {
                $sub->whereNull('answer_option.label')
                    ->orWhereRaw('UPPER(TRIM(attempt_response.text_response)) != UPPER(TRIM(answer_option.label))');
            })
            ->distinct('attempt_response.question_id')
            ->count('attempt_response.question_id');
    }

    /**
     * Lấy danh sách câu hỏi làm sai tích lũy (phục vụ tái sử dụng cho tab Câu sai trang Cải thiện).
     *
     * @return Collection<int, object>
     */
    public function getAccumulatedWrongQuestions(int $userId): Collection
    {
        return DB::table('attempt_response')
            ->join('attempts', 'attempt_response.attempt_id', '=', 'attempts.attempt_id')
            ->join('questions', 'attempt_response.question_id', '=', 'questions.question_id')
            ->join('question_type', 'questions.question_type_id', '=', 'question_type.question_type_id')
            ->leftJoin('exam_section', 'questions.section_id', '=', 'exam_section.section_id')
            ->leftJoin('skills', 'exam_section.skill_id', '=', 'skills.skill_id')
            ->leftJoin('answer_option', function ($join): void {
                $join->on('questions.question_id', '=', 'answer_option.question_id')
                    ->where('answer_option.is_correct', '=', 1);
            })
            ->where('attempts.user_id', $userId)
            ->whereIn('attempts.status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->where('attempts.session_type', TestAttempt::SESSION_TYPE_PRACTICE)
            ->where('question_type.response_mode', 'SINGLE_CHOICE')
            ->whereNotNull('attempt_response.text_response')
            ->whereRaw("TRIM(attempt_response.text_response) != ''")
            ->where(function ($sub): void {
                $sub->whereNull('answer_option.label')
                    ->orWhereRaw('UPPER(TRIM(attempt_response.text_response)) != UPPER(TRIM(answer_option.label))');
            })
            ->select([
                'questions.question_id',
                'questions.content',
                'questions.explanation',
                'exam_section.code as section_code',
                'exam_section.name as section_name',
                'skills.code as skill_code',
                'skills.skill_name',
                DB::raw('MAX(attempt_response.submitted_at) as last_attempted_at'),
            ])
            ->groupBy('questions.question_id', 'questions.content', 'questions.explanation', 'exam_section.code', 'exam_section.name', 'skills.code', 'skills.skill_name')
            ->orderBy('last_attempted_at', 'desc')
            ->get();
    }

    /**
     * Đếm số lượng phiên trên 3 tab (Tất cả, Bài học, Luyện đề) theo bộ lọc hiện tại.
     *
     * @return array{all: int, lesson: int, exam: int}
     */
    public function getTypeCounts(int $userId, string $range, ?string $partFilter = null, ?string $search = null): array
    {
        $dateRange = $this->parseDateRange($range);

        $baseQuery = TestAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED']);

        if ($dateRange['start'] !== null) {
            $baseQuery->where('started_at', '>=', $dateRange['start']);
        }

        if ($partFilter && $partFilter !== 'all') {
            $baseQuery->whereHas('attemptResponses.question.section', function ($s) use ($partFilter): void {
                $s->where('code', $partFilter);
            });
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $baseQuery->where(function ($q) use ($term): void {
                $q->whereHas('testForm', fn ($tf) => $tf->where('title', 'like', "%{$term}%"))
                    ->orWhereHas('attemptResponses.question.section', function ($s) use ($term): void {
                        $s->where('name', 'like', "%{$term}%")
                            ->orWhere('code', 'like', "%{$term}%")
                            ->orWhereHas('skill', function ($sk) use ($term): void {
                                $sk->where('skill_name', 'like', "%{$term}%")
                                    ->orWhere('code', 'like', "%{$term}%");
                            });
                    });
            });
        }

        $allCount = (clone $baseQuery)->count();

        $lessonCount = (clone $baseQuery)
            ->where('session_type', TestAttempt::SESSION_TYPE_PRACTICE)
            ->whereNull('test_form_id')
            ->count();

        $examCount = (clone $baseQuery)
            ->where(function ($q): void {
                $q->where('session_type', TestAttempt::SESSION_TYPE_EXAM)
                    ->orWhereNotNull('test_form_id');
            })
            ->count();

        return [
            'all' => $allCount,
            'lesson' => $lessonCount,
            'exam' => $examCount,
        ];
    }

    /**
     * Lấy danh sách Part thực tế từ exam_section và skills.
     *
     * @return array<int, array{code: string, name: string, skill_name: string}>
     */
    public function getAvailableParts(): array
    {
        return ExamSection::query()
            ->with('skill')
            ->orderBy('skill_id')
            ->orderBy('sequence_no')
            ->get()
            ->map(fn ($section) => [
                'code' => (string) $section->code,
                'name' => (string) $section->name,
                'skill_name' => (string) ($section->skill?->skill_name ?? ''),
            ])
            ->values()
            ->all();
    }

    /**
     * Truy vấn danh sách lịch sử phân trang (10 bản ghi/trang).
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedHistory(int $userId, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $range = (string) ($filters['range'] ?? '30days');
        $type = (string) ($filters['type'] ?? 'all');
        $part = (string) ($filters['part'] ?? 'all');
        $sort = (string) ($filters['sort'] ?? 'newest');
        $search = isset($filters['q']) ? trim((string) $filters['q']) : null;

        $dateRange = $this->parseDateRange($range);

        $query = TestAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->with([
                'testForm',
                'scoreResult',
                'attemptResponses' => fn ($r) => $r->orderBy('sequence_no', 'asc')->with([
                    'question.section.skill',
                    'question.options',
                    'question.questionType',
                ]),
            ]);

        // 1. Lọc theo thời gian
        if ($dateRange['start'] !== null) {
            $query->where('started_at', '>=', $dateRange['start']);
        }

        // 2. Lọc theo loại bài
        if ($type === 'lesson') {
            $query->where('session_type', TestAttempt::SESSION_TYPE_PRACTICE)
                ->whereNull('test_form_id');
        } elseif ($type === 'exam') {
            $query->where(function ($q): void {
                $q->where('session_type', TestAttempt::SESSION_TYPE_EXAM)
                    ->orWhereNotNull('test_form_id');
            });
        }

        // 3. Lọc theo Part
        if ($part !== 'all' && $part !== '') {
            $query->whereHas('attemptResponses.question.section', function ($s) use ($part): void {
                $s->where('code', $part);
            });
        }

        // 4. Tìm kiếm từ khóa
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->whereHas('testForm', fn ($tf) => $tf->where('title', 'like', "%{$search}%"))
                    ->orWhereHas('attemptResponses.question.section', function ($s) use ($search): void {
                        $s->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhereHas('skill', function ($sk) use ($search): void {
                                $sk->where('skill_name', 'like', "%{$search}%")
                                    ->orWhere('code', 'like', "%{$search}%");
                            });
                    });
            });
        }

        // 5. Sắp xếp
        if ($sort === 'oldest') {
            $query->orderBy('started_at', 'asc')->orderBy('attempt_id', 'asc');
        } elseif ($sort === 'score_desc' || $sort === 'score_asc') {
            $direction = $sort === 'score_desc' ? 'desc' : 'asc';
            $query->leftJoin('score_result', 'attempts.attempt_id', '=', 'score_result.attempt_id')
                ->select('attempts.*')
                ->orderBy('score_result.total_score', $direction)
                ->orderBy('attempts.started_at', 'desc')
                ->orderBy('attempts.attempt_id', 'desc');
        } else { // 'newest'
            $query->orderBy('started_at', 'desc')->orderBy('attempt_id', 'desc');
        }

        $paginator = $query->paginate($perPage)->withQueryString();

        // 6. Transform items for clean UI rendering
        $paginator->getCollection()->transform(function (TestAttempt $attempt) use ($userId): array {
            return $this->formatAttemptCard($attempt, $userId);
        });

        return $paginator;
    }

    /**
     * Chuẩn hóa dữ liệu một card lịch sử cho giao diện.
     *
     * @return array<string, mixed>
     */
    private function formatAttemptCard(TestAttempt $attempt, int $userId): array
    {
        $isExam = ($attempt->session_type === TestAttempt::SESSION_TYPE_EXAM || $attempt->test_form_id !== null);

        $firstResponse = $attempt->attemptResponses->first();
        $firstQuestion = $firstResponse?->question;
        $section = $firstQuestion?->section;
        $skill = $section?->skill;

        $sectionCode = $section?->code ? (string) $section->code : null;
        $skillCode = $skill?->code ? (string) $skill->code : null;

        // Tên hiển thị
        if ($isExam) {
            $title = $attempt->testForm?->title ?: 'Đề thi thử TOEIC';
            $iconClass = 'ls-activity-icon--exam';
            $type = 'exam';
        } else {
            $title = $section?->name ?: ('Luyện tập '.($skill?->skill_name ?? 'kỹ năng'));
            $partLower = strtolower($sectionCode ?? 'part5');
            $iconClass = 'ls-activity-icon--'.$partLower;
            $type = 'lesson';
        }

        // Tính câu đúng, câu sai, chưa trả lời
        $totalQuestions = $attempt->attemptResponses->count();
        $correctCount = 0;
        $incorrectCount = 0;
        $unansweredCount = 0;
        $hasMcq = false;

        foreach ($attempt->attemptResponses as $response) {
            $q = $response->question;
            if (! $q) {
                continue;
            }

            $isSingleChoice = ($q->questionType?->response_mode === 'SINGLE_CHOICE');
            if ($isSingleChoice) {
                $hasMcq = true;
                $userPick = $response->text_response ? strtoupper(trim((string) $response->text_response)) : null;
                $correctOpt = $q->options->firstWhere('is_correct', true);
                $correctLabel = $correctOpt ? strtoupper(trim((string) $correctOpt->label)) : null;

                if ($userPick === null || $userPick === '') {
                    $unansweredCount++;
                } elseif ($correctLabel !== null && $userPick === $correctLabel) {
                    $correctCount++;
                } else {
                    $incorrectCount++;
                }
            } else {
                if ($response->score !== null && (float) $response->score > 0) {
                    $correctCount++;
                } else {
                    $unansweredCount++;
                }
            }
        }

        // Tỉ lệ đúng / Điểm số
        $accuracyPercent = ($hasMcq && $totalQuestions > 0) ? (int) round(($correctCount / $totalQuestions) * 100) : null;

        if ($isExam) {
            $scoreVal = $attempt->scoreResult?->total_score;
            $scoreText = $scoreVal !== null ? ((int) round((float) $scoreVal).' điểm') : 'Đang chấm';
            $scoreLabel = 'ĐIỂM SỐ';
            $submetaText = $totalQuestions > 0 ? "{$correctCount}/{$totalQuestions} câu đúng" : 'Đã hoàn thành';
        } elseif ($skillCode === 'SPK' || $skillCode === 'WRT') {
            $scoreVal = $attempt->scoreResult?->total_score;
            $scoreText = $scoreVal !== null ? (round((float) $scoreVal, 1).' đ') : 'Đang chấm';
            $scoreLabel = 'KẾT QUẢ AI';
            $submetaText = $scoreVal !== null ? 'Đã chấm điểm hoàn tất' : 'Đang chờ xử lý';
        } else {
            $scoreText = $accuracyPercent !== null ? "{$accuracyPercent}%" : '0%';
            $scoreLabel = 'TỈ LỆ ĐÚNG';
            $submetaText = "{$correctCount}/{$totalQuestions} câu đúng";
        }

        // So sánh với lần làm trước
        $delta = $this->calculateComparisonDelta($attempt, $userId, $isExam, $sectionCode, $accuracyPercent);

        // Định dạng thời gian
        $startedAt = $attempt->started_at ?: Carbon::now();
        $timeFormatted = $startedAt->format('H:i • d/m');

        // Định dạng nhóm ngày
        if ($startedAt->isToday()) {
            $dateGroupKey = 'today';
            $dateGroupLabel = 'HÔM NAY';
        } elseif ($startedAt->isYesterday()) {
            $dateGroupKey = 'yesterday';
            $dateGroupLabel = 'HÔM QUA';
        } else {
            $dateGroupKey = $startedAt->format('Y-m-d');
            $dateGroupLabel = $startedAt->format('d/m/Y');
        }

        // URLs
        $resultUrl = route('lich-su.show', $attempt->attempt_id);
        $retryUrl = route('lich-su.retry', $attempt->attempt_id);

        return [
            'attempt_id' => (int) $attempt->attempt_id,
            'session_type' => (string) $attempt->session_type,
            'type' => $type,
            'is_exam' => $isExam,
            'title' => $title,
            'part_code' => $sectionCode,
            'icon_class' => $iconClass,
            'total_questions' => $totalQuestions,
            'correct_count' => $correctCount,
            'incorrect_count' => $incorrectCount,
            'unanswered_count' => $unansweredCount,
            'accuracy_percent' => $accuracyPercent,
            'score_text' => $scoreText,
            'score_label' => $scoreLabel,
            'submeta_text' => $submetaText,
            'time_formatted' => $timeFormatted,
            'date_group_key' => $dateGroupKey,
            'date_group_label' => $dateGroupLabel,
            'delta' => $delta,
            'result_url' => $resultUrl,
            'retry_url' => $retryUrl,
        ];
    }

    /**
     * So sánh kết quả với lượt làm hợp lệ gần nhất trước đó của cùng Part/Đề thi.
     *
     * @return array{type: string, text: string, class: string}
     */
    private function calculateComparisonDelta(
        TestAttempt $attempt,
        int $userId,
        bool $isExam,
        ?string $sectionCode,
        ?int $accuracyPercent
    ): array {
        if ($isExam) {
            if ($attempt->test_form_id === null) {
                return ['type' => 'neutral', 'text' => '• Lần đầu', 'class' => 'ls-delta--neutral'];
            }

            $prev = TestAttempt::query()
                ->where('user_id', $userId)
                ->where('attempt_id', '<', $attempt->attempt_id)
                ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
                ->where('test_form_id', $attempt->test_form_id)
                ->with('scoreResult')
                ->orderBy('started_at', 'desc')
                ->orderBy('attempt_id', 'desc')
                ->first();

            if (! $prev || ! $prev->scoreResult || ! $attempt->scoreResult) {
                return ['type' => 'neutral', 'text' => '• Lần đầu', 'class' => 'ls-delta--neutral'];
            }

            $curScore = (float) $attempt->scoreResult->total_score;
            $prevScore = (float) $prev->scoreResult->total_score;
            $diff = round($curScore - $prevScore, 1);

            if ($diff > 0) {
                return ['type' => 'positive', 'text' => "+{$diff} so với lần trước", 'class' => 'ls-delta--positive'];
            } elseif ($diff < 0) {
                return ['type' => 'negative', 'text' => "{$diff} so với lần trước", 'class' => 'ls-delta--negative'];
            }

            return ['type' => 'neutral', 'text' => '0 so với lần trước', 'class' => 'ls-delta--neutral'];
        }

        // Bài luyện tập
        if (! $sectionCode || $accuracyPercent === null) {
            return ['type' => 'neutral', 'text' => '• Lần đầu', 'class' => 'ls-delta--neutral'];
        }

        $prev = TestAttempt::query()
            ->where('user_id', $userId)
            ->where('attempt_id', '<', $attempt->attempt_id)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->where('session_type', TestAttempt::SESSION_TYPE_PRACTICE)
            ->whereHas('attemptResponses.question.section', function ($s) use ($sectionCode): void {
                $s->where('code', $sectionCode);
            })
            ->with([
                'attemptResponses.question.options',
                'attemptResponses.question.questionType',
            ])
            ->orderBy('started_at', 'desc')
            ->orderBy('attempt_id', 'desc')
            ->first();

        if (! $prev) {
            return ['type' => 'neutral', 'text' => '• Lần đầu', 'class' => 'ls-delta--neutral'];
        }

        // Tính accuracy của bài trước
        $prevTotal = $prev->attemptResponses->count();
        $prevCorrect = 0;
        foreach ($prev->attemptResponses as $r) {
            $q = $r->question;
            if ($q && $q->questionType?->response_mode === 'SINGLE_CHOICE') {
                $pick = $r->text_response ? strtoupper(trim((string) $r->text_response)) : null;
                $cOpt = $q->options->firstWhere('is_correct', true);
                $cLabel = $cOpt ? strtoupper(trim((string) $cOpt->label)) : null;
                if ($pick !== null && $cLabel !== null && $pick === $cLabel) {
                    $prevCorrect++;
                }
            }
        }

        $prevAccuracy = $prevTotal > 0 ? (int) round(($prevCorrect / $prevTotal) * 100) : 0;
        $diff = $accuracyPercent - $prevAccuracy;

        if ($diff > 0) {
            return ['type' => 'positive', 'text' => "+{$diff}% so với lần trước", 'class' => 'ls-delta--positive'];
        } elseif ($diff < 0) {
            return ['type' => 'negative', 'text' => "{$diff}% so với lần trước", 'class' => 'ls-delta--negative'];
        }

        return ['type' => 'neutral', 'text' => '0% so với lần trước', 'class' => 'ls-delta--neutral'];
    }
}
