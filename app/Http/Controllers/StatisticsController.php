<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function __construct(
        public StatisticsService $statisticsService
    ) {}

    /**
     * Màn hình chính trang Phân tích kết quả học tập.
     */
    public function index(Request $request): View|JsonResponse
    {
        $userId = (int) auth()->id();
        $period = (string) $request->query('period', '90');

        if (! in_array($period, ['7', '30', '90', 'all'], true)) {
            $period = '90';
        }

        $analyticsStats = $this->statisticsService->getOverviewStats($userId);
        $partAccuracyList = $this->statisticsService->getPartAccuracyList($userId);
        $fullTestData = $this->statisticsService->getFullTestData($userId, $period);
        $masteryParts = $this->statisticsService->getMasteryParts();
        $topWeaknesses = $this->statisticsService->getTopWeaknesses();
        $vocabStats = $this->statisticsService->getVocabularyStats($userId);
        $recentActivities = $this->statisticsService->getRecentActivities($userId, 3);

        if ($request->wantsJson() || $request->query('format') === 'json') {
            return response()->json([
                'success' => true,
                'period' => $period,
                'fullTestData' => $fullTestData,
                'analyticsStats' => $analyticsStats,
                'partAccuracyList' => $partAccuracyList,
                'vocabStats' => $vocabStats,
                'recentActivities' => $recentActivities,
            ]);
        }

        return view('pages.phan-tich', [
            'period' => $period,
            'analyticsStats' => $analyticsStats,
            'partAccuracyList' => $partAccuracyList,
            'fullTestData' => $fullTestData,
            'masteryParts' => $masteryParts,
            'topWeaknesses' => $topWeaknesses,
            'vocabStats' => $vocabStats,
            'recentActivities' => $recentActivities,
        ]);
    }

    /**
     * API tải dữ liệu biểu đồ Luyện đề theo bộ lọc thời gian.
     */
    public function chartData(Request $request): JsonResponse
    {
        $userId = (int) auth()->id();
        $period = (string) $request->query('period', '90');

        if (! in_array($period, ['7', '30', '90', 'all'], true)) {
            $period = '90';
        }

        $fullTestData = $this->statisticsService->getFullTestData($userId, $period);

        return response()->json([
            'success' => true,
            'period' => $period,
            'data' => $fullTestData,
        ]);
    }
}
