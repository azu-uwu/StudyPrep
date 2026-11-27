<?php

namespace App\Http\Controllers;

use App\Services\HistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImprovementController extends Controller
{
    public function __construct(
        public HistoryService $historyService
    ) {}

    /**
     * Màn hình chính trang Cải thiện (mặc định tab Câu sai).
     */
    public function index(Request $request): View|JsonResponse
    {
        $userId = (int) auth()->id();

        $totalWrongCount = $this->historyService->countAccumulatedWrongQuestions($userId);
        $wrongParts = $this->historyService->getAccumulatedWrongParts($userId);
        $wrongPartsDetails = $this->historyService->getAccumulatedWrongPartsWithQuestions($userId);

        if ($request->wantsJson() || $request->query('format') === 'json') {
            return response()->json([
                'success' => true,
                'total_wrong_count' => $totalWrongCount,
                'parts' => $wrongParts,
                'details' => $wrongPartsDetails,
            ]);
        }

        return view('pages.cai-thien', [
            'totalWrongCount' => $totalWrongCount,
            'wrongParts' => $wrongParts,
            'wrongPartsDetails' => $wrongPartsDetails,
        ]);
    }

    /**
     * API lấy chi tiết danh sách câu sai của một Part cụ thể.
     */
    public function wrongQuestionsByPart(Request $request, string|int $sectionId): JsonResponse
    {
        $userId = (int) auth()->id();
        $wrongPartsDetails = $this->historyService->getAccumulatedWrongPartsWithQuestions($userId);

        $key = (string) $sectionId;
        $data = $wrongPartsDetails[$key] ?? $wrongPartsDetails['section_'.$key] ?? null;

        if (! $data) {
            foreach ($wrongPartsDetails as $item) {
                if ((string) ($item['section_id'] ?? '') === $key || (string) ($item['section_code'] ?? '') === $key) {
                    $data = $item;
                    break;
                }
            }
        }

        if (! $data) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy câu sai thuộc phần này hoặc phần chưa được luyện tập.',
                'questions' => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'part' => [
                'section_id' => $data['section_id'],
                'section_code' => $data['section_code'],
                'title' => $data['display_title'],
                'skill_code' => $data['skill_slug'],
                'wrong_count' => $data['count'],
                'questions' => array_map(function ($q) {
                    return [
                        'id' => $q['id'],
                        'question_number' => $q['sequence_no'],
                        'stem' => $q['content'],
                        'options' => $q['options'],
                        'user_answer' => $q['user_answer'],
                        'correct_answer' => $q['correct_answer'],
                        'explanation' => $q['explanation'],
                        'stimulus_text' => $q['stimulus_text'] ?? null,
                        'image_url' => $q['image_url'] ?? null,
                        'audio_url' => $q['audio_url'] ?? null,
                        'transcript' => $q['transcript'] ?? null,
                    ];
                }, $data['questions']),
            ],
        ]);
    }
}
