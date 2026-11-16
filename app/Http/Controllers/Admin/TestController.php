<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExamSampleDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestController extends Controller
{
    public function __construct(
        protected ExamSampleDataService $examService
    ) {}

    /**
     * Hiển thị danh sách đề thi Full Test của Admin.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $type = strtoupper((string) $request->input('type', 'ALL'));
        $status = strtoupper((string) $request->input('status', 'ALL'));

        $tests = $this->examService->getAllTests($search, $type, $status);
        $allTests = $this->examService->getAllTests();

        // Tính toán các chỉ số thống kê KPI
        $stats = [
            'total' => count($allTests),
            'lr_count' => count(array_filter($allTests, fn ($t) => $t['exam_type'] === 'TOEIC_LR')),
            'sw_count' => count(array_filter($allTests, fn ($t) => $t['exam_type'] === 'TOEIC_SW')),
            'completed' => count(array_filter($allTests, fn ($t) => $t['status'] === 'COMPLETED')),
        ];

        return view('admin.tests.index', [
            'tests' => $tests,
            'stats' => $stats,
            'blueprints' => ExamSampleDataService::BLUEPRINTS,
            'filters' => [
                'search' => $search,
                'type' => $type,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Hiển thị chi tiết cấu trúc kỹ năng, Part và danh sách câu hỏi của một đề thi.
     */
    public function show(int|string $id): View
    {
        $test = $this->examService->findTest($id);

        if (! $test) {
            abort(404, 'Không tìm thấy đề thi yêu cầu.');
        }

        return view('admin.tests.show', [
            'test' => $test,
        ]);
    }

    /**
     * Mô phỏng tạo đề thi mới ở mức giao diện và điều hướng đến trang chi tiết.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|min:3|max:150',
            'exam_type' => 'required|in:TOEIC_LR,TOEIC_SW',
        ], [
            'title.required' => 'Vui lòng nhập tên đề thi.',
            'title.min' => 'Tên đề thi phải có ít nhất :min ký tự.',
            'title.max' => 'Tên đề thi không vượt quá :max ký tự.',
            'exam_type.required' => 'Vui lòng chọn loại đề thi TOEIC.',
            'exam_type.in' => 'Loại đề thi không hợp lệ.',
        ]);

        $newTest = $this->examService->createMockTest($validated);

        return redirect()->route('admin.tests.show', $newTest['id'])
            ->with('success', "Đề thi mô phỏng \"{$newTest['title']}\" đã được tạo thành công với 0 câu hỏi ban đầu. Bạn có thể bắt đầu thêm câu hỏi vào các Part bên dưới.");
    }
}
