<?php

namespace App\Services;

use App\Models\TestAttempt;
use App\Models\UserVocabulary;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    public function __construct(
        public HistoryService $historyService
    ) {}

    /**
     * Chuẩn hóa chuỗi tham số bộ lọc thời gian.
     */
    public function normalizeRange(string $range): string
    {
        return match ($range) {
            '7', '7days' => '7days',
            '30', '30days' => '30days',
            '90', '90days' => '90days',
            'all' => 'all',
            default => '90days',
        };
    }

    /**
     * Lấy các chỉ số tổng quan đầu trang Phân tích.
     *
     * @return array{
     *     highest_score: ?int,
     *     highest_test_name: ?string,
     *     highest_test_date: ?string,
     *     avg_accuracy: int,
     *     correct_questions: int,
     *     total_questions: int,
     *     weakest_skill: string,
     *     weakest_score_info: string,
     *     wrong_questions_count: int
     * }
     */
    public function getOverviewStats(int $userId): array
    {
        // 1. Điểm cao nhất từ các bài Full Test đã chấm điểm
        $bestAttempt = TestAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->where('session_type', TestAttempt::SESSION_TYPE_FULL_TEST)
            ->whereHas('scoreResult', function ($q): void {
                $q->whereNotNull('total_score');
            })
            ->with(['scoreResult', 'testForm'])
            ->join('score_result', 'attempts.attempt_id', '=', 'score_result.attempt_id')
            ->select('attempts.*')
            ->orderBy('score_result.total_score', 'desc')
            ->orderBy('attempts.started_at', 'desc')
            ->first();

        $highestScore = null;
        $highestTestName = null;
        $highestTestDate = null;

        if ($bestAttempt && $bestAttempt->scoreResult) {
            $highestScore = (int) round((float) $bestAttempt->scoreResult->total_score);
            $highestTestName = (string) ($bestAttempt->testForm?->title ?: 'Bài thi Full Test');
            $highestTestDate = $bestAttempt->started_at ? $bestAttempt->started_at->format('d/m') : null;
        }

        // 2. Tỉ lệ đúng trung bình trắc nghiệm (LC & RC từ PRACTICE, PRACTICE_PART, FULL_TEST)
        $mcqStats = DB::table('attempt_response')
            ->join('attempts', 'attempt_response.attempt_id', '=', 'attempts.attempt_id')
            ->join('questions', 'attempt_response.question_id', '=', 'questions.question_id')
            ->join('question_type', 'questions.question_type_id', '=', 'question_type.question_type_id')
            ->join('exam_section', 'questions.section_id', '=', 'exam_section.section_id')
            ->join('skills', 'exam_section.skill_id', '=', 'skills.skill_id')
            ->leftJoin('answer_option', function ($join): void {
                $join->on('questions.question_id', '=', 'answer_option.question_id')
                    ->where('answer_option.is_correct', '=', 1);
            })
            ->where('attempts.user_id', $userId)
            ->whereIn('attempts.status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->whereIn('attempts.session_type', [
                TestAttempt::SESSION_TYPE_PRACTICE,
                TestAttempt::SESSION_TYPE_PRACTICE_PART,
                TestAttempt::SESSION_TYPE_FULL_TEST,
                TestAttempt::SESSION_TYPE_EXAM,
            ])
            ->where('question_type.response_mode', 'SINGLE_CHOICE')
            ->whereIn('skills.code', ['LC', 'RC'])
            ->selectRaw('
                COUNT(attempt_response.response_id) as total_questions,
                SUM(CASE
                    WHEN attempt_response.text_response IS NOT NULL
                         AND UPPER(TRIM(attempt_response.text_response)) = UPPER(TRIM(answer_option.label))
                    THEN 1
                    ELSE 0
                END) as correct_questions
            ')
            ->first();

        $totalQuestions = (int) ($mcqStats->total_questions ?? 0);
        $correctQuestions = (int) ($mcqStats->correct_questions ?? 0);
        $avgAccuracy = $totalQuestions > 0 ? (int) round(($correctQuestions / $totalQuestions) * 100) : 0;

        // 3. Câu sai tích lũy trắc nghiệm (đồng bộ với Lịch sử & Cải thiện)
        $wrongCount = $this->historyService->countAccumulatedWrongQuestions($userId);

        // 4. Điểm yếu nhất (Giữ nguyên bố cục, hiển thị trạng thái chưa có dữ liệu AI cho BE-023)
        return [
            'highest_score' => $highestScore,
            'highest_test_name' => $highestTestName,
            'highest_test_date' => $highestTestDate,
            'avg_accuracy' => $avgAccuracy,
            'correct_questions' => $correctQuestions,
            'total_questions' => $totalQuestions,
            'weakest_skill' => 'Chưa có dữ liệu',
            'weakest_score_info' => 'Dữ liệu phân tích điểm yếu sẽ được cập nhật khi có kết quả đánh giá AI.',
            'wrong_questions_count' => $wrongCount,
        ];
    }

    /**
     * Lấy dữ liệu biểu đồ tỉ lệ đúng theo Part (Part 1 -> Part 7).
     * Chỉ tính câu hỏi trắc nghiệm của Listening & Reading đã làm.
     * Quy định: Cần tối thiểu 5 câu để có dữ liệu thống kê.
     *
     * @return array<int, array{part: string, rate: ?int, has_data: bool, correct: int, total: int}>
     */
    public function getPartAccuracyList(int $userId): array
    {
        $rows = DB::table('attempt_response')
            ->join('attempts', 'attempt_response.attempt_id', '=', 'attempts.attempt_id')
            ->join('questions', 'attempt_response.question_id', '=', 'questions.question_id')
            ->join('question_type', 'questions.question_type_id', '=', 'question_type.question_type_id')
            ->join('exam_section', 'questions.section_id', '=', 'exam_section.section_id')
            ->join('skills', 'exam_section.skill_id', '=', 'skills.skill_id')
            ->leftJoin('answer_option', function ($join): void {
                $join->on('questions.question_id', '=', 'answer_option.question_id')
                    ->where('answer_option.is_correct', '=', 1);
            })
            ->where('attempts.user_id', $userId)
            ->whereIn('attempts.status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->whereIn('attempts.session_type', [
                TestAttempt::SESSION_TYPE_PRACTICE,
                TestAttempt::SESSION_TYPE_PRACTICE_PART,
                TestAttempt::SESSION_TYPE_FULL_TEST,
                TestAttempt::SESSION_TYPE_EXAM,
            ])
            ->where('question_type.response_mode', 'SINGLE_CHOICE')
            ->whereIn('skills.code', ['LC', 'RC'])
            ->selectRaw('
                exam_section.code as section_code,
                exam_section.sequence_no,
                COUNT(attempt_response.response_id) as total_questions,
                SUM(CASE
                    WHEN attempt_response.text_response IS NOT NULL
                         AND UPPER(TRIM(attempt_response.text_response)) = UPPER(TRIM(answer_option.label))
                    THEN 1
                    ELSE 0
                END) as correct_questions
            ')
            ->groupBy('exam_section.code', 'exam_section.sequence_no')
            ->get();

        $partStats = [];
        foreach ($rows as $row) {
            $partNumber = null;
            if (preg_match('/(\d+)/', (string) $row->section_code, $m) === 1) {
                $partNumber = (int) $m[1];
            } else {
                $partNumber = (int) $row->sequence_no;
            }

            if ($partNumber >= 1 && $partNumber <= 7) {
                if (! isset($partStats[$partNumber])) {
                    $partStats[$partNumber] = ['total' => 0, 'correct' => 0];
                }
                $partStats[$partNumber]['total'] += (int) $row->total_questions;
                $partStats[$partNumber]['correct'] += (int) $row->correct_questions;
            }
        }

        $result = [];
        for ($p = 1; $p <= 7; $p++) {
            $total = $partStats[$p]['total'] ?? 0;
            $correct = $partStats[$p]['correct'] ?? 0;
            $hasData = ($total >= 5);
            $rate = $hasData ? (int) round(($correct / $total) * 100) : null;

            $result[] = [
                'part' => "Part {$p}",
                'rate' => $rate,
                'has_data' => $hasData,
                'correct' => $correct,
                'total' => $total,
            ];
        }

        return $result;
    }

    /**
     * Lấy dữ liệu bài thi Full Test và biểu đồ điểm số theo thời gian.
     *
     * @return array{
     *     has_attempts: bool,
     *     is_single_attempt: bool,
     *     attempts_count: int,
     *     filter_range: string,
     *     points: array<int, array<string, mixed>>,
     *     total_path: string,
     *     listening_path: string,
     *     reading_path: string,
     *     recent_test: ?array{
     *         listening_score: int,
     *         reading_score: int,
     *         total_score: int,
     *         max_skill_score: int,
     *         date: string,
     *         test_name: string,
     *         balance_title: string,
     *         balance_desc: string
     *     }
     * }
     */
    public function getFullTestData(int $userId, string $range = '90'): array
    {
        $normalizedRange = $this->normalizeRange($range);
        $dateRange = $this->historyService->parseDateRange($normalizedRange);

        // 1. Lần thi Full Test hoàn chỉnh gần nhất (toàn thời gian)
        $latestAttempt = TestAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->where('session_type', TestAttempt::SESSION_TYPE_FULL_TEST)
            ->whereHas('scoreResult', function ($q): void {
                $q->whereNotNull('total_score');
            })
            ->with(['scoreResult', 'testForm'])
            ->orderBy('started_at', 'desc')
            ->orderBy('attempt_id', 'desc')
            ->first();

        $recentTest = null;
        if ($latestAttempt && $latestAttempt->scoreResult) {
            $lis = (int) round((float) ($latestAttempt->scoreResult->listening_score ?? 0));
            $rea = (int) round((float) ($latestAttempt->scoreResult->reading_score ?? 0));
            $tot = (int) round((float) ($latestAttempt->scoreResult->total_score ?? ($lis + $rea)));

            $diff = abs($lis - $rea);
            if ($lis === 0 && $rea === 0) {
                $balanceTitle = 'Chưa có phân tách điểm kỹ năng';
                $balanceDesc = 'Bài thi chưa ghi nhận điểm thành phần Nghe và Đọc.';
            } elseif ($diff <= 30) {
                $balanceTitle = 'Hai kỹ năng khá cân bằng';
                $balanceDesc = "Nghe {$lis}, Đọc {$rea} — hãy giữ nhịp luyện đều cả hai.";
            } elseif ($lis > $rea) {
                $balanceTitle = 'Kỹ năng Nghe tốt hơn Đọc';
                $balanceDesc = "Nghe {$lis}, Đọc {$rea} — điểm Nghe cao hơn Đọc {$diff} điểm.";
            } else {
                $balanceTitle = 'Kỹ năng Đọc tốt hơn Nghe';
                $balanceDesc = "Nghe {$lis}, Đọc {$rea} — điểm Đọc cao hơn Nghe {$diff} điểm.";
            }

            $recentTest = [
                'listening_score' => $lis,
                'reading_score' => $rea,
                'total_score' => $tot,
                'max_skill_score' => 495,
                'date' => $latestAttempt->started_at ? $latestAttempt->started_at->format('d/m') : '---',
                'test_name' => (string) ($latestAttempt->testForm?->title ?: 'Đề thi Full Test'),
                'balance_title' => $balanceTitle,
                'balance_desc' => $balanceDesc,
            ];
        }

        // 2. Danh sách các bài Full Test trong khoảng thời gian được chọn
        $query = TestAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->where('session_type', TestAttempt::SESSION_TYPE_FULL_TEST)
            ->whereHas('scoreResult', function ($q): void {
                $q->whereNotNull('total_score');
            })
            ->with(['scoreResult', 'testForm']);

        if ($dateRange['start'] !== null) {
            $query->where('started_at', '>=', $dateRange['start']);
        }
        if ($dateRange['end'] !== null) {
            $query->where('started_at', '<=', $dateRange['end']);
        }

        $attempts = $query->orderBy('started_at', 'asc')
            ->orderBy('attempt_id', 'asc')
            ->get();

        $count = $attempts->count();
        $points = [];
        $totalCoords = [];
        $listeningCoords = [];
        $readingCoords = [];

        foreach ($attempts as $idx => $att) {
            $scoreRes = $att->scoreResult;
            $tot = (int) round((float) ($scoreRes?->total_score ?? 0));
            $lis = (int) round((float) ($scoreRes?->listening_score ?? 0));
            $rea = (int) round((float) ($scoreRes?->reading_score ?? 0));

            // Tọa độ X trên SVG viewBox 760x200
            $x = $count === 1 ? 390 : (int) round(90 + ($idx * (610 / max(1, $count - 1))));

            // Tọa độ Y: Điểm 0 ứng với Y=170, Điểm 990 ứng với Y=20 (chiều cao 150px)
            $yTot = (int) round(170 - (min(990, max(0, $tot)) / 990) * 150);
            $yLis = (int) round(170 - (min(990, max(0, $lis)) / 990) * 150);
            $yRea = (int) round(170 - (min(990, max(0, $rea)) / 990) * 150);

            $dateLabel = $att->started_at ? $att->started_at->format('d/m') : '';
            $fullDate = $att->started_at ? $att->started_at->format('H:i • d/m/Y') : '';

            $points[] = [
                'attempt_id' => $att->attempt_id,
                'x' => $x,
                'date_label' => $dateLabel,
                'full_date' => $fullDate,
                'test_title' => (string) ($att->testForm?->title ?: 'Bài thi Full Test'),
                'total_score' => $tot,
                'y_total' => $yTot,
                'listening_score' => $lis,
                'y_listening' => $yLis,
                'reading_score' => $rea,
                'y_reading' => $yRea,
            ];

            $totalCoords[] = "{$x},{$yTot}";
            $listeningCoords[] = "{$x},{$yLis}";
            $readingCoords[] = "{$x},{$yRea}";
        }

        $totalPath = $count >= 2 ? 'M '.implode(' L ', $totalCoords) : '';
        $listeningPath = $count >= 2 ? 'M '.implode(' L ', $listeningCoords) : '';
        $readingPath = $count >= 2 ? 'M '.implode(' L ', $readingCoords) : '';

        return [
            'has_attempts' => $count > 0,
            'is_single_attempt' => $count === 1,
            'attempts_count' => $count,
            'filter_range' => $range,
            'points' => $points,
            'total_path' => $totalPath,
            'listening_path' => $listeningPath,
            'reading_path' => $readingPath,
            'recent_test' => $recentTest,
        ];
    }

    /**
     * Thống kê từ vựng theo cơ chế StudyPrep đã nghiệm thu.
     *
     * @return array{
     *     total_learned: int,
     *     mastered: int,
     *     forgotten: int,
     *     mastery_rate: int
     * }
     */
    public function getVocabularyStats(int $userId): array
    {
        $learned = UserVocabulary::query()->where('user_id', $userId)->count();
        $mastered = UserVocabulary::query()->where('user_id', $userId)->where('is_done', 1)->count();
        $forgotten = UserVocabulary::query()->where('user_id', $userId)->where('is_done', 0)->count();
        $masteryRate = $learned > 0 ? (int) round(($mastered / $learned) * 100) : 0;

        return [
            'total_learned' => $learned,
            'mastered' => $mastered,
            'forgotten' => $forgotten,
            'mastery_rate' => $masteryRate,
        ];
    }

    /**
     * Lấy 3 hoạt động gần đây nhất từ lịch sử làm bài hợp lệ.
     *
     * @return array<int, array{
     *     type: string,
     *     icon: string,
     *     title: string,
     *     time: string,
     *     score_label: string,
     *     score_val: string,
     *     result_url: string
     * }>
     */
    public function getRecentActivities(int $userId, int $limit = 3): array
    {
        $attempts = TestAttempt::query()
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
            ])
            ->orderBy('started_at', 'desc')
            ->orderBy('attempt_id', 'desc')
            ->limit($limit)
            ->get();

        $activities = [];
        foreach ($attempts as $attempt) {
            $card = $this->historyService->formatAttemptCard($attempt, $userId);

            $isFullTest = in_array($attempt->session_type, [
                TestAttempt::SESSION_TYPE_FULL_TEST,
                TestAttempt::SESSION_TYPE_EXAM,
            ], true);

            $icon = $card['is_exam']
                ? 'test'
                : ($card['part_code'] === 'PART1' ? 'photo' : 'test');

            $scoreLabel = $card['is_exam'] ? 'Tổng điểm' : 'Tỉ lệ đúng';
            $scoreVal = $card['is_exam']
                ? ($attempt->scoreResult?->total_score !== null ? (int) round((float) $attempt->scoreResult->total_score) : 'Đang chấm')
                : ($card['accuracy_percent'] !== null ? "{$card['accuracy_percent']}%" : '0%');

            $timeText = 'Gần đây';
            if ($attempt->started_at) {
                $timeText = $attempt->started_at->locale('vi')->diffForHumans();
            }

            $activities[] = [
                'type' => $isFullTest ? 'fulltest' : 'part',
                'icon' => $icon,
                'title' => $card['title'],
                'time' => $timeText,
                'score_label' => $scoreLabel,
                'score_val' => (string) $scoreVal,
                'result_url' => $card['result_url'],
            ];
        }

        return $activities;
    }

    /**
     * Danh sách 7 Part thể hiện độ thành thạo (Chưa có AI -> hiển thị Chưa đánh giá).
     *
     * @return array<int, array{part: string, status: string, status_type: string, rate: ?int, has_data: bool}>
     */
    public function getMasteryParts(): array
    {
        $parts = [];
        for ($i = 1; $i <= 7; $i++) {
            $parts[] = [
                'part' => "Part {$i}",
                'status' => 'Chưa đánh giá',
                'status_type' => 'neutral',
                'rate' => null,
                'has_data' => false,
            ];
        }

        return $parts;
    }

    /**
     * Top 3 điểm yếu AI (Chưa triển khai AI -> mảng rỗng để View hiển thị Empty State).
     *
     * @return array<empty>
     */
    public function getTopWeaknesses(): array
    {
        return [];
    }
}
