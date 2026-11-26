<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\TestAttempt;
use App\Services\PracticeAttemptService;
use App\Services\ReadingQuestionBank;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ReadingPracticeController extends Controller
{
    public function __construct(
        public PracticeAttemptService $practiceAttemptService
    ) {}

    /**
     * Màn hình danh sách luyện tập kỹ năng Đọc (Part 5, 6, 7).
     * Xác định độc lập trạng thái Bắt đầu / Tiếp tục theo từng Part từ MySQL.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->query('skill') === 'nghe') {
            return redirect('/luyen-tap/nghe');
        }
        if ($request->query('skill') === 'noi') {
            return redirect('/luyen-tap/noi');
        }
        if ($request->query('skill') === 'viet') {
            return redirect('/luyen-tap/viet');
        }
        if ($request->query('exit')) {
            session()->forget('reading_practice_session');
        }

        $inProgressAttempts = [
            5 => null,
            6 => null,
            7 => null,
        ];

        if (auth()->check()) {
            $inProgressAttempts = $this->practiceAttemptService->getInProgressAttemptsByPart((int) auth()->id());
        }

        return view('pages.luyen-tap-doc', [
            'inProgressAttempts' => $inProgressAttempts,
        ]);
    }

    /**
     * Khôi phục lượt luyện tập đã lưu trong CSDL (Tiếp tục).
     */
    public function resume(Request $request): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để tiếp tục bài luyện tập.');
        }

        $userId = (int) auth()->id();
        $attemptId = $request->query('attempt_id');
        $part = (int) $request->query('part', 5);

        try {
            if ($attemptId !== null && (int) $attemptId > 0) {
                $resumed = $this->practiceAttemptService->resumeAttempt((int) $attemptId, $userId);
            } else {
                $sectionCode = match ($part) {
                    6 => 'PART6',
                    7 => 'PART7',
                    default => 'PART5',
                };
                $attempt = $this->practiceAttemptService->findInProgressAttempt($userId, 'RC', $sectionCode);
                if (! $attempt) {
                    return redirect()->route('luyen-tap.doc')
                        ->with('practice_error', 'Không tìm thấy bài làm dở cho Part '.$part.'.');
                }
                $resumed = $this->practiceAttemptService->resumeAttempt($attempt->attempt_id, $userId);
            }

            session(['reading_practice_session' => $resumed['sessionData']]);

            return redirect()->route('luyen-tap.doc.hoan-thanh-cau', [
                'part' => $resumed['part'],
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('luyen-tap.doc')
                ->with('practice_error', 'Không thể khôi phục bài làm: '.$e->getMessage());
        }
    }

    /**
     * Lưu bài làm dở và thoát về trang danh sách (Lưu & Thoát).
     */
    public function saveAndExit(Request $request): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để lưu bài luyện tập.',
                'redirect' => route('login'),
            ], 401);
        }

        $sessionData = session('reading_practice_session');
        if (empty($sessionData) || empty($sessionData['question_ids'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiên luyện tập đang hoạt động để lưu.',
            ], 422);
        }

        $incomingAnswers = (array) $request->input('answers', []);

        try {
            $attempt = $this->practiceAttemptService->saveAttempt(
                $sessionData,
                $incomingAnswers,
                (int) auth()->id()
            );

            $sessionData['attempt_id'] = $attempt->attempt_id;
            session(['reading_practice_session' => $sessionData]);

            // Dọn dẹp trạng thái luyện tập tạm thời sau khi đã lưu thành công vào MySQL
            session()->forget('reading_practice_session');

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu bài luyện tập thành công.',
                'attempt_id' => $attempt->attempt_id,
                'redirect' => route('luyen-tap.doc'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu bài luyện tập: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Màn hình làm bài luyện tập theo từng Part (Part 5, Part 6, Part 7) từ MySQL.
     */
    public function practice(Request $request): View|RedirectResponse
    {
        $rawPart = $request->query('part');
        if ($rawPart === null || ! in_array((string) $rawPart, ['5', '6', '7'], true)) {
            return redirect()->to(url('/luyen-tap/doc'))
                ->with('practice_error', 'Phần luyện tập không hợp lệ. Vui lòng chọn Part 5, Part 6 hoặc Part 7.');
        }
        $part = (int) $rawPart;

        $existingSession = session('reading_practice_session');
        $isNewRequest = (bool) $request->query('new');
        $isRetryWrong = (bool) $request->query('retry_wrong');

        $rawCount = $request->query('count');
        $hasCountQuery = ($rawCount !== null && trim((string) $rawCount) !== '');
        $requestedCount = ($hasCountQuery && preg_match('/^\d+$/', trim((string) $rawCount)))
            ? (int) trim((string) $rawCount)
            : null;

        // Khôi phục lượt luyện tập hiện tại khi tải lại trang (reload)
        // Chỉ reload khi cùng Part và không có count khác với phiên đang làm dở
        $countMatches = true;
        if ($requestedCount !== null && ! empty($existingSession)) {
            $sessTarget = $existingSession['target_count'] ?? count($existingSession['question_ids'] ?? []);
            $sessActual = count($existingSession['question_ids'] ?? []);
            $countMatches = ($requestedCount === (int) $sessTarget || $requestedCount === $sessActual);
        }

        $sessionUserId = $existingSession['user_id'] ?? null;
        $userMatches = (auth()->check() ? ($sessionUserId === auth()->id()) : true);

        $isReload = (
            ! $isNewRequest &&
            ! $isRetryWrong &&
            ! empty($existingSession) &&
            ($existingSession['part'] ?? null) === $part &&
            ! empty($existingSession['question_ids']) &&
            $countMatches &&
            $userMatches
        );

        if ($isReload) {
            $questionIds = $existingSession['question_ids'];
            $count = $existingSession['target_count'] ?? count($questionIds);
            $loaded = $this->loadReadingQuestions($part, count($questionIds), $questionIds);
            $selectedQuestions = $loaded['questions'];
            $noticeMessage = $loaded['notice'] ?? null;

            if (empty($selectedQuestions)) {
                session()->forget('reading_practice_session');

                return redirect()->to(url('/luyen-tap/doc?part='.$part))
                    ->with('practice_error', 'Lượt luyện tập không còn tồn tại hoặc dữ liệu câu hỏi đã thay đổi.');
            }

            return view('pages.doc-luyen-tap', [
                'part' => $part,
                'questions' => $selectedQuestions,
                'totalCount' => count($selectedQuestions),
                'targetCount' => $count,
                'noticeMessage' => $noticeMessage,
                'savedAnswers' => $existingSession['answers'] ?? [],
                'savedQuestionStates' => $existingSession['question_states'] ?? [],
                'savedGroupStates' => $existingSession['groups'] ?? [],
                'savedFlags' => $existingSession['flagged_questions'] ?? [],
            ]);
        }

        $specificIds = null;
        // Xử lý làm lại các câu bị sai
        if ($isRetryWrong) {
            $attemptIdForRetry = $request->query('attempt_id');
            if ($attemptIdForRetry && auth()->check()) {
                $specificIds = $this->practiceAttemptService->getRetryQuestionIds((int) $attemptIdForRetry, (int) auth()->id());
            }

            if (empty($specificIds)) {
                $lastResult = session('reading_practice_result');
                if ($lastResult && ! empty($lastResult['details'])) {
                    $wrongIds = collect($lastResult['details'])
                        ->where('status', 'incorrect')
                        ->pluck('id')
                        ->map(fn ($id) => (int) $id)
                        ->values()
                        ->all();

                    if (! empty($wrongIds)) {
                        $specificIds = $wrongIds;
                    }
                }
            }

            if (empty($specificIds)) {
                return redirect()->to(url('/luyen-tap/doc?part='.$part))
                    ->with('practice_error', 'Không tìm thấy câu hỏi sai từ lượt luyện tập trước.');
            }

            $count = count($specificIds);
        } else {
            $rawCount = $request->query('count');

            if ($rawCount === null || trim((string) $rawCount) === '') {
                return redirect()->to(url('/luyen-tap/doc?part='.$part))
                    ->with('practice_error', 'Vui lòng chọn số lượng câu hỏi để bắt đầu luyện tập.');
            }

            $trimmedCount = trim((string) $rawCount);
            if (! preg_match('/^\d+$/', $trimmedCount)) {
                return redirect()->to(url('/luyen-tap/doc?part='.$part.'&count='.$rawCount))
                    ->with('practice_error', 'Số lượng câu hỏi không hợp lệ. Vui lòng nhập một số nguyên dương.');
            }

            $count = (int) $trimmedCount;

            if ($count > 100) {
                return redirect()->to(url('/luyen-tap/doc?part='.$part.'&count='.$count))
                    ->with('practice_error', 'Số lượng câu hỏi không được vượt quá giới hạn 100 câu của hệ thống.');
            }

            // 1. Kiểm tra số lượng câu tối thiểu của từng Part
            if ($part === 5 && $count < 1) {
                return redirect()->to(url('/luyen-tap/doc?part=5&count='.$count))
                    ->with('practice_error', 'Part 5 yêu cầu tối thiểu 1 câu hỏi. Vui lòng chọn lại số lượng câu.');
            }

            if ($part === 6 && $count < 4) {
                return redirect()->to(url('/luyen-tap/doc?part=6&count='.$count))
                    ->with('practice_error', 'Part 6 yêu cầu tối thiểu 4 câu (1 đoạn văn). Vui lòng chọn lại số lượng câu.');
            }

            if ($part === 7 && $count < 2) {
                return redirect()->to(url('/luyen-tap/doc?part=7&count='.$count))
                    ->with('practice_error', 'Part 7 yêu cầu tối thiểu 2 câu để tạo một nhóm bài đọc. Vui lòng chọn lại số lượng câu.');
            }
        }

        $loaded = $this->loadReadingQuestions($part, $count, $specificIds);
        $selectedQuestions = $loaded['questions'];
        $noticeMessage = $loaded['notice'];

        // 2. Nếu không tạo được bài luyện tập hợp lệ (ví dụ ngân hàng không có nhóm phù hợp)
        if (empty($selectedQuestions)) {
            $errorMsg = $noticeMessage ?: 'Hiện tại chưa có đủ dữ liệu câu hỏi hợp lệ để tạo bài luyện tập. Vui lòng thử lại sau.';

            return redirect()->to(url('/luyen-tap/doc?part='.$part.'&count='.$count))
                ->with('practice_error', $errorMsg);
        }

        // Lưu danh sách ID câu hỏi và thông tin nhóm (nếu có) của lượt luyện tập vào session để kiểm tra bảo mật & chấm điểm đúng thứ tự
        $questionIds = collect($selectedQuestions)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $sequenceMap = [];
        foreach ($questionIds as $index => $qid) {
            $sequenceMap[$qid] = $index + 1;
        }

        $groups = [];
        if ($part === 6 || $part === 7) {
            $grouped = collect($selectedQuestions)->groupBy('question_set_id');
            foreach ($grouped as $setId => $qs) {
                $sId = (int) $setId;
                $groups[$sId] = [
                    'question_set_id' => $sId,
                    'question_ids' => $qs->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                    'completed' => false,
                    'answers' => [],
                ];
            }
        }

        session([
            'reading_practice_session' => [
                'user_id' => auth()->id(),
                'part' => $part,
                'target_count' => $count,
                'actual_count' => count($questionIds),
                'started_at' => now()->toIso8601String(),
                'question_ids' => $questionIds,
                'sequence_map' => $sequenceMap,
                'answers' => [],
                'question_states' => [],
                'groups' => $groups,
                'flagged_questions' => [],
            ],
        ]);

        return view('pages.doc-luyen-tap', [
            'part' => $part,
            'questions' => $selectedQuestions,
            'totalCount' => count($selectedQuestions),
            'targetCount' => $count,
            'noticeMessage' => $noticeMessage,
            'savedAnswers' => [],
            'savedQuestionStates' => [],
            'savedGroupStates' => $groups,
            'savedFlags' => [],
        ]);
    }

    /**
     * Truy vấn câu hỏi Reading từ MySQL theo Part (Part 5, Part 6, Part 7).
     * Tuyệt đối không fallback về mock bank khi thiếu câu hỏi.
     *
     * @param  array<int>|null  $specificIds
     * @return array{questions: array<int, array<string, mixed>>, notice: ?string}
     */
    private function loadReadingQuestions(int $part, int $targetCount, ?array $specificIds = null): array
    {
        if (! Schema::hasTable('questions') || ! Schema::hasTable('exam_section')) {
            return [
                'questions' => [],
                'notice' => 'Bảng dữ liệu câu hỏi chưa sẵn sàng trên hệ thống.',
            ];
        }

        return match ($part) {
            6 => $this->loadPart6Questions($targetCount, $specificIds),
            7 => $this->loadPart7Questions($targetCount, $specificIds),
            default => $this->loadPart5Questions($targetCount, $specificIds),
        };
    }

    /**
     * PART 5: Hoàn thành câu (Câu hỏi độc lập).
     *
     * Quy tắc hợp lệ:
     * 1. Status = ACTIVE
     * 2. Thuộc PART5 (exam_section.code = 'PART5')
     * 3. Loại câu hỏi SINGLE_CHOICE (question_type.response_mode = 'SINGLE_CHOICE')
     * 4. Đúng 4 phương án trả lời
     * 5. Bốn nhãn đáp án phải là A, B, C, D (không thiếu, không trùng)
     * 6. Đúng 1 phương án is_correct = 1
     * 7. Nội dung câu hỏi và nội dung các phương án không được rỗng.
     *
     * @param  array<int>|null  $specificIds
     * @return array{questions: array<int, array<string, mixed>>, notice: ?string}
     */
    private function loadPart5Questions(int $targetCount, ?array $specificIds = null): array
    {
        $query = Question::query()
            ->where('status', Question::STATUS_ACTIVE)
            ->whereNotNull('content')
            ->whereRaw("TRIM(content) != ''")
            ->whereHas('section', fn ($s) => $s->where('code', 'PART5')->whereHas('skill', fn ($sk) => $sk->where('code', 'RC')))
            ->whereHas('questionType', fn ($qt) => $qt->where('response_mode', 'SINGLE_CHOICE'))
            ->has('options', '=', 4)
            ->whereHas('options', fn ($opt) => $opt->where('is_correct', true), '=', 1)
            ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'A'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"))
            ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'B'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"))
            ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'C'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"))
            ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'D'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"))
            ->with([
                'options' => fn ($opt) => $opt->select(['option_id', 'question_id', 'label', 'content', 'is_correct']),
                'section.skill',
                'questionType',
            ]);

        if (! empty($specificIds)) {
            $query->whereIn('question_id', $specificIds);
        } else {
            $query->inRandomOrder()->limit($targetCount);
        }

        $questions = $query->get();

        // Lọc qua validator để đảm bảo 100% tính toàn vẹn nghiệp vụ
        $validQuestions = $questions->filter(fn (Question $q) => $this->isValidPart5Question($q))->values();

        // Đảm bảo thứ tự khớp đúng danh sách $specificIds nếu được chỉ định (khôi phục lượt làm dở)
        if (! empty($specificIds)) {
            $byId = $validQuestions->keyBy('question_id');
            $ordered = collect();
            foreach ($specificIds as $sid) {
                if ($byId->has($sid)) {
                    $ordered->push($byId->get($sid));
                }
            }
            $validQuestions = $ordered;
        }

        if ($validQuestions->isEmpty()) {
            return [
                'questions' => [],
                'notice' => 'Hiện tại chưa có câu hỏi Part 5 hợp lệ (ACTIVE) trong hệ thống.',
            ];
        }

        $mapped = [];
        foreach ($validQuestions as $idx => $q) {
            $mapped[] = $this->formatQuestionPayload($q, $idx + 1, 5);
        }

        $notice = null;
        if (empty($specificIds) && count($mapped) < $targetCount) {
            $notice = "Bạn đã chọn {$targetCount} câu. Hiện hệ thống có ".count($mapped).' câu Part 5 hợp lệ để luyện tập.';
        }

        return [
            'questions' => $mapped,
            'notice' => $notice,
        ];
    }

    /**
     * PART 6: Hoàn thành đoạn văn (Mỗi QuestionSet gồm đúng 4 câu).
     * Quy tắc số lượng: Làm tròn XUỐNG về bội số của 4 (floor($targetCount / 4) * 4).
     *
     * @param  array<int>|null  $specificIds
     * @return array{questions: array<int, array<string, mixed>>, notice: ?string}
     */
    private function loadPart6Questions(int $targetCount, ?array $specificIds = null): array
    {
        $targetGroups = intdiv($targetCount, 4);
        $roundedTarget = $targetGroups * 4;

        if ($targetGroups < 1 && empty($specificIds)) {
            return [
                'questions' => [],
                'notice' => 'Part 6 gồm các đoạn văn 4 câu. Vui lòng chọn số lượng tối thiểu từ 4 câu trở lên.',
            ];
        }

        $candidateQuery = QuestionSet::query()
            ->whereNotNull('stimulus_text')
            ->whereRaw("TRIM(stimulus_text) != ''")
            ->whereHas('questions', function ($q) {
                $q->where('status', Question::STATUS_ACTIVE)
                    ->whereNotNull('content')
                    ->whereRaw("TRIM(content) != ''")
                    ->whereHas('section', fn ($s) => $s->where('code', 'PART6')->whereHas('skill', fn ($sk) => $sk->where('code', 'RC')))
                    ->whereHas('questionType', fn ($qt) => $qt->where('response_mode', 'SINGLE_CHOICE'))
                    ->has('options', '=', 4)
                    ->whereHas('options', fn ($opt) => $opt->where('is_correct', true), '=', 1)
                    ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'A'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"))
                    ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'B'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"))
                    ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'C'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"))
                    ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'D'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"));
            }, '=', 4)
            ->whereDoesntHave('questions', function ($q) {
                $q->whereHas('section', fn ($s) => $s->where('code', 'PART6'))
                    ->where(function ($sub) {
                        $sub->where('status', '!=', Question::STATUS_ACTIVE)
                            ->orWhereNull('content')
                            ->orWhereRaw("TRIM(content) = ''");
                    });
            })
            ->with([
                'questions' => function ($q) {
                    $q->where('status', Question::STATUS_ACTIVE)
                        ->whereHas('section', fn ($s) => $s->where('code', 'PART6')->whereHas('skill', fn ($sk) => $sk->where('code', 'RC')))
                        ->with([
                            'options' => fn ($opt) => $opt->select(['option_id', 'question_id', 'label', 'content', 'is_correct']),
                            'section.skill',
                            'questionType',
                        ]);
                },
            ]);

        if (! empty($specificIds)) {
            $candidateQuery->whereHas('questions', fn ($q) => $q->whereIn('question_id', $specificIds));
        }

        $candidates = $candidateQuery->get();

        // Lọc qua validator để đảm bảo 100% nhóm đủ 4 câu hợp lệ
        $validSets = $candidates->filter(function (QuestionSet $set) {
            if (trim((string) $set->stimulus_text) === '') {
                return false;
            }
            if ($set->questions->count() !== 4) {
                return false;
            }

            return $set->questions->every(fn (Question $q) => $this->isValidPart6Question($q));
        })->values();

        if ($validSets->isEmpty()) {
            return [
                'questions' => [],
                'notice' => 'Chưa có đủ nhóm đoạn văn 4 câu hợp lệ cho Part 6 trong hệ thống.',
            ];
        }

        if (! empty($specificIds)) {
            // Sắp xếp các nhóm QuestionSet theo đúng thứ tự xuất hiện của câu hỏi trong $specificIds
            $firstQidPos = [];
            foreach ($specificIds as $pos => $qid) {
                if (! isset($firstQidPos[$qid])) {
                    $firstQidPos[$qid] = $pos;
                }
            }
            $selectedSets = $validSets->sortBy(function (QuestionSet $s) use ($firstQidPos) {
                $minPos = 999999;
                foreach ($s->questions as $q) {
                    if (isset($firstQidPos[$q->question_id])) {
                        $minPos = min($minPos, $firstQidPos[$q->question_id]);
                    }
                }

                return $minPos;
            })->values();
        } else {
            $selectedSets = $validSets->shuffle()->take($targetGroups);
        }

        $mapped = [];
        $globalNum = 1;

        foreach ($selectedSets as $set) {
            $sortedQuestions = $this->sortQuestionsInSet($set->questions);
            if ($sortedQuestions->count() !== 4) {
                continue;
            }

            $groupStart = $globalNum;
            $groupEnd = $globalNum + $sortedQuestions->count() - 1;
            $rangeText = "Câu {$groupStart} - {$groupEnd}";

            foreach ($sortedQuestions as $q) {
                $mapped[] = $this->formatQuestionPayload($q, $globalNum++, 6, $set, $rangeText);
            }
        }

        if (empty($mapped)) {
            return [
                'questions' => [],
                'notice' => 'Không tìm thấy nhóm đoạn văn Part 6 nào có đủ 4 câu hỏi hợp lệ.',
            ];
        }

        $notice = null;
        if (empty($specificIds)) {
            if ($targetCount % 4 !== 0) {
                $notice = "Bạn đã chọn {$targetCount} câu. Do Part 6 gồm các đoạn văn 4 câu, hệ thống đã điều chỉnh xuống {$roundedTarget} câu (gồm {$targetGroups} đoạn văn).";
            }
            if (count($mapped) < $roundedTarget) {
                $actualSets = (int) (count($mapped) / 4);
                $notice = "Bạn đã chọn {$targetCount} câu. Ngân hàng hiện chỉ có ".count($mapped)." câu Part 6 hợp lệ (gồm {$actualSets} đoạn văn).";
            }
        }

        return [
            'questions' => $mapped,
            'notice' => $notice,
        ];
    }

    /**
     * PART 7: Đọc hiểu văn bản (Mỗi QuestionSet gồm 2–5 câu).
     * Quy tắc số lượng: Chọn tổ hợp nhóm sao cho tổng số câu <= $targetCount và lớn nhất có thể.
     *
     * @param  array<int>|null  $specificIds
     * @return array{questions: array<int, array<string, mixed>>, notice: ?string}
     */
    private function loadPart7Questions(int $targetCount, ?array $specificIds = null): array
    {
        $candidateQuery = QuestionSet::query()
            ->where(function ($sub) {
                $sub->whereNotNull('stimulus_text')->whereRaw("TRIM(stimulus_text) != ''")
                    ->orWhere(function ($m) {
                        $m->whereNotNull('media_url')->whereRaw("TRIM(media_url) != ''");
                    });
            })
            ->whereHas('questions', function ($q) {
                $q->where('status', Question::STATUS_ACTIVE)
                    ->whereNotNull('content')
                    ->whereRaw("TRIM(content) != ''")
                    ->whereHas('section', fn ($s) => $s->where('code', 'PART7')->whereHas('skill', fn ($sk) => $sk->where('code', 'RC')))
                    ->whereHas('questionType', fn ($qt) => $qt->where('response_mode', 'SINGLE_CHOICE'))
                    ->has('options', '=', 4)
                    ->whereHas('options', fn ($opt) => $opt->where('is_correct', true), '=', 1)
                    ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'A'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"))
                    ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'B'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"))
                    ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'C'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"))
                    ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'D'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"));
            }, '>=', 2)
            ->whereHas('questions', function ($q) {
                $q->where('status', Question::STATUS_ACTIVE)
                    ->whereHas('section', fn ($s) => $s->where('code', 'PART7')->whereHas('skill', fn ($sk) => $sk->where('code', 'RC')));
            }, '<=', 5)
            ->whereDoesntHave('questions', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('status', '!=', Question::STATUS_ACTIVE)
                        ->orWhereNull('content')
                        ->orWhereRaw("TRIM(content) = ''")
                        ->orWhereDoesntHave('section', fn ($s) => $s->where('code', 'PART7')->whereHas('skill', fn ($sk) => $sk->where('code', 'RC')))
                        ->orWhereDoesntHave('questionType', fn ($qt) => $qt->where('response_mode', 'SINGLE_CHOICE'));
                });
            })
            ->with([
                'questions' => function ($q) {
                    $q->with([
                        'options' => fn ($opt) => $opt->select(['option_id', 'question_id', 'label', 'content', 'is_correct']),
                        'section.skill',
                        'questionType',
                    ]);
                },
            ]);

        if (! empty($specificIds)) {
            $candidateQuery->whereHas('questions', fn ($q) => $q->whereIn('question_id', $specificIds));
        }

        $candidates = $candidateQuery->get();

        // Lọc qua validator để đảm bảo 100% nhóm từ 2 đến 5 câu hợp lệ.
        // Nếu một câu trong nhóm không hợp lệ, loại bỏ toàn bộ nhóm.
        $validSets = $candidates->filter(function (QuestionSet $set) {
            $hasStimulus = trim((string) $set->stimulus_text) !== '' || trim((string) $set->media_url) !== '';
            if (! $hasStimulus) {
                return false;
            }
            $count = $set->questions->count();
            if ($count < 2 || $count > 5) {
                return false;
            }

            return $set->questions->every(fn (Question $q) => $this->isValidPart7Question($q));
        })->values();

        if ($validSets->isEmpty()) {
            return [
                'questions' => [],
                'notice' => 'Chưa có nhóm bài đọc hợp lệ (2–5 câu) cho Part 7 trong hệ thống.',
            ];
        }

        if (! empty($specificIds)) {
            // Sắp xếp các nhóm QuestionSet theo đúng thứ tự xuất hiện của câu hỏi trong $specificIds
            $firstQidPos = [];
            foreach ($specificIds as $pos => $qid) {
                if (! isset($firstQidPos[$qid])) {
                    $firstQidPos[$qid] = $pos;
                }
            }
            $selectedSets = $validSets->sortBy(function (QuestionSet $s) use ($firstQidPos) {
                $minPos = 999999;
                foreach ($s->questions as $q) {
                    if (isset($firstQidPos[$q->question_id])) {
                        $minPos = min($minPos, $firstQidPos[$q->question_id]);
                    }
                }

                return $minPos;
            })->values();
        } else {
            $selectedSets = $this->selectOptimalPart7Sets($validSets, $targetCount);
        }

        if ($selectedSets->isEmpty()) {
            return [
                'questions' => [],
                'notice' => "Không tìm thấy tổ hợp bài đọc nào có tổng số câu ≤ {$targetCount}. Vui lòng chọn số lượng câu hỏi lớn hơn.",
            ];
        }

        $shuffledSelectedSets = empty($specificIds)
            ? $selectedSets->shuffle()->values()
            : $selectedSets->values();

        $mapped = [];
        $globalNum = 1;

        foreach ($shuffledSelectedSets as $set) {
            $sortedQuestions = $this->sortQuestionsInSet($set->questions);
            $qCount = $sortedQuestions->count();
            if ($qCount < 2 || $qCount > 5) {
                continue;
            }

            $groupStart = $globalNum;
            $groupEnd = $globalNum + $qCount - 1;
            $rangeText = "Câu {$groupStart} - {$groupEnd}";

            foreach ($sortedQuestions as $q) {
                $mapped[] = $this->formatQuestionPayload($q, $globalNum++, 7, $set, $rangeText);
            }
        }

        if (empty($mapped)) {
            return [
                'questions' => [],
                'notice' => 'Không tìm thấy nhóm bài đọc Part 7 nào có câu hỏi hợp lệ.',
            ];
        }

        $notice = null;
        if (empty($specificIds) && count($mapped) < $targetCount) {
            $totalAvailable = $validSets->sum(fn (QuestionSet $s) => $s->questions->count());
            if ($totalAvailable < $targetCount) {
                $notice = "Bạn đã chọn {$targetCount} câu. Ngân hàng hiện chỉ có {$totalAvailable} câu Part 7 hợp lệ (gồm ".count($validSets).' bài đọc).';
            } else {
                $notice = "Bạn đã chọn {$targetCount} câu. Do Part 7 giữ nguyên vẹn nhóm bài đọc (2–5 câu), hệ thống đã chọn tổ hợp tối ưu gồm ".count($mapped).' câu.';
            }
        }

        return [
            'questions' => $mapped,
            'notice' => $notice,
        ];
    }

    /**
     * Thuật toán 0-1 Knapsack tìm tổ hợp các nhóm QuestionSet sao cho:
     * - Tổng số câu <= $targetCount
     * - Tổng số câu lớn nhất có thể (ưu tiên bằng đúng $targetCount, nếu không thì gần nhất thấp hơn)
     * - Giữ nguyên vẹn từng nhóm, tuyệt đối không tách câu.
     *
     * @param  Collection<int, QuestionSet>  $candidateSets
     * @return Collection<int, QuestionSet>
     */
    private function selectOptimalPart7Sets(Collection $candidateSets, int $targetCount): Collection
    {
        $items = $candidateSets->shuffle()->values();
        $n = $items->count();
        $capacity = max(0, $targetCount);

        if ($n === 0 || $capacity === 0) {
            return collect();
        }

        $dp = array_fill(0, $n + 1, array_fill(0, $capacity + 1, 0));
        $keep = array_fill(0, $n + 1, array_fill(0, $capacity + 1, false));

        for ($i = 1; $i <= $n; $i++) {
            $w = $items[$i - 1]->questions->count();
            for ($c = 0; $c <= $capacity; $c++) {
                $dp[$i][$c] = $dp[$i - 1][$c];
                if ($w > 0 && $w <= $c && ($dp[$i - 1][$c - $w] + $w) > $dp[$i][$c]) {
                    $dp[$i][$c] = $dp[$i - 1][$c - $w] + $w;
                    $keep[$i][$c] = true;
                }
            }
        }

        $chosen = collect();
        $currCap = $capacity;
        for ($i = $n; $i >= 1; $i--) {
            if ($keep[$i][$currCap]) {
                $chosen->push($items[$i - 1]);
                $w = $items[$i - 1]->questions->count();
                $currCap -= $w;
            }
        }

        return $chosen->reverse()->values();
    }

    /**
     * Chuẩn hóa cấu trúc dữ liệu câu hỏi trả về cho View doc-luyen-tap.blade.php.
     * BẢO MẬT: Tuyệt đối không gửi is_correct, correct hoặc explanation trong bài làm chưa nộp.
     *
     * @return array<string, mixed>
     */
    private function formatQuestionPayload(
        Question $q,
        int $questionNumber,
        int $partNumber,
        ?QuestionSet $qSet = null,
        ?string $questionRange = null
    ): array {
        $optionsMap = [];
        if ($q->relationLoaded('options')) {
            foreach (['A', 'B', 'C', 'D'] as $label) {
                $matchingOpt = $q->options->first(
                    fn ($opt) => strtoupper(trim((string) $opt->label)) === $label
                );
                if ($matchingOpt) {
                    $optionsMap[$label] = (string) $matchingOpt->content;
                }
            }
        }

        $passageHtml = $this->buildPassageHtml($qSet);

        return [
            'id' => (int) $q->question_id,
            'part' => 'Part '.$partNumber,
            'part_number' => $partNumber,
            'question_range' => $questionRange ?? ('Câu '.$questionNumber),
            'question_number' => $questionNumber,
            'question_set_id' => $qSet?->question_set_id,
            'passage_title' => $qSet ? ($qSet->title ?: ('Đoạn văn Part '.$partNumber)) : null,
            'passage' => $passageHtml,
            'stem' => (string) $q->content,
            'options' => $optionsMap,
            // CÁC TRƯỜNG BẢO MẬT: Bắt buộc để null trước khi nộp bài
            'correct' => null,
            'explanation' => null,
            'translation' => null,
        ];
    }

    /**
     * Kiểm tra tính hợp lệ nghiêm ngặt của câu hỏi Part 5:
     * 1. Status = ACTIVE
     * 2. Thuộc PART5
     * 3. Loại câu hỏi SINGLE_CHOICE
     * 4. Đúng 4 phương án trả lời
     * 5. Bốn nhãn đáp án phải là A, B, C, D (không thiếu, không trùng)
     * 6. Đúng 1 phương án is_correct = 1
     * 7. Nội dung câu hỏi và nội dung các phương án không được rỗng.
     */
    private function isValidPart5Question(Question $q): bool
    {
        if ($q->status !== Question::STATUS_ACTIVE) {
            return false;
        }

        if (trim((string) $q->content) === '') {
            return false;
        }

        if ($q->section?->code !== 'PART5') {
            return false;
        }

        if ($q->section?->skill?->code !== 'RC') {
            return false;
        }

        if ($q->questionType?->response_mode !== 'SINGLE_CHOICE') {
            return false;
        }

        if (! $q->relationLoaded('options') || $q->options->count() !== 4) {
            return false;
        }

        $labels = [];
        $correctCount = 0;

        foreach ($q->options as $opt) {
            $lbl = strtoupper(trim((string) $opt->label));
            $labels[] = $lbl;

            if (trim((string) $opt->content) === '') {
                return false;
            }

            if ((bool) $opt->is_correct) {
                $correctCount++;
            }
        }

        sort($labels);
        if ($labels !== ['A', 'B', 'C', 'D']) {
            return false;
        }

        return $correctCount === 1;
    }

    /**
     * Kiểm tra tính hợp lệ nghiêm ngặt của câu hỏi Part 6:
     * 1. Status = ACTIVE
     * 2. Thuộc PART6 và kỹ năng RC
     * 3. Loại câu hỏi SINGLE_CHOICE
     * 4. Thuộc về một question_set_id hợp lệ
     * 5. Đúng 4 phương án trả lời với nhãn A, B, C, D
     * 6. Đúng 1 phương án is_correct = 1
     * 7. Nội dung câu hỏi và nội dung các phương án không được rỗng.
     */
    private function isValidPart6Question(Question $q): bool
    {
        if ($q->status !== Question::STATUS_ACTIVE) {
            return false;
        }

        if (trim((string) $q->content) === '') {
            return false;
        }

        if ($q->section?->code !== 'PART6') {
            return false;
        }

        if ($q->section?->skill?->code !== 'RC') {
            return false;
        }

        if ($q->questionType?->response_mode !== 'SINGLE_CHOICE') {
            return false;
        }

        if (empty($q->question_set_id)) {
            return false;
        }

        if (! $q->relationLoaded('options') || $q->options->count() !== 4) {
            return false;
        }

        $labels = [];
        $correctCount = 0;

        foreach ($q->options as $opt) {
            $lbl = strtoupper(trim((string) $opt->label));
            $labels[] = $lbl;

            if (trim((string) $opt->content) === '') {
                return false;
            }

            if ((bool) $opt->is_correct) {
                $correctCount++;
            }
        }

        sort($labels);
        if ($labels !== ['A', 'B', 'C', 'D']) {
            return false;
        }

        return $correctCount === 1;
    }

    /**
     * Kiểm tra tính hợp lệ nghiêm ngặt của câu hỏi Part 7:
     * 1. Status = ACTIVE
     * 2. Thuộc PART7 và kỹ năng RC
     * 3. Loại câu hỏi SINGLE_CHOICE
     * 4. Thuộc về một question_set_id hợp lệ
     * 5. Đúng 4 phương án trả lời với nhãn A, B, C, D
     * 6. Đúng 1 phương án is_correct = 1
     * 7. Nội dung câu hỏi và nội dung các phương án không được rỗng.
     */
    private function isValidPart7Question(Question $q): bool
    {
        if ($q->status !== Question::STATUS_ACTIVE) {
            return false;
        }

        if (trim((string) $q->content) === '') {
            return false;
        }

        if ($q->section?->code !== 'PART7') {
            return false;
        }

        if ($q->section?->skill?->code !== 'RC') {
            return false;
        }

        if ($q->questionType?->response_mode !== 'SINGLE_CHOICE') {
            return false;
        }

        if (empty($q->question_set_id)) {
            return false;
        }

        if (! $q->relationLoaded('options') || $q->options->count() !== 4) {
            return false;
        }

        $labels = [];
        $correctCount = 0;

        foreach ($q->options as $opt) {
            $lbl = strtoupper(trim((string) $opt->label));
            $labels[] = $lbl;

            if (trim((string) $opt->content) === '') {
                return false;
            }

            if ((bool) $opt->is_correct) {
                $correctCount++;
            }
        }

        sort($labels);
        if ($labels !== ['A', 'B', 'C', 'D']) {
            return false;
        }

        return $correctCount === 1;
    }

    /**
     * Dựng nội dung HTML của bài đọc an toàn:
     * - Giữ nguyên dấu xuống dòng của văn bản thuần hoặc bảo toàn các thẻ HTML an toàn.
     * - Hiển thị hình ảnh nếu có media_url hợp lệ và không phải âm thanh.
     */
    private function buildPassageHtml(?QuestionSet $qSet): ?string
    {
        if ($qSet === null) {
            return null;
        }

        $passageHtml = null;
        $rawPassage = trim((string) ($qSet->stimulus_text ?? ''));

        if ($rawPassage !== '') {
            if (strip_tags($rawPassage) === $rawPassage) {
                $passageHtml = nl2br(e($rawPassage));
            } else {
                $passageHtml = strip_tags($rawPassage, '<p><br><br/><b><strong><i><em><u><span><ul><ol><li><table><thead><tbody><tr><th><td><h1><h2><h3><h4><h5><h6><div><hr><img>');
            }
        }

        $mediaType = strtoupper(trim((string) ($qSet->media_type ?? '')));
        $mediaUrl = (string) ($qSet->media_url ?? '');
        $isAudio = $mediaType === 'AUDIO' || preg_match('/\.(mp3|wav|ogg|m4a|webm)$/i', $mediaUrl) === 1;

        if (! $isAudio && $qSet->resolved_media_url) {
            $imgTag = '<div class="sp-vp-passage-media" style="margin-bottom: 16px;"><img src="'
                .e($qSet->resolved_media_url)
                .'" alt="Reading material" style="max-width: 100%; height: auto; border-radius: 8px;"></div>';
            $passageHtml = $imgTag.($passageHtml ?? '');
        }

        return $passageHtml;
    }

    /**
     * Sắp xếp các câu hỏi trong cùng một nhóm QuestionSet.
     * Ưu tiên tìm số thứ tự chỗ trống trong nội dung câu hỏi (VD: [131], 131., Câu 131),
     * nếu không có thì sắp xếp theo question_id tăng dần.
     *
     * @param  Collection<int, Question>|EloquentCollection<int, Question>  $questions
     * @return Collection<int, Question>
     */
    private function sortQuestionsInSet(Collection|EloquentCollection $questions): Collection
    {
        return $questions->sort(function (Question $a, Question $b) {
            $numA = $this->extractQuestionNumberFromStem($a->content);
            $numB = $this->extractQuestionNumberFromStem($b->content);

            if ($numA !== null && $numB !== null && $numA !== $numB) {
                return $numA <=> $numB;
            }

            return $a->question_id <=> $b->question_id;
        })->values();
    }

    private function extractQuestionNumberFromStem(string $content): ?int
    {
        if (preg_match('/(?:\[|\(|\b|---)\s*(?:câu\s*)?(\d{2,3})\s*(?:\]|\)|\b|---|\.)/ui', $content, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * Kiểm tra câu trả lời hoặc mở khóa giải thích tức thời cho chế độ Luyện tập Part 5.
     * BẢO MẬT: Kiểm tra tính hợp lệ của câu hỏi thuộc phiên làm việc hiện tại và đối chiếu với CSDL.
     */
    public function checkAnswer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question_id' => ['required', 'integer'],
            'selected_option' => ['nullable', 'string', 'in:A,B,C,D,a,b,c,d'],
            'action' => ['nullable', 'string', 'in:answer,reveal'],
            'version' => ['nullable', 'integer'],
        ]);

        $questionId = (int) $validated['question_id'];
        $action = $validated['action'] ?? (! empty($validated['selected_option']) ? 'answer' : 'reveal');

        $question = Question::query()
            ->where('question_id', $questionId)
            ->with([
                'options' => fn ($opt) => $opt->select(['option_id', 'question_id', 'label', 'content', 'is_correct']),
                'section',
            ])
            ->first();

        if (! $question) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy câu hỏi trong hệ thống.',
            ], 404);
        }

        // BẢO MẬT: Tuyệt đối không cho phép gửi câu hỏi Part 6 hoặc Part 7 riêng lẻ đến endpoint Part 5 để xem trước đáp án
        $sectionCode = $question->section?->code;
        if ($sectionCode === 'PART6' || $sectionCode === 'PART7') {
            $pNum = $sectionCode === 'PART6' ? 6 : 7;

            return response()->json([
                'success' => false,
                'message' => 'Câu hỏi Part '.$pNum.' phải được kiểm tra theo nhóm đoạn văn.',
            ], 403);
        }

        // Backend kiểm tra phiên làm việc và câu hỏi thuộc đúng lượt luyện tập của người dùng hiện tại
        $sessionData = session('reading_practice_session');
        if (empty($sessionData)) {
            return response()->json([
                'success' => false,
                'message' => 'Lượt luyện tập không tồn tại hoặc đã hết hạn.',
            ], 403);
        }

        if (auth()->check() && isset($sessionData['user_id']) && $sessionData['user_id'] !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Lượt luyện tập không thuộc về tài khoản hiện tại.',
            ], 403);
        }

        $sessionPart = $sessionData['part'] ?? null;
        if ($sessionPart === 6 || $sessionPart === 7) {
            return response()->json([
                'success' => false,
                'message' => 'Câu hỏi Part '.$sessionPart.' phải được kiểm tra theo nhóm đoạn văn.',
            ], 403);
        }

        $allowedIds = $sessionData['question_ids'] ?? [];

        if (! empty($allowedIds) && ! in_array($questionId, $allowedIds, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Câu hỏi không thuộc lượt luyện tập hiện tại.',
            ], 403);
        }

        // Request Versioning ở Backend: nếu có version, kiểm tra không để request cũ ghi đè
        $incomingVersion = $request->has('version') ? (int) $request->input('version') : null;
        if ($incomingVersion !== null) {
            $prevVersion = (int) ($sessionData['question_versions'][$questionId] ?? 0);
            if ($incomingVersion < $prevVersion) {
                return response()->json([
                    'success' => true,
                    'stale' => true,
                    'question_id' => $questionId,
                    'message' => 'Bỏ qua request cũ do độ trễ mạng.',
                ]);
            }
            $sessionData['question_versions'][$questionId] = $incomingVersion;
        }

        if ($question->section?->code === 'PART6' || $question->section?->code === 'PART7') {
            $partLabel = $question->section?->code === 'PART7' ? 'Part 7' : 'Part 6';

            return response()->json([
                'success' => false,
                'message' => 'Câu hỏi '.$partLabel.' phải được kiểm tra theo nhóm đoạn văn.',
            ], 403);
        }

        $correctOpt = $question->options->firstWhere('is_correct', true);
        $correctLabel = $correctOpt ? strtoupper(trim((string) $correctOpt->label)) : null;

        $rawExplanation = trim((string) $question->explanation);
        $explanation = $rawExplanation !== '' ? $rawExplanation : 'Chưa có giải thích cho câu hỏi này.';

        // Trường hợp người học bấm nút Giải thích trước khi chọn đáp án
        if ($action === 'reveal' || empty($validated['selected_option'])) {
            if ($sessionData && empty($sessionData['answers'][$questionId])) {
                $sessionData['question_states'][$questionId] = [
                    'answered' => false,
                    'userPick' => null,
                    'correctPick' => $correctLabel,
                    'isCorrect' => false,
                    'revealed' => true,
                    'explanation' => $explanation,
                    'explainOpen' => true,
                ];
                session(['reading_practice_session' => $sessionData]);
            }

            return response()->json([
                'success' => true,
                'question_id' => $questionId,
                'action' => 'reveal',
                'correct_answer' => $correctLabel,
                'explanation' => $explanation,
            ]);
        }

        $selectedOption = strtoupper(trim((string) $validated['selected_option']));
        $isCorrect = ($selectedOption === $correctLabel);

        if ($sessionData) {
            $sessionData['answers'][$questionId] = $selectedOption;
            $sessionData['question_states'][$questionId] = [
                'answered' => true,
                'userPick' => $selectedOption,
                'correctPick' => $correctLabel,
                'isCorrect' => $isCorrect,
                'revealed' => false,
                'explanation' => $explanation,
                'explainOpen' => true,
            ];
            session(['reading_practice_session' => $sessionData]);
        }

        return response()->json([
            'success' => true,
            'question_id' => $questionId,
            'action' => 'answer',
            'user_answer' => $selectedOption,
            'correct_answer' => $correctLabel,
            'is_correct' => $isCorrect,
            'is_right' => $isCorrect,
            'explanation' => $explanation,
        ]);
    }

    /**
     * Kiểm tra đáp án theo nhóm đoạn văn (Dùng chung cho Part 6 và tái sử dụng cho Part 7).
     * BẢO MẬT:
     * - Chỉ nhận request khi có đủ câu trả lời của toàn bộ nhóm khi nộp kết quả.
     * - Hỗ trợ lưu tiến độ dở dang (action = 'save_progress').
     * - Đối chiếu trực tiếp với CSDL (answer_option.is_correct).
     * - Cập nhật kết quả khi thay đổi đáp án theo nghiệp vụ luyện tập.
     */
    public function checkGroupAnswers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question_set_id' => ['required', 'integer'],
            'action' => ['nullable', 'string'],
            'answers' => ['nullable', 'array'],
            'question_id' => ['nullable', 'integer'],
            'version' => ['nullable', 'integer'],
        ]);

        $setId = (int) $validated['question_set_id'];
        $action = $validated['action'] ?? 'answer';
        $sessionData = session('reading_practice_session');

        if (! $sessionData || ! isset($sessionData['groups'])) {
            return response()->json([
                'success' => false,
                'message' => 'Lượt luyện tập không tồn tại hoặc đã hết hạn.',
            ], 403);
        }

        if (auth()->check() && isset($sessionData['user_id']) && $sessionData['user_id'] !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Lượt luyện tập không thuộc về tài khoản hiện tại.',
            ], 403);
        }

        if (! isset($sessionData['groups'][$setId])) {
            return response()->json([
                'success' => false,
                'message' => 'Nhóm đoạn văn không thuộc lượt luyện tập hiện tại.',
            ], 403);
        }

        $groupInfo = $sessionData['groups'][$setId];
        $expectedQids = $groupInfo['question_ids'] ?? [];

        // Hỗ trợ lưu tiến độ từng câu đang làm dở trong nhóm
        if ($action === 'save_progress') {
            $incomingQid = $request->has('question_id') ? (int) $request->input('question_id') : null;
            $incomingVersion = $request->has('version') ? (int) $request->input('version') : null;

            if ($incomingQid !== null && $incomingVersion !== null) {
                $prevVersion = (int) ($sessionData['question_versions'][$incomingQid] ?? 0);
                if ($incomingVersion < $prevVersion) {
                    return response()->json([
                        'success' => true,
                        'stale' => true,
                        'question_id' => $incomingQid,
                        'saved' => false,
                        'message' => 'Bỏ qua cập nhật tiến độ câu cũ do đến trễ.',
                    ]);
                }
                $sessionData['question_versions'][$incomingQid] = $incomingVersion;
            }
            $userAnswers = (array) ($validated['answers'] ?? []);
            $normalizedAnswers = [];
            foreach ($userAnswers as $qid => $val) {
                $qInt = (int) $qid;
                if (in_array($qInt, $expectedQids, true)) {
                    $lbl = strtoupper(trim((string) $val));
                    if (in_array($lbl, ['A', 'B', 'C', 'D'], true)) {
                        $normalizedAnswers[$qInt] = $lbl;
                        $sessionData['answers'][$qInt] = $lbl;
                    }
                }
            }
            $sessionData['groups'][$setId]['answers'] = array_merge(
                $sessionData['groups'][$setId]['answers'] ?? [],
                $normalizedAnswers
            );
            session(['reading_practice_session' => $sessionData]);

            return response()->json([
                'success' => true,
                'saved' => true,
                'question_set_id' => $setId,
                'answers' => $normalizedAnswers,
            ]);
        }

        // Hỗ trợ xem trước giải thích trong chế độ luyện tập (action === 'reveal')
        if ($action === 'reveal') {
            $dbQuestions = Question::query()
                ->whereIn('question_id', $expectedQids)
                ->with(['options' => fn ($opt) => $opt->select(['option_id', 'question_id', 'label', 'content', 'is_correct'])])
                ->get()
                ->keyBy('question_id');

            $results = [];
            foreach ($expectedQids as $qid) {
                $q = $dbQuestions->get($qid);
                if (! $q) {
                    continue;
                }
                $correctOpt = $q->options->firstWhere('is_correct', true);
                $correctLabel = $correctOpt ? strtoupper(trim((string) $correctOpt->label)) : null;
                $rawExp = trim((string) $q->explanation);
                $explanation = $rawExp !== '' ? $rawExp : 'Chưa có giải thích cho câu hỏi này.';

                $results[] = [
                    'question_id' => $qid,
                    'user_answer' => null,
                    'correct_answer' => $correctLabel,
                    'is_correct' => false,
                    'is_right' => false,
                    'explanation' => $explanation,
                ];
            }

            $sessionData['groups'][$setId]['revealed'] = true;
            $sessionData['groups'][$setId]['explainOpen'] = true;
            session(['reading_practice_session' => $sessionData]);

            return response()->json([
                'success' => true,
                'action' => 'reveal',
                'question_set_id' => $setId,
                'total_questions' => count($expectedQids),
                'results' => $results,
            ]);
        }

        // Kiểm tra xem đã nhận đủ đáp án hợp lệ cho TẤT CẢ câu hỏi trong nhóm chưa
        $userAnswers = $validated['answers'] ?? [];
        $normalizedAnswers = [];
        foreach ($userAnswers as $qid => $val) {
            $normalizedAnswers[(int) $qid] = strtoupper(trim((string) $val));
        }

        foreach ($expectedQids as $expectedId) {
            if (! isset($normalizedAnswers[$expectedId]) || ! in_array($normalizedAnswers[$expectedId], ['A', 'B', 'C', 'D'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn cần trả lời đầy đủ tất cả các câu hỏi trong đoạn văn trước khi xem kết quả.',
                ], 422);
            }
        }

        // Nếu nhóm đã hoàn thành và gửi lại đúng tập đáp án cũ thì trả về kết quả đã lưu
        if (! empty($groupInfo['completed']) && ! empty($groupInfo['result'])) {
            $prevAnswers = $groupInfo['answers'] ?? [];
            $hasNewAnswers = false;
            foreach ($normalizedAnswers as $qid => $ans) {
                if (($prevAnswers[$qid] ?? null) !== $ans) {
                    $hasNewAnswers = true;
                    break;
                }
            }
            if (! $hasNewAnswers && count($prevAnswers) === count($normalizedAnswers)) {
                return response()->json([
                    'success' => true,
                    'already_completed' => true,
                    'question_set_id' => $setId,
                    'correct_count' => $groupInfo['result']['correct_count'],
                    'total_questions' => count($expectedQids),
                    'results' => $groupInfo['result']['results'],
                ]);
            }
        }

        $dbQuestions = Question::query()
            ->whereIn('question_id', $expectedQids)
            ->with(['options' => fn ($opt) => $opt->select(['option_id', 'question_id', 'label', 'content', 'is_correct'])])
            ->get()
            ->keyBy('question_id');

        $results = [];
        $correctCount = 0;

        foreach ($expectedQids as $qid) {
            $q = $dbQuestions->get($qid);
            if (! $q) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu câu hỏi không hợp lệ trên hệ thống.',
                ], 404);
            }

            $correctOpt = $q->options->firstWhere('is_correct', true);
            $correctLabel = $correctOpt ? strtoupper(trim((string) $correctOpt->label)) : null;

            $userPick = $normalizedAnswers[$qid];
            $isCorrect = ($userPick === $correctLabel);
            if ($isCorrect) {
                $correctCount++;
            }

            $rawExp = trim((string) $q->explanation);
            $explanation = $rawExp !== '' ? $rawExp : 'Chưa có giải thích cho câu hỏi này.';

            $results[] = [
                'question_id' => $qid,
                'user_answer' => $userPick,
                'correct_answer' => $correctLabel,
                'is_correct' => $isCorrect,
                'is_right' => $isCorrect,
                'explanation' => $explanation,
            ];

            // Cập nhật câu trả lời và trạng thái câu vào session
            $sessionData['answers'][$qid] = $userPick;
            $sessionData['question_states'][$qid] = [
                'answered' => true,
                'userPick' => $userPick,
                'correctPick' => $correctLabel,
                'isCorrect' => $isCorrect,
                'revealed' => false,
                'explanation' => $explanation,
                'explainOpen' => true,
            ];
        }

        // Ghi nhận trạng thái hoàn thành và đáp án đã chốt vào Session
        $sessionData['groups'][$setId]['completed'] = true;
        $sessionData['groups'][$setId]['answers'] = $normalizedAnswers;
        $sessionData['groups'][$setId]['result'] = [
            'correct_count' => $correctCount,
            'results' => $results,
        ];
        session(['reading_practice_session' => $sessionData]);

        return response()->json([
            'success' => true,
            'question_set_id' => $setId,
            'correct_count' => $correctCount,
            'total_questions' => count($expectedQids),
            'results' => $results,
        ]);
    }

    /**
     * Xử lý nộp bài thi luyện tập Đọc
     */
    public function submit(Request $request): RedirectResponse
    {
        $part = (int) $request->input('part', 5);
        if (! in_array($part, [5, 6, 7], true)) {
            $part = 5;
        }

        $submittedAnswers = (array) $request->input('answers', []);
        $requestedCount = (int) $request->input('total_count', 20);

        // Lấy danh sách ID câu hỏi đã phân phối trong phiên luyện tập (giữ đúng thứ tự và danh sách câu hỏi)
        $sessionData = session('reading_practice_session');
        $sessionIds = $sessionData['question_ids'] ?? [];

        $requestIds = [];
        if ($request->filled('question_ids')) {
            $requestIds = array_values(array_filter(
                array_map('intval', explode(',', (string) $request->input('question_ids'))),
                fn ($id) => $id > 0
            ));
        }

        $orderedIds = ! empty($sessionIds) ? $sessionIds : $requestIds;

        // Nếu người dùng đã đăng nhập, lưu và chấm điểm thông qua PracticeAttemptService (BE-014)
        if (auth()->check() && ! empty($orderedIds)) {
            $sessionData = session('reading_practice_session') ?? [
                'part' => $part,
                'question_ids' => $orderedIds,
                'answers' => $submittedAnswers,
                'target_count' => count($orderedIds),
                'actual_count' => count($orderedIds),
            ];

            try {
                $submitRes = $this->practiceAttemptService->submitAttempt(
                    $sessionData,
                    $submittedAnswers,
                    (int) auth()->id()
                );
                $attempt = $submitRes['attempt'];
                $resultData = $submitRes['result'];

                session(['reading_practice_result' => $resultData]);
                session()->forget('reading_practice_session');

                return redirect()->route('luyen-tap.doc.ket-qua', ['attempt_id' => $attempt->attempt_id]);
            } catch (\Throwable $e) {
                return redirect()->back()
                    ->with('practice_error', 'Lỗi khi nộp bài: '.$e->getMessage());
            }
        }

        // Chấm điểm Part 5, 6, 7 (hoặc khi có danh sách câu hỏi từ MySQL) cho khách vãng lai
        if ($part === 5 || $part === 6 || $part === 7 || ! empty($orderedIds)) {
            $dbQuestions = Question::query()
                ->whereIn('question_id', $orderedIds)
                ->with([
                    'options' => fn ($opt) => $opt->select(['option_id', 'question_id', 'label', 'content', 'is_correct']),
                    'questionSet',
                ])
                ->get()
                ->keyBy('question_id');

            $orderedQuestions = collect($orderedIds)
                ->map(fn ($id) => $dbQuestions->get($id))
                ->filter()
                ->values();

            if ($orderedQuestions->isNotEmpty()) {
                $correctCount = 0;
                $incorrectCount = 0;
                $unansweredCount = 0;
                $details = [];

                foreach ($orderedQuestions as $idx => $q) {
                    $qid = (int) $q->question_id;

                    // Đối với câu hỏi thuộc nhóm đã hoàn thành trong Session, ưu tiên đáp án đã ghi nhận trong session
                    // Nếu không có trong group session, lấy từ $sessionData['answers'][$qid] hoặc $submittedAnswers[$qid]
                    $groupAnswer = null;
                    if (! empty($sessionData['groups'])) {
                        foreach ($sessionData['groups'] as $grp) {
                            if (! empty($grp['completed']) && isset($grp['answers'][$qid])) {
                                $groupAnswer = $grp['answers'][$qid];
                                break;
                            }
                        }
                    }

                    $sessionAnswer = $sessionData['answers'][$qid] ?? null;
                    $submittedPick = isset($submittedAnswers[$qid]) && trim((string) $submittedAnswers[$qid]) !== ''
                        ? strtoupper(trim((string) $submittedAnswers[$qid]))
                        : null;

                    $userPick = $groupAnswer ?? ($sessionAnswer ?? ($submittedPick ?? null));
                    if ($userPick !== null && ! in_array($userPick, ['A', 'B', 'C', 'D'], true)) {
                        $userPick = null;
                    }

                    $correctOpt = $q->options->firstWhere('is_correct', true);
                    $correctLabel = $correctOpt ? strtoupper(trim((string) $correctOpt->label)) : null;

                    if ($userPick === null) {
                        $status = 'unanswered';
                        $unansweredCount++;
                    } elseif ($userPick === $correctLabel) {
                        $status = 'correct';
                        $correctCount++;
                    } else {
                        $status = 'incorrect';
                        $incorrectCount++;
                    }

                    $optionsMap = [];
                    foreach (['A', 'B', 'C', 'D'] as $label) {
                        $opt = $q->options->first(fn ($o) => strtoupper(trim((string) $o->label)) === $label);
                        if ($opt) {
                            $optionsMap[$label] = (string) $opt->content;
                        }
                    }

                    $rawExp = trim((string) $q->explanation);
                    $explanation = $rawExp !== '' ? $rawExp : 'Chưa có giải thích cho câu hỏi này.';

                    $passageHtml = $this->buildPassageHtml($q->questionSet);
                    $passageTitle = $q->questionSet !== null
                        ? ($q->questionSet->title ?: ('Đoạn văn Part '.$part))
                        : null;

                    $details[] = [
                        'id' => $qid,
                        'part' => 'Part '.$part,
                        'part_number' => $part,
                        'question_number' => $idx + 1,
                        'stem' => (string) $q->content,
                        'passage_title' => $passageTitle,
                        'passage' => $passageHtml,
                        'options' => $optionsMap,
                        'user_answer' => $userPick,
                        'correct_answer' => $correctLabel,
                        'status' => $status,
                        'explanation' => $explanation,
                        'translation' => null,
                    ];
                }

                $total = $orderedQuestions->count();
                $scorePercent = $total > 0 ? (int) round(($correctCount / $total) * 100) : 0;

                $previousPercent = session('reading_practice_result.score_percent');
                $previousPercentText = $previousPercent !== null ? ($previousPercent.'%') : '40%';

                $partNames = [
                    5 => 'Part 5 — Hoàn thành câu',
                    6 => 'Part 6 — Hoàn thành đoạn văn',
                    7 => 'Part 7 — Đọc hiểu văn bản',
                ];

                $resultData = [
                    'part' => $part,
                    'part_name' => $partNames[$part] ?? ('Part '.$part.' — Đọc'),
                    'total' => $total,
                    'correct_count' => $correctCount,
                    'incorrect_count' => $incorrectCount,
                    'unanswered_count' => $unansweredCount,
                    'score_percent' => $scorePercent,
                    'previous_percent' => $previousPercentText,
                    'submitted_at' => now()->format('H:i • d/m/Y'),
                    'details' => $details,
                ];

                session(['reading_practice_result' => $resultData]);
                session()->forget('reading_practice_session');

                return redirect()->route('luyen-tap.doc.ket-qua');
            }
        }

        // Fallback tương thích cho Part 6 / 7
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

        $resultData = [
            'part' => $part,
            'part_name' => 'Part '.$part.' — Đọc',
            'total' => $total,
            'correct_count' => $correctCount,
            'incorrect_count' => $incorrectCount,
            'unanswered_count' => $unansweredCount,
            'score_percent' => $scorePercent,
            'previous_percent' => '40%',
            'submitted_at' => now()->format('H:i • d/m/Y'),
            'details' => $details,
        ];

        session(['reading_practice_result' => $resultData]);
        session()->forget('reading_practice_session');

        return redirect()->route('luyen-tap.doc.ket-qua');
    }

    /**
     * Màn hình kết quả luyện tập Đọc
     */
    public function result(Request $request): View|RedirectResponse
    {
        $attemptId = $request->query('attempt_id');
        $result = null;

        if ($attemptId !== null && (int) $attemptId > 0) {
            $attemptIdInt = (int) $attemptId;
            $userId = auth()->id();
            if (! $userId) {
                return redirect()->route('login')
                    ->with('error', 'Vui lòng đăng nhập để xem kết quả bài luyện tập.');
            }

            $attempt = TestAttempt::query()
                ->where('attempt_id', $attemptIdInt)
                ->first();

            if (! $attempt) {
                return redirect()->route('luyen-tap.doc')
                    ->with('practice_error', 'Không tìm thấy lượt luyện tập yêu cầu.');
            }

            if ((int) $attempt->user_id !== (int) $userId) {
                return redirect()->route('luyen-tap.doc')
                    ->with('practice_error', 'Bạn không có quyền truy cập kết quả bài luyện tập của người khác.');
            }

            if ($attempt->status !== TestAttempt::STATUS_COMPLETED) {
                return redirect()->route('luyen-tap.doc')
                    ->with('practice_error', 'Lượt luyện tập này chưa hoàn thành.');
            }

            $result = $this->practiceAttemptService->getAttemptResult($attemptIdInt, (int) $userId);
        }

        if (! $result) {
            $result = session('reading_practice_result');
        }

        if (! $result) {
            $result = [
                'part' => 5,
                'part_name' => 'Part 5 — Đọc',
                'total' => 0,
                'correct_count' => 0,
                'incorrect_count' => 0,
                'unanswered_count' => 0,
                'score_percent' => 0,
                'previous_percent' => '0%',
                'submitted_at' => now()->format('H:i • d/m/Y'),
                'details' => [],
            ];
        }

        return view('pages.doc-ket-qua', [
            'result' => $result,
        ]);
    }
}
