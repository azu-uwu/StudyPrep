<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnswerOption;
use App\Models\ExamSection;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\TestForm;
use App\Services\ExamSampleDataService;
use App\Services\TestFormService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TestController extends Controller
{
    public function __construct(
        protected TestFormService $testFormService
    ) {}

    /**
     * Hiển thị danh sách đề thi TOEIC (lọc, tìm kiếm, phân trang) từ CSDL.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', $request->query('keyword', '')));
        $type = strtoupper((string) $request->query('type', 'ALL'));
        $status = strtoupper((string) $request->query('status', 'ALL'));
        $perPage = (int) $request->query('per_page', TestFormService::PER_PAGE);
        if ($perPage < 5 || $perPage > 100) {
            $perPage = TestFormService::PER_PAGE;
        }

        $highlightTestId = session('created_test_id');

        return view('admin.tests.index', [
            'tests' => $this->testFormService->paginate(
                $search,
                $type,
                $status,
                $highlightTestId !== null ? (int) $highlightTestId : null,
                $perPage,
            ),
            'stats' => $this->testFormService->stats(),
            'blueprints' => ExamSampleDataService::BLUEPRINTS,
            'filters' => [
                'search' => $search,
                'type' => $type,
                'status' => $status,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Hiển thị chi tiết cấu trúc kỹ năng, Part và danh sách câu hỏi của một đề thi.
     */
    public function show(int|string $id): View
    {
        $testForm = TestForm::query()->findOrFail($id);
        $testDetail = $this->testFormService->detail($testForm);
        $allowedPartCodes = $testDetail['allowed_part_codes'] ?? [];

        $existingQuestionIds = Schema::hasTable('test_form_question')
            ? DB::table('test_form_question')->where('test_form_id', $testForm->test_form_id)->pluck('question_id')->all()
            : [];

        $sections = ExamSection::with('skill')
            ->when(! empty($allowedPartCodes), fn ($q) => $q->whereIn('code', $allowedPartCodes))
            ->orderBy('sequence_no')
            ->get();

        $allowedSectionIds = $sections->pluck('section_id')->all();

        $bankQuestions = Question::query()
            ->with(['section.skill', 'options', 'questionSet'])
            ->when(! empty($allowedSectionIds), fn ($q) => $q->whereIn('section_id', $allowedSectionIds))
            ->when(! empty($existingQuestionIds), fn ($q) => $q->whereNotIn('question_id', $existingQuestionIds))
            ->orderByDesc('question_id')
            ->limit(150)
            ->get();

        $lastSavedAt = session(
            'test_saved_at_'.$testForm->test_form_id,
            $testForm->created_at ? $testForm->created_at->format('H:i') : now()->format('H:i')
        );

        return view('admin.tests.show', [
            'test' => $testDetail,
            'bankQuestions' => $bankQuestions,
            'sections' => $sections,
            'lastSavedAt' => $lastSavedAt,
        ]);
    }

    /**
     * Tạo đề thi mới với 0 câu hỏi và điều hướng đến trang chi tiết để bổ sung câu hỏi.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:200', Rule::unique('test_form', 'title')],
            'exam_type' => ['required', Rule::in(TestForm::examTypes())],
        ], [
            'title.required' => 'Vui lòng nhập tên đề thi.',
            'title.min' => 'Tên đề thi phải có ít nhất :min ký tự.',
            'title.max' => 'Tên đề thi không vượt quá :max ký tự.',
            'title.unique' => 'Tên đề thi đã tồn tại, vui lòng đặt tên khác.',
            'exam_type.required' => 'Vui lòng chọn loại đề thi TOEIC.',
            'exam_type.in' => 'Loại đề thi không hợp lệ.',
        ]);

        $testForm = $this->testFormService->create($validated);
        session()->put('test_saved_at_'.$testForm->test_form_id, now()->format('H:i'));

        return redirect()->route('admin.tests.show', $testForm->test_form_id)
            ->with('created_test_id', $testForm->test_form_id)
            ->with('success', "Đề thi \"{$testForm->title}\" ({$testForm->test_code}) đã được tạo thành công với 0 câu hỏi ban đầu. Bạn có thể bắt đầu thêm câu hỏi vào các Part bên dưới.");
    }

    /**
     * Cập nhật thông tin đề thi (Tên đề thi, Loại đề thi).
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $testForm = TestForm::query()->findOrFail($id);

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'min:3',
                'max:200',
                Rule::unique('test_form', 'title')->ignore($testForm->test_form_id, 'test_form_id'),
            ],
            'exam_type' => ['required', Rule::in(TestForm::examTypes())],
        ], [
            'title.required' => 'Vui lòng nhập tên đề thi.',
            'title.unique' => 'Tên đề thi đã tồn tại, vui lòng đặt tên khác.',
            'exam_type.required' => 'Vui lòng chọn loại đề thi TOEIC.',
        ]);

        $result = $this->testFormService->update($testForm, $validated);
        $updated = $result['test_form'];
        $detachedCount = $result['detached_count'];
        session()->put('test_saved_at_'.$updated->test_form_id, now()->format('H:i'));

        $message = "Đã cập nhật thông tin đề thi \"{$updated->title}\" ({$updated->test_code}) thành công!";
        if ($detachedCount > 0) {
            $message .= " Hệ thống đã tự động gỡ {$detachedCount} câu hỏi không thuộc kỹ năng/Part của loại đề mới.";
        }

        return redirect()->route('admin.tests.index')
            ->with('success', $message);
    }

    /**
     * Xóa đề thi khỏi hệ thống (chặn xóa nếu đã có lịch sử làm bài của học viên).
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $testForm = TestForm::query()->findOrFail($id);
        $code = $testForm->test_code;
        $title = $testForm->title;

        $result = $this->testFormService->delete($testForm);

        if (! $result['deleted']) {
            return redirect()->route('admin.tests.index')
                ->with('error', "Không thể xóa đề thi \"{$title}\" ({$code}) vì đã có {$result['attempts_count']} lượt làm bài của học viên. Vui lòng giữ lại để bảo toàn lịch sử thi.");
        }

        return redirect()->route('admin.tests.index')
            ->with('success', "Đã xóa đề thi \"{$title}\" ({$code}) khỏi hệ thống.");
    }

    /**
     * Lưu trạng thái biên soạn đề thi hoặc Duyệt & Xuất bản toàn bộ câu hỏi trong đề.
     */
    public function saveState(Request $request, int|string $id): RedirectResponse
    {
        $testForm = TestForm::query()->findOrFail($id);
        $actionType = $request->input('action_type', 'save');
        $partName = $request->input('part_name');

        if ($actionType === 'approve' && Schema::hasTable('test_form_question')) {
            $questionIds = DB::table('test_form_question')
                ->where('test_form_id', $testForm->test_form_id)
                ->pluck('question_id')
                ->all();

            if (! empty($questionIds)) {
                Question::query()->whereIn('question_id', $questionIds)->update(['status' => 'ACTIVE']);
            }
        }

        $savedTime = now()->format('H:i');
        session()->put('test_saved_at_'.$testForm->test_form_id, $savedTime);

        if ($actionType === 'approve') {
            $msg = "Đã lưu và duyệt xuất bản chính thức đề thi \"{$testForm->title}\" ({$testForm->test_code}) lúc {$savedTime}!";
        } elseif ($partName) {
            $msg = "Đã lưu dữ liệu phần thi \"{$partName}\" của đề {$testForm->test_code} lúc {$savedTime}.";
        } else {
            $msg = "Đã lưu trạng thái đề thi \"{$testForm->title}\" ({$testForm->test_code}) thành công lúc {$savedTime}.";
        }

        return redirect()->route('admin.tests.show', $testForm->test_form_id)
            ->with('success', $msg);
    }

    /**
     * Gắn trực tiếp các câu hỏi được chọn từ Ngân hàng câu hỏi vào Đề thi hiện tại.
     */
    public function attachQuestions(Request $request, int|string $id): RedirectResponse
    {
        $testForm = TestForm::query()->findOrFail($id);
        $questionIds = $request->input('question_ids', []);

        if (empty($questionIds) || ! is_array($questionIds)) {
            return redirect()->route('admin.tests.show', $testForm->test_form_id)
                ->with('error', 'Vui lòng chọn ít nhất một câu hỏi từ Ngân hàng câu hỏi để gắn vào đề.');
        }

        $result = $this->testFormService->attachQuestions($testForm, $questionIds);
        session()->put('test_saved_at_'.$testForm->test_form_id, now()->format('H:i'));

        $notes = [];
        if ($result['skipped'] > 0) {
            $notes[] = "bỏ qua {$result['skipped']} câu trùng lặp";
        }
        if ($result['rejected_skill'] > 0) {
            $notes[] = "chặn {$result['rejected_skill']} câu không đúng kỹ năng/Part của đề";
        }
        if ($result['rejected_limit'] > 0) {
            $notes[] = "chặn {$result['rejected_limit']} câu do vượt chỉ tiêu tối đa của Part/đề";
        }

        if ($result['attached'] === 0 && ! empty($notes)) {
            return redirect()->route('admin.tests.show', $testForm->test_form_id)
                ->with('error', 'Không thể gắn câu hỏi nào vào đề thi: '.implode(', ', $notes).'.');
        }

        $msg = "Đã gắn thêm {$result['attached']} câu hỏi vào đề thi \"{$testForm->title}\" ({$testForm->test_code}).";
        if (! empty($notes)) {
            $msg .= ' ('.ucfirst(implode('; ', $notes)).').';
        }

        return redirect()->route('admin.tests.show', $testForm->test_form_id)
            ->with('success', $msg);
    }

    /**
     * Chỉnh sửa trực tiếp nội dung câu hỏi, phương án A/B/C/D và transcript ngay trong màn hình soạn đề thi.
     */
    public function updateQuestion(Request $request, int|string $id, int|string $questionId): RedirectResponse
    {
        $testForm = TestForm::query()->findOrFail($id);
        $question = Question::query()->findOrFail($questionId);

        $validated = $request->validate([
            'content' => 'required|string|min:3',
            'difficulty' => 'required|in:EASY,MEDIUM,HARD',
            'explanation' => 'nullable|string',
            'transcript' => 'nullable|string',
            'opt_a' => 'nullable|string',
            'opt_b' => 'nullable|string',
            'opt_c' => 'nullable|string',
            'opt_d' => 'nullable|string',
            'correct_option' => 'nullable|in:A,B,C,D',
        ]);

        DB::transaction(function () use ($question, $validated, $request): void {
            $question->content = $validated['content'];
            $question->difficulty = $validated['difficulty'];
            $question->explanation = $validated['explanation'] ?? $question->explanation;
            $question->save();

            // Cập nhật transcript / ngữ cảnh âm thanh nếu có
            if ($request->filled('transcript')) {
                if ($question->question_set_id) {
                    QuestionSet::query()->where('question_set_id', $question->question_set_id)->update([
                        'transcript' => $validated['transcript'],
                    ]);
                } else {
                    $set = QuestionSet::query()->create([
                        'title' => 'Audio Transcript #Q-'.$question->question_id,
                        'transcript' => $validated['transcript'],
                        'media_type' => 'AUDIO',
                    ]);
                    $question->question_set_id = $set->question_set_id;
                    $question->save();
                }
            }

            $optionsInput = [
                'A' => $request->input('opt_a'),
                'B' => $request->input('opt_b'),
                'C' => $request->input('opt_c'),
                'D' => $request->input('opt_d'),
            ];

            $hasOptionUpdates = collect($optionsInput)->filter(fn ($v) => $v !== null && trim((string) $v) !== '')->isNotEmpty();
            if ($hasOptionUpdates) {
                $correctOpt = $validated['correct_option'] ?? 'A';
                $question->options()->delete();
                foreach ($optionsInput as $label => $text) {
                    if ($text !== null && trim((string) $text) !== '') {
                        AnswerOption::query()->create([
                            'question_id' => $question->question_id,
                            'label' => $label,
                            'content' => trim((string) $text),
                            'is_correct' => $label === $correctOpt,
                        ]);
                    }
                }
            }
        });

        session()->put('test_saved_at_'.$testForm->test_form_id, now()->format('H:i'));

        return redirect()->route('admin.tests.show', $testForm->test_form_id)
            ->with('success', "Đã cập nhật nội dung câu hỏi #Q-{$question->question_id} trong đề {$testForm->test_code}!");
    }

    /**
     * Gỡ một câu hỏi khỏi đề thi (không xóa câu hỏi khỏi Ngân hàng chung).
     */
    public function detachQuestion(int|string $id, int|string $questionId): RedirectResponse
    {
        $testForm = TestForm::query()->findOrFail($id);

        if (Schema::hasTable('test_form_question')) {
            DB::table('test_form_question')
                ->where('test_form_id', $testForm->test_form_id)
                ->where('question_id', (int) $questionId)
                ->delete();
        }

        session()->put('test_saved_at_'.$testForm->test_form_id, now()->format('H:i'));

        return redirect()->route('admin.tests.show', $testForm->test_form_id)
            ->with('success', "Đã xóa câu hỏi #Q-{$questionId} khỏi đề thi {$testForm->test_code}.");
    }
}
