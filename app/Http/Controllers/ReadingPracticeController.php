<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionSet;
use App\Services\ReadingQuestionBank;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ReadingPracticeController extends Controller
{
    /**
     * Màn hình làm bài luyện tập theo từng Part (Part 5, Part 6, Part 7) từ MySQL.
     */
    public function practice(Request $request): View
    {
        $part = (int) $request->query('part', 5);
        if (! in_array($part, [5, 6, 7], true)) {
            $part = 5;
        }

        $count = (int) $request->query('count', 20);
        if ($count <= 0) {
            $count = 20;
        }

        $specificIds = null;
        // Xử lý làm lại các câu bị sai
        if ($request->query('retry_wrong')) {
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

        $loaded = $this->loadReadingQuestions($part, $count, $specificIds);
        $selectedQuestions = $loaded['questions'];
        $noticeMessage = $loaded['notice'];

        return view('pages.doc-luyen-tap', [
            'part' => $part,
            'questions' => $selectedQuestions,
            'totalCount' => count($selectedQuestions),
            'targetCount' => $count,
            'noticeMessage' => $noticeMessage,
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
            ->whereHas('section', fn ($s) => $s->where('code', 'PART5'))
            ->whereHas('questionType', fn ($qt) => $qt->where('response_mode', 'SINGLE_CHOICE'))
            ->has('options', '=', 4)
            ->whereHas('options', fn ($opt) => $opt->where('is_correct', true), '=', 1)
            ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'A'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"))
            ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'B'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"))
            ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'C'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"))
            ->whereHas('options', fn ($opt) => $opt->whereRaw("UPPER(TRIM(label)) = 'D'")->whereNotNull('content')->whereRaw("TRIM(content) != ''"))
            ->with([
                'options' => fn ($opt) => $opt->select(['option_id', 'question_id', 'label', 'content', 'is_correct']),
                'section',
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

        $setQuery = QuestionSet::query()
            ->whereNotNull('stimulus_text')
            ->where('stimulus_text', '!=', '')
            ->whereHas('questions', function ($q) {
                $q->where('status', Question::STATUS_ACTIVE)
                    ->whereHas('section', fn ($s) => $s->where('code', 'PART6'));
            }, '=', 4);

        if (! empty($specificIds)) {
            $setQuery->whereHas('questions', fn ($q) => $q->whereIn('question_id', $specificIds));
        } else {
            $setQuery->inRandomOrder()->limit($targetGroups);
        }

        $sets = $setQuery->get();

        if ($sets->isEmpty()) {
            return [
                'questions' => [],
                'notice' => 'Chưa có đủ nhóm đoạn văn 4 câu hợp lệ cho Part 6 trong hệ thống.',
            ];
        }

        $sets->load([
            'questions' => function ($q) {
                $q->where('status', Question::STATUS_ACTIVE)
                    ->whereHas('section', fn ($s) => $s->where('code', 'PART6'))
                    ->with(['options' => fn ($opt) => $opt->select(['option_id', 'question_id', 'label', 'content'])]);
            },
        ]);

        $mapped = [];
        $globalNum = 1;

        foreach ($sets as $set) {
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
                $notice = 'Ngân hàng hiện chỉ có '.count($mapped)." câu Part 6 hợp lệ (gồm {$actualSets} đoạn văn).";
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
            ->whereNotNull('stimulus_text')
            ->where('stimulus_text', '!=', '')
            ->whereHas('questions', function ($q) {
                $q->where('status', Question::STATUS_ACTIVE)
                    ->whereHas('section', fn ($s) => $s->where('code', 'PART7'));
            }, '>=', 2)
            ->whereHas('questions', function ($q) {
                $q->where('status', Question::STATUS_ACTIVE)
                    ->whereHas('section', fn ($s) => $s->where('code', 'PART7'));
            }, '<=', 5)
            ->withCount([
                'questions as active_part7_count' => function ($q) {
                    $q->where('status', Question::STATUS_ACTIVE)
                        ->whereHas('section', fn ($s) => $s->where('code', 'PART7'));
                },
            ]);

        if (! empty($specificIds)) {
            $candidateQuery->whereHas('questions', fn ($q) => $q->whereIn('question_id', $specificIds));
        }

        $candidates = $candidateQuery->get();

        if ($candidates->isEmpty()) {
            return [
                'questions' => [],
                'notice' => 'Chưa có nhóm bài đọc hợp lệ (2–5 câu) cho Part 7 trong hệ thống.',
            ];
        }

        $selectedSets = empty($specificIds)
            ? $this->selectOptimalPart7Sets($candidates, $targetCount)
            : $candidates;

        if ($selectedSets->isEmpty()) {
            return [
                'questions' => [],
                'notice' => "Không tìm thấy tổ hợp bài đọc nào có tổng số câu ≤ {$targetCount}. Vui lòng chọn số lượng câu hỏi lớn hơn.",
            ];
        }

        $selectedSetIds = $selectedSets->pluck('question_set_id')->all();
        $setsWithQuestions = QuestionSet::query()
            ->whereIn('question_set_id', $selectedSetIds)
            ->with([
                'questions' => function ($q) {
                    $q->where('status', Question::STATUS_ACTIVE)
                        ->whereHas('section', fn ($s) => $s->where('code', 'PART7'))
                        ->with(['options' => fn ($opt) => $opt->select(['option_id', 'question_id', 'label', 'content'])]);
                },
            ])
            ->get();

        $mapped = [];
        $globalNum = 1;

        foreach ($setsWithQuestions as $set) {
            $sortedQuestions = $this->sortQuestionsInSet($set->questions);
            if ($sortedQuestions->count() < 2 || $sortedQuestions->count() > 5) {
                continue;
            }

            $groupStart = $globalNum;
            $groupEnd = $globalNum + $sortedQuestions->count() - 1;
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
            $notice = "Bạn đã chọn {$targetCount} câu. Hệ thống đã chọn tổ hợp bài đọc nguyên vẹn tối ưu nhất gồm ".count($mapped).' câu.';
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
            $w = (int) ($items[$i - 1]->active_part7_count ?? $items[$i - 1]->questions_count ?? 0);
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
                $w = (int) ($items[$i - 1]->active_part7_count ?? $items[$i - 1]->questions_count ?? 0);
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
            if ($partNumber === 5) {
                // Với Part 5: Sắp xếp cố định A -> B -> C -> D, không dựa vào thứ tự MySQL
                foreach (['A', 'B', 'C', 'D'] as $label) {
                    $matchingOpt = $q->options->first(
                        fn ($opt) => strtoupper(trim((string) $opt->label)) === $label
                    );
                    if ($matchingOpt) {
                        $optionsMap[$label] = (string) $matchingOpt->content;
                    }
                }
            } else {
                foreach ($q->options->sortBy('label') as $opt) {
                    $lbl = strtoupper(trim((string) $opt->label));
                    $optionsMap[$lbl] = (string) $opt->content;
                }
            }
        }

        $passageHtml = null;
        if ($qSet !== null) {
            $passageHtml = (string) ($qSet->stimulus_text ?? '');
            if ($qSet->resolved_media_url) {
                $imgTag = '<div class="sp-vp-passage-media" style="margin-bottom: 16px;"><img src="'
                    .e($qSet->resolved_media_url)
                    .'" alt="Reading material" style="max-width: 100%; height: auto; border-radius: 8px;"></div>';
                $passageHtml = $imgTag.$passageHtml;
            }
        }

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
     * Xử lý nộp bài thi luyện tập Đọc
     */
    public function submit(Request $request): RedirectResponse
    {
        $part = (int) $request->input('part', 5);
        if (! in_array($part, [5, 6, 7])) {
            $part = 5;
        }

        $submittedAnswers = $request->input('answers', []);
        $timeSpentSeconds = (int) $request->input('time_spent', 165);
        $requestedCount = (int) $request->input('total_count', 20);

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

        $minutes = floor($timeSpentSeconds / 60);
        $seconds = $timeSpentSeconds % 60;
        $formattedTime = sprintf('%02d:%02d', $minutes, $seconds);
        $timeSpentText = max(1, (int) round($timeSpentSeconds / 60)).' phút';

        $resultData = [
            'part' => $part,
            'total' => $total,
            'correct_count' => $correctCount,
            'incorrect_count' => $incorrectCount,
            'unanswered_count' => $unansweredCount,
            'score_percent' => $scorePercent,
            'time_spent_seconds' => $timeSpentSeconds,
            'time_spent_formatted' => $formattedTime,
            'time_spent_text' => $timeSpentText,
            'previous_percent' => '40%',
            'submitted_at' => now()->format('H:i • d/m/Y'),
            'details' => $details,
        ];

        session(['reading_practice_result' => $resultData]);

        return redirect()->route('luyen-tap.doc.ket-qua');
    }

    /**
     * Màn hình kết quả luyện tập Đọc
     */
    public function result(Request $request): View
    {
        $result = session('reading_practice_result');

        // Nếu người dùng vào thẳng không qua submit, tạo dữ liệu thực tế từ ngân hàng câu hỏi
        if (! $result) {
            $bank = array_slice(ReadingQuestionBank::getQuestions(5), 0, 10);
            $correctCount = 8;
            $incorrectCount = 2;
            $unansweredCount = 0;
            $details = [];

            foreach ($bank as $index => $q) {
                if ($index < 8) {
                    $status = 'correct';
                    $userPick = $q['correct'];
                } else {
                    $status = 'incorrect';
                    $wrongKeys = array_diff(array_keys($q['options']), [$q['correct']]);
                    $userPick = reset($wrongKeys);
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

            $result = [
                'part' => 5,
                'total' => 10,
                'correct_count' => $correctCount,
                'incorrect_count' => $incorrectCount,
                'unanswered_count' => $unansweredCount,
                'score_percent' => 80,
                'time_spent_seconds' => 332,
                'time_spent_formatted' => '05:32',
                'time_spent_text' => '5 phút',
                'previous_percent' => '40%',
                'submitted_at' => now()->format('H:i • d/m/Y'),
                'details' => $details,
            ];
        }

        return view('pages.doc-ket-qua', [
            'result' => $result,
        ]);
    }
}
