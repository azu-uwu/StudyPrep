<?php

namespace App\Http\Controllers;

use App\Models\TestAttempt;
use App\Services\HistoryService;
use App\Services\PracticeAttemptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function __construct(
        public HistoryService $historyService,
        public PracticeAttemptService $practiceAttemptService
    ) {}

    /**
     * Màn hình danh sách lịch sử làm bài.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để xem lịch sử làm bài.');
        }

        $userId = (int) auth()->id();

        $allowedRanges = ['today', '7days', '30days', '90days', 'all'];
        $range = (string) $request->query('range', '30days');
        if (! in_array($range, $allowedRanges, true)) {
            $range = '30days';
        }

        $allowedTypes = ['all', 'lesson', 'exam'];
        $type = (string) $request->query('type', 'all');
        if (! in_array($type, $allowedTypes, true)) {
            $type = 'all';
        }

        $part = (string) $request->query('part', 'all');

        $allowedSorts = ['newest', 'oldest', 'score_desc', 'score_asc'];
        $sort = (string) $request->query('sort', 'newest');
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'newest';
        }

        $rawSearch = $request->query('q');
        $search = $rawSearch !== null ? trim((string) $rawSearch) : null;

        $stats = $this->historyService->getStatistics($userId, $range);
        $typeCounts = $this->historyService->getTypeCounts($userId, $range, $part, $search);
        $partList = $this->historyService->getAvailableParts();

        $paginator = $this->historyService->getPaginatedHistory($userId, [
            'range' => $range,
            'type' => $type,
            'part' => $part,
            'sort' => $sort,
            'q' => $search,
        ], 10);

        // Gom nhóm theo ngày
        $groupedHistory = [];
        foreach ($paginator->items() as $item) {
            $groupKey = $item['date_group_key'];
            if (! isset($groupedHistory[$groupKey])) {
                $groupedHistory[$groupKey] = [
                    'label' => $item['date_group_label'],
                    'items' => [],
                ];
            }
            $groupedHistory[$groupKey]['items'][] = $item;
        }

        // Kiểm tra xem người dùng đã từng có lịch sử nào trong hệ thống chưa
        $hasAnyHistory = TestAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->exists();

        $dateRangeInfo = $this->historyService->parseDateRange($range);

        return view('pages.lich-su', [
            'stats' => $stats,
            'typeCounts' => $typeCounts,
            'partList' => $partList,
            'paginator' => $paginator,
            'groupedHistory' => $groupedHistory,
            'range' => $range,
            'rangeLabel' => $dateRangeInfo['label'],
            'type' => $type,
            'selectedPart' => $part,
            'sort' => $sort,
            'search' => $search ?? '',
            'hasAnyHistory' => $hasAnyHistory,
            'totalCount' => $paginator->total(),
            'displayedCount' => $paginator->count(),
        ]);
    }

    /**
     * Mở màn hình xem lại kết quả bài làm tương ứng.
     */
    public function show(int $attemptId): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để xem lại kết quả bài làm.');
        }

        $userId = (int) auth()->id();

        $attempt = TestAttempt::query()
            ->where('attempt_id', $attemptId)
            ->with(['attemptResponses.question.section.skill'])
            ->first();

        if (! $attempt) {
            abort(404, 'Không tìm thấy lượt làm bài.');
        }

        if ((int) $attempt->user_id !== $userId) {
            abort(403, 'Bạn không có quyền truy cập lượt làm bài của người khác.');
        }

        if (! in_array($attempt->status, [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'], true)) {
            return redirect()->route('lich-su')
                ->with('practice_error', 'Lượt làm bài này chưa hoàn thành.');
        }

        if (in_array($attempt->session_type, [TestAttempt::SESSION_TYPE_FULL_TEST, TestAttempt::SESSION_TYPE_PRACTICE_PART, TestAttempt::SESSION_TYPE_EXAM], true)) {
            return redirect()->route('thi-thu.ket-qua', ['attemptId' => $attemptId]);
        }

        $firstQuestion = $attempt->attemptResponses->first()?->question;
        $skillCode = $firstQuestion?->section?->skill?->code ?? 'RC';

        if ($skillCode === 'LC') {
            return redirect()->route('luyen-tap.nghe.ket-qua', ['attempt_id' => $attemptId, 'from' => 'history']);
        }

        // Mặc định Reading
        return redirect()->route('luyen-tap.doc.ket-qua', ['attempt_id' => $attemptId, 'from' => 'history']);
    }

    /**
     * Ôn lại toàn bộ câu hỏi của phiên làm bài đã chọn.
     */
    public function retry(int $attemptId): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để ôn lại bài làm.');
        }

        $userId = (int) auth()->id();

        $attempt = TestAttempt::query()
            ->where('attempt_id', $attemptId)
            ->with(['attemptResponses.question.section.skill'])
            ->first();

        if (! $attempt) {
            abort(404, 'Không tìm thấy lượt làm bài để ôn lại.');
        }

        if ((int) $attempt->user_id !== $userId) {
            abort(403, 'Bạn không có quyền ôn lại bài làm của người khác.');
        }

        if (! in_array($attempt->status, [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'], true)) {
            return redirect()->route('lich-su')
                ->with('practice_error', 'Lượt làm bài này chưa hoàn thành.');
        }

        if (in_array($attempt->session_type, [TestAttempt::SESSION_TYPE_FULL_TEST, TestAttempt::SESSION_TYPE_PRACTICE_PART, TestAttempt::SESSION_TYPE_EXAM], true)) {
            return redirect()->route('thi-thu.gioi-thieu-de', [
                'id' => $attempt->test_form_id,
            ]);
        }

        $firstQuestion = $attempt->attemptResponses->first()?->question;
        $sectionCode = $firstQuestion?->section?->code ?? 'PART5';
        $skillCode = $firstQuestion?->section?->skill?->code ?? 'RC';

        if ($skillCode === 'RC' || in_array($sectionCode, ['PART5', 'PART6', 'PART7'], true)) {
            $part = match ($sectionCode) {
                'PART6' => 6,
                'PART7' => 7,
                default => 5,
            };

            return redirect()->route('luyen-tap.doc.hoan-thanh-cau', [
                'part' => $part,
                'retry_attempt_id' => $attemptId,
            ]);
        }

        if ($skillCode === 'LC' || in_array($sectionCode, ['PART1', 'PART2', 'PART3', 'PART4'], true)) {
            return redirect()->route('luyen-tap.nghe.lam-bai');
        }

        return redirect()->route('lich-su')
            ->with('practice_error', 'Chức năng ôn lại cho phần này chưa được hỗ trợ.');
    }
}
