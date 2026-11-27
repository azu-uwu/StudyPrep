<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\ScoreResult;
use App\Models\TestAttempt;
use App\Models\TestForm;
use App\Models\User;
use App\Services\ExamSampleDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MockTestController extends Controller
{
    /**
     * Bản đồ thời lượng ước tính chuẩn cho từng Part (phút).
     */
    public const PART_MINUTES = [
        'PART1' => 3,
        'PART2' => 10,
        'PART3' => 17,
        'PART4' => 15,
        'PART5' => 20,
        'PART6' => 15,
        'PART7' => 40,
        'SPK_T1' => 3,
        'SPK_T2' => 3,
        'SPK_T3' => 5,
        'SPK_T4' => 5,
        'SPK_T5' => 4,
        'WRITING_T1' => 15,
        'WRITING_T2' => 20,
        'WRITING_T3' => 25,
    ];

    /**
     * Hướng dẫn chính thức chuẩn ETS cho từng Part TOEIC.
     */
    public const PART_DIRECTIONS = [
        'PART1' => [
            'title' => 'Part 1: PHOTOGRAPHS',
            'text' => 'Directions: For each question, you will listen to four short statements about a picture in your test book. These statements will not be printed and will only be spoken one time. Select the statement that best describes what is happening in the picture and mark the corresponding letter (A), (B), (C), or (D) on the answer sheet. The statement that best describes the picture is (B), "The man is sitting at the desk." So, you should mark letter (B) on the answer sheet.',
        ],
        'PART2' => [
            'title' => 'PART 2: QUESTION & RESPONSE',
            'text' => 'Directions: For each question, you will listen to a statement or question followed by three possible responses spoken in English. They will not be printed and will only be spoken one time. Select the best response and mark the corresponding letter (A), (B), or (C) on your answer sheet.',
        ],
        'PART3' => [
            'title' => 'PART 3: CONVERSATIONS',
            'text' => 'Directions: In this part, you will listen to several conversations between two or more speakers. These conversations will not be printed and will only be spoken one time. For each conversation, you will be asked to answer three questions. Select the best response and mark the corresponding letter (A), (B), (C), or (D) on your answer sheet.',
        ],
        'PART4' => [
            'title' => 'PART 4: SHORT TALKS',
            'text' => 'Directions: In this part, you will listen to several short talks by a single speaker. These talks will not be printed and will only be spoken one time. For each talk, you will be asked to answer three questions. Select the best response and mark the corresponding letter (A), (B), (C), or (D) on your answer sheet.',
        ],
        'PART5' => [
            'title' => 'PART 5: INCOMPLETE SENTENCES',
            'text' => 'Directions: A word or phrase is missing in each of the sentences below. Four answer choices are given below each sentence. Select the best answer to complete the sentence. Then mark the letter (A), (B), (C), or (D) on your answer sheet.',
        ],
        'PART6' => [
            'title' => 'PART 6: TEXT COMPLETION',
            'text' => 'Directions: Read the texts that follow. A word, phrase, or sentence is missing in parts of each text. Four answer choices for each question are given below the text. Select the best answer to complete the text. Then mark the letter (A), (B), (C), or (D) on your answer sheet.',
        ],
        'PART7' => [
            'title' => 'PART 7: READING COMPREHENSION',
            'text' => 'Directions: In this part you will read a selection of texts, such as magazine and newspaper articles, letters, and advertisements. Each text is followed by several questions. Select the best answer for each question and mark the letter (A), (B), (C), or (D) on your answer sheet.',
        ],
        'SPK_T1' => [
            'title' => 'Part 1: READ A TEXT ALOUD',
            'text' => 'Directions: In this part, you will read aloud the text on the screen. You will have 45 seconds to prepare. Then you will have 45 seconds to read the text aloud.',
        ],
        'WRITING_T1' => [
            'title' => 'Part 1: WRITE A SENTENCE BASED ON A PICTURE',
            'text' => 'Directions: In this part, you will write one sentence that is based on a picture. With each picture you will be given two words or phrases that you must use in your sentence.',
        ],
    ];

    /**
     * BƯỚC 1: Danh sách đề thi thật từ CSDL
     */
    public function index(): View
    {
        $query = TestForm::query()
            ->with([
                'blueprint',
                'questions' => fn ($q) => $q->select('questions.question_id', 'questions.section_id', 'questions.status')
                    ->with('section:section_id,code'),
            ])
            ->whereHas('blueprint', fn ($q) => $q->where('status', 'ACTIVE'));

        if (Schema::hasColumn('test_form', 'status')) {
            $query->where(function ($q) {
                $q->whereIn('status', ['published', 'active', 'COMPLETED', ''])
                    ->orWhereNull('status');
            });
        }

        $allTests = $query->orderByDesc('test_form_id')->get();

        $placementTests = [];
        $lrTests = [];
        $swTests = [];

        foreach ($allTests as $test) {
            $examType = (string) ($test->blueprint?->exam_type ?? TestForm::EXAM_TYPE_LR);
            $definition = ExamSampleDataService::BLUEPRINTS[$examType] ?? ExamSampleDataService::BLUEPRINTS[TestForm::EXAM_TYPE_LR];

            $activeCount = $test->questions->filter(fn ($q) => strtoupper((string) $q->status) === Question::STATUS_ACTIVE)->count();
            $targetCount = (int) ($definition['total_questions'] ?? 200);

            $presentPartCodes = $test->questions
                ->map(fn ($q) => strtoupper((string) ($q->section?->code ?? '')))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $partsSummary = ! empty($presentPartCodes)
                ? (count($presentPartCodes) > 1 ? min($presentPartCodes).'–'.max($presentPartCodes) : $presentPartCodes[0])
                : 'Chưa có câu hỏi';

            $cardData = [
                'id' => $test->test_form_id,
                'title' => $test->title,
                'code' => $test->test_code,
                'exam_type' => $examType,
                'type_name' => $definition['name'],
                'duration' => (int) ($definition['total_time'] ?? 120),
                'questions_count' => $activeCount,
                'target_questions' => $targetCount,
                'parts_summary' => $partsSummary,
                'attempts_count' => $test->attempts()->count(),
                'created_at' => $test->created_at?->format('d/m/Y') ?? '-',
            ];

            if ($examType === 'PLACEMENT_TEST') {
                $placementTests[] = $cardData;
            } elseif (in_array($examType, [TestForm::EXAM_TYPE_LR, TestForm::EXAM_TYPE_LISTENING, TestForm::EXAM_TYPE_READING], true)) {
                $lrTests[] = $cardData;
            } else {
                $swTests[] = $cardData;
            }
        }

        $availableAttempts = 5;
        if (auth()->check()) {
            $availableAttempts = auth()->user()->energy ?? 5;
        }

        return view('pages.thi-thu', compact('placementTests', 'lrTests', 'swTests', 'availableAttempts'));
    }

    /**
     * BƯỚC 2: Trang giới thiệu đề thi thực tế và chọn 3 hình thức thi
     */
    public function intro(int $id): View
    {
        $test = TestForm::query()
            ->with([
                'blueprint',
                'questions' => fn ($q) => $q->orderBy('test_form_question.sequence_no')
                    ->with(['section', 'questionSet']),
            ])
            ->findOrFail($id);

        $examType = (string) ($test->blueprint?->exam_type ?? TestForm::EXAM_TYPE_LR);
        $definition = ExamSampleDataService::BLUEPRINTS[$examType] ?? ExamSampleDataService::BLUEPRINTS[TestForm::EXAM_TYPE_LR];

        // Đếm câu hỏi theo mã Part thực tế có trong đề
        $questionCountsByPart = [];
        foreach ($test->questions as $q) {
            $code = strtoupper((string) ($q->section?->code ?? ''));
            if ($code !== '' && strtoupper((string) $q->status) === Question::STATUS_ACTIVE) {
                $questionCountsByPart[$code] = ($questionCountsByPart[$code] ?? 0) + 1;
            }
        }

        // Tạo danh sách cấu trúc các Part cho đề
        $parts = [];
        $totalQuestionsInTest = 0;
        $listeningQuestions = 0;
        $readingQuestions = 0;
        $listeningPartsCount = 0;
        $readingPartsCount = 0;

        foreach ($definition['skills'] as $skillKey => $skill) {
            foreach ($skill['parts'] as $p) {
                $code = strtoupper((string) $p['code']);
                $actualCount = $questionCountsByPart[$code] ?? 0;
                $totalQuestionsInTest += $actualCount;

                $skillCode = $skill['skill_code'] ?? 'LC';
                if ($skillCode === 'LC' || in_array($code, ['PART1', 'PART2', 'PART3', 'PART4'], true)) {
                    $listeningQuestions += $actualCount;
                    if ($actualCount > 0) {
                        $listeningPartsCount++;
                    }
                } else {
                    $readingQuestions += $actualCount;
                    if ($actualCount > 0) {
                        $readingPartsCount++;
                    }
                }

                $parts[] = [
                    'code' => $code,
                    'part_number' => $p['part_number'] ?? null,
                    'name' => $p['name'] ?? $code,
                    'description' => $p['description'] ?? '',
                    'target_count' => (int) ($p['target_count'] ?? 0),
                    'actual_count' => $actualCount,
                    'time_minutes' => self::PART_MINUTES[$code] ?? 15,
                    'has_questions' => $actualCount > 0,
                    'skill_code' => $skillCode,
                    'skill_name' => $skill['name'] ?? '',
                ];
            }
        }

        // Lịch sử thi của user đối với đề này
        $userId = auth()->id() ?? 0;
        $historyRows = TestAttempt::query()
            ->where('test_form_id', $id)
            ->where('user_id', $userId)
            ->with('scoreResult')
            ->orderByDesc('started_at')
            ->take(10)
            ->get();

        $examHistory = [];
        foreach ($historyRows as $attempt) {
            $scoreDisplay = 'Chưa hoàn thành';
            if ($attempt->scoreResult) {
                $scoreDisplay = (int) $attempt->scoreResult->total_score.' điểm';
            }

            $durationMinutes = $attempt->ended_at && $attempt->started_at
                ? max(1, $attempt->started_at->diffInMinutes($attempt->ended_at)).' phút'
                : 'Đang làm';

            $examHistory[] = [
                'id' => $attempt->attempt_id,
                'date' => $attempt->started_at?->format('d/m/Y H:i') ?? '-',
                'mode' => $attempt->session_type === 'FULL_TEST' ? 'Full Test' : 'Luyện từng phần',
                'duration' => $durationMinutes,
                'score' => $scoreDisplay,
                'status' => $attempt->status === 'COMPLETED' ? 'Đã hoàn thành' : 'Đang làm',
            ];
        }

        $highestScore = ScoreResult::query()
            ->whereHas('attempt', fn ($q) => $q->where('test_form_id', $id)->where('user_id', $userId))
            ->max('total_score');

        return view('pages.thi-thu-gioi-thieu-de', [
            'test' => $test,
            'examType' => $examType,
            'definition' => $definition,
            'parts' => $parts,
            'totalQuestionsInTest' => $totalQuestionsInTest,
            'totalDuration' => (int) ($definition['total_time'] ?? 120),
            'listeningQuestions' => $listeningQuestions,
            'readingQuestions' => $readingQuestions,
            'listeningPartsCount' => $listeningPartsCount,
            'readingPartsCount' => $readingPartsCount,
            'examHistory' => $examHistory,
            'highestScore' => $highestScore !== null ? (int) $highestScore : null,
        ]);
    }

    /**
     * BƯỚC 3: Xử lý bắt đầu thi (Hỗ trợ 3 hình thức: Full Test, Luyện kỹ năng, Luyện riêng từng Part)
     */
    public function start(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'exam_mode' => 'required|in:fulltest,skill,selected_parts',
            'selected_skill' => 'nullable|in:listening,reading,speaking,writing',
            'exam_parts' => 'nullable|array',
            'exam_parts.*' => 'string',
        ]);

        $test = TestForm::query()
            ->with(['blueprint'])
            ->findOrFail($id);

        $examMode = $validated['exam_mode'];
        $selectedSkill = $validated['selected_skill'] ?? null;
        $selectedParts = array_map('strtoupper', (array) ($validated['exam_parts'] ?? []));

        $questionQuery = $test->questions()
            ->where('questions.status', Question::STATUS_ACTIVE)
            ->with(['section']);

        $durationMinutes = 120;
        $sessionType = 'FULL_TEST';

        if ($examMode === 'skill') {
            $sessionType = 'PRACTICE_PART';
            if ($selectedSkill === 'listening') {
                $questionQuery->whereHas('section', fn ($q) => $q->whereIn('code', ['PART1', 'PART2', 'PART3', 'PART4']));
                $durationMinutes = 45;
            } elseif ($selectedSkill === 'reading') {
                $questionQuery->whereHas('section', fn ($q) => $q->whereIn('code', ['PART5', 'PART6', 'PART7']));
                $durationMinutes = 75;
            } elseif ($selectedSkill === 'speaking') {
                $questionQuery->whereHas('section', fn ($q) => $q->where('code', 'like', 'SPK_%'));
                $durationMinutes = 20;
            } elseif ($selectedSkill === 'writing') {
                $questionQuery->whereHas('section', fn ($q) => $q->where('code', 'like', 'WRITING_%'));
                $durationMinutes = 60;
            }
        } elseif ($examMode === 'selected_parts') {
            $sessionType = 'PRACTICE_PART';
            if (empty($selectedParts)) {
                return back()->with('error', 'Vui lòng chọn ít nhất một phần thi (Part) để bắt đầu.');
            }

            $questionQuery->whereHas('section', fn ($q) => $q->whereIn('code', $selectedParts));

            $durationMinutes = 0;
            foreach ($selectedParts as $partCode) {
                $durationMinutes += self::PART_MINUTES[$partCode] ?? 15;
            }
            if ($durationMinutes <= 0) {
                $durationMinutes = 30;
            }
        } else {
            // Full Test
            $examType = (string) ($test->blueprint?->exam_type ?? TestForm::EXAM_TYPE_LR);
            $definition = ExamSampleDataService::BLUEPRINTS[$examType] ?? ExamSampleDataService::BLUEPRINTS[TestForm::EXAM_TYPE_LR];
            $durationMinutes = (int) ($definition['total_time'] ?? 120);
        }

        $rawQuestions = $questionQuery->get();

        if ($rawQuestions->isEmpty()) {
            return back()->with('error', 'Phần thi bạn chọn hiện chưa có câu hỏi nào trong đề. Vui lòng chọn phần khác.');
        }

        // Sắp xếp câu hỏi tuyệt đối theo trình tự chuẩn ETS:
        // PART1 -> PART2 -> PART3 -> PART4 -> PART5 -> PART6 -> PART7 -> SPK_* -> WRITING_*
        $etsOrder = [
            'PART1' => 1, 'PART2' => 2, 'PART3' => 3, 'PART4' => 4,
            'PART5' => 5, 'PART6' => 6, 'PART7' => 7,
            'SPK_T1' => 8, 'SPK_T2' => 9, 'SPK_T3' => 10, 'SPK_T4' => 11, 'SPK_T5' => 12,
            'WRITING_T1' => 13, 'WRITING_T2' => 14, 'WRITING_T3' => 15,
        ];

        $sortedQuestions = $rawQuestions->sort(function ($a, $b) use ($etsOrder) {
            $secA = strtoupper((string) ($a->section?->code ?? 'PART5'));
            $secB = strtoupper((string) ($b->section?->code ?? 'PART5'));
            $orderA = $etsOrder[$secA] ?? 99;
            $orderB = $etsOrder[$secB] ?? 99;
            if ($orderA !== $orderB) {
                return $orderA <=> $orderB;
            }

            return ($a->pivot->sequence_no ?? $a->sequence_no ?? 0) <=> ($b->pivot->sequence_no ?? $b->sequence_no ?? 0);
        })->values();

        // Trừ 1 lượt thi nếu user có tài khoản
        $userId = auth()->id();
        if ($userId) {
            $user = User::query()->find($userId);
            if ($user && isset($user->energy) && $user->energy <= 0) {
                return back()->with('error', 'Bạn đã hết lượt thi khả dụng. Hãy hoàn thành Flashcard từ vựng để nhận thêm lượt.');
            }
            if ($user && isset($user->energy)) {
                $user->decrement('energy', 1);
            }
        } else {
            $userId = User::query()->value('user_id') ?? 1;
        }

        // Khởi tạo bản ghi Attempt
        $attempt = DB::transaction(function () use ($userId, $test, $sessionType, $sortedQuestions): TestAttempt {
            $testAttempt = TestAttempt::query()->create([
                'user_id' => $userId,
                'test_form_id' => $test->test_form_id,
                'session_type' => $sessionType,
                'started_at' => now(),
                'status' => 'IN_PROGRESS',
            ]);

            // Ghi nhận danh sách câu hỏi vào attempt_response theo thứ tự 1..N liên tục
            $responses = [];
            $seq = 1;
            foreach ($sortedQuestions as $q) {
                $responses[] = [
                    'attempt_id' => $testAttempt->attempt_id,
                    'question_id' => $q->question_id,
                    'sequence_no' => $seq++,
                    'text_response' => null,
                    'media_url' => null,
                    'score' => null,
                    'feedback' => null,
                    'submitted_at' => null,
                ];
            }

            DB::table('attempt_response')->insert($responses);

            return $testAttempt;
        });

        // Lưu thời lượng vào session
        session(["attempt_{$attempt->attempt_id}_duration" => $durationMinutes]);

        return redirect()->route('thi-thu.giao-dien-thi', $attempt->attempt_id);
    }

    /**
     * BƯỚC 4: Kết nối giao diện làm bài thi chuẩn cấu trúc TOEIC & Clustered Items
     */
    public function exam(int $attemptId): View|RedirectResponse
    {
        $attempt = TestAttempt::query()
            ->with([
                'testForm.blueprint',
                'responses' => fn ($q) => $q->orderBy('sequence_no')
                    ->with([
                        'question.options',
                        'question.questionSet',
                        'question.section.skill',
                        'question.questionType',
                    ]),
            ])
            ->findOrFail($attemptId);

        if ($attempt->status === 'COMPLETED') {
            return redirect()->route('thi-thu.ket-qua', $attemptId);
        }

        $durationMinutes = (int) session("attempt_{$attemptId}_duration", 120);
        $elapsedSeconds = now()->diffInSeconds($attempt->started_at ?? now());
        $totalSeconds = $durationMinutes * 60;
        $remainingSeconds = max(0, $totalSeconds - $elapsedSeconds);

        // Đếm tổng số câu theo từng Part trong bài thi này
        $partTotals = [];
        foreach ($attempt->responses as $resp) {
            $code = strtoupper((string) ($resp->question?->section?->code ?? 'PART5'));
            $partTotals[$code] = ($partTotals[$code] ?? 0) + 1;
        }

        // BƯỚC QUAN TRỌNG: Gom nhóm các câu hỏi theo cụm (Clusters)
        // - Part 1, 2, 5: Mỗi câu là 1 cụm độc lập (is_group = false)
        // - Part 3, 4, 6, 7: Nhóm các câu liên tiếp dùng chung đoạn văn/bài nói
        $clusters = [];
        $currentCluster = null;
        $partCurrentCounts = [];
        $flatQuestions = [];

        foreach ($attempt->responses as $resp) {
            $q = $resp->question;
            if (! $q) {
                continue;
            }

            $secCode = strtoupper((string) ($q->section?->code ?? 'PART5'));
            $skillCode = strtoupper((string) ($q->section?->skill?->code ?? 'RC'));
            $partCurrentCounts[$secCode] = ($partCurrentCounts[$secCode] ?? 0) + 1;
            $partQNum = $partCurrentCounts[$secCode];
            $totalInThisPart = $partTotals[$secCode] ?? 1;

            $isSpeaking = str_starts_with($secCode, 'SPK_');
            $isWriting = str_starts_with($secCode, 'WRITING_');
            $isListening = $skillCode === 'LC' || in_array($secCode, ['PART1', 'PART2', 'PART3', 'PART4'], true);
            $isReading = $skillCode === 'RC' || in_array($secCode, ['PART5', 'PART6', 'PART7'], true);
            $isGroupable = in_array($secCode, ['PART3', 'PART4', 'PART6', 'PART7'], true);

            // BẢO MẬT & ĐÚNG ĐỊNH DẠNG:
            // 1. Không gửi `is_correct` xuống Client
            // 2. Part 2 chỉ có 3 đáp án: A, B, C (Loại bỏ đáp án D nếu có)
            $safeOptions = $q->options
                ->filter(function ($opt) use ($secCode) {
                    if ($secCode === 'PART2') {
                        return in_array(strtoupper(trim((string) $opt->label)), ['A', 'B', 'C'], true);
                    }

                    return true;
                })
                ->map(fn ($opt): array => [
                    'option_id' => $opt->option_id,
                    'label' => $opt->label,
                    'content' => $opt->content,
                ])
                ->values()
                ->all();

            $mediaUrl = $q->questionSet?->resolved_media_url ?? ($q->media_url ?? null ? asset(ltrim($q->media_url, '/')) : null);
            $audioUrl = $q->questionSet?->resolved_audio_url ?? ($q->audio_url ?? null ? asset(ltrim($q->audio_url, '/')) : null);

            $setTitle = trim((string) ($q->questionSet?->title ?? ''));
            $stimulusText = $q->questionSet?->stimulus_text ?? null;

            // Xác định khoá nhóm
            if ($isGroupable && $setTitle !== '') {
                $clusterKey = $secCode.'_'.md5($setTitle);
            } elseif ($isGroupable && $q->question_set_id) {
                $clusterKey = $secCode.'_qs_'.$q->question_set_id;
            } else {
                $clusterKey = 'single_'.$resp->sequence_no;
            }

            $questionItem = [
                'question_id' => $q->question_id,
                'sequence_no' => (int) $resp->sequence_no,
                'part_q_num' => $partQNum,
                'content' => $q->content,
                'options' => $safeOptions,
                'saved_answer' => $resp->text_response,
                'is_speaking' => $isSpeaking,
                'is_writing' => $isWriting,
                'explanation' => $q->explanation,
            ];

            if ($currentCluster && $currentCluster['key'] === $clusterKey) {
                $currentCluster['questions'][] = $questionItem;
                $currentCluster['end_seq'] = (int) $resp->sequence_no;
                $currentCluster['end_part_num'] = $partQNum;
            } else {
                if ($currentCluster) {
                    $clusters[] = $currentCluster;
                }
                $currentCluster = [
                    'key' => $clusterKey,
                    'cluster_index' => count($clusters),
                    'section_code' => $secCode,
                    'section_name' => $q->section?->name ?? $secCode,
                    'is_group' => $isGroupable,
                    'is_listening' => $isListening,
                    'is_reading' => $isReading,
                    'is_speaking' => $isSpeaking,
                    'is_writing' => $isWriting,
                    'start_seq' => (int) $resp->sequence_no,
                    'end_seq' => (int) $resp->sequence_no,
                    'start_part_num' => $partQNum,
                    'end_part_num' => $partQNum,
                    'total_part_questions' => $totalInThisPart,
                    'title' => $setTitle,
                    'stimulus_text' => $stimulusText,
                    'audio_url' => $audioUrl,
                    'media_url' => $mediaUrl,
                    'questions' => [$questionItem],
                ];
            }

            $flatQuestions[] = [
                'question_id' => $q->question_id,
                'sequence_no' => (int) $resp->sequence_no,
                'section_code' => $secCode,
                'cluster_index' => count($clusters), // tạm thời, cập nhật sau vòng lặp
                'saved_answer' => $resp->text_response,
            ];
        }

        if ($currentCluster) {
            $clusters[] = $currentCluster;
        }

        // Hoàn thiện thông tin nhãn hiển thị cho từng cụm
        foreach ($clusters as $idx => &$cluster) {
            $cluster['cluster_index'] = $idx;
            $startSeq = $cluster['start_seq'];
            $endSeq = $cluster['end_seq'];
            $startP = $cluster['start_part_num'];
            $endP = $cluster['end_part_num'];
            $totP = $cluster['total_part_questions'];

            if ($startSeq === $endSeq) {
                $cluster['display_label'] = "Câu {$startSeq}";
                $cluster['badge_label'] = "Q {$startP}/{$totP}";
            } else {
                $cluster['display_label'] = "Câu {$startSeq}–{$endSeq}";
                $cluster['badge_label'] = "Q {$startP}-{$endP}/{$totP}";
            }
        }
        unset($cluster);

        // Cập nhật đúng `cluster_index` cho `flatQuestions`
        $seqToClusterMap = [];
        foreach ($clusters as $cIdx => $c) {
            foreach ($c['questions'] as $q) {
                $seqToClusterMap[$q['sequence_no']] = $cIdx;
            }
        }
        foreach ($flatQuestions as &$fq) {
            $fq['cluster_index'] = $seqToClusterMap[$fq['sequence_no']] ?? 0;
        }
        unset($fq);

        return view('pages.thi-thu-giao-dien-thi', [
            'attempt' => $attempt,
            'test' => $attempt->testForm,
            'clustersPayload' => $clusters,
            'flatQuestions' => $flatQuestions,
            'partDirections' => self::PART_DIRECTIONS,
            'totalQuestions' => count($flatQuestions),
            'remainingSeconds' => $remainingSeconds,
            'durationMinutes' => $durationMinutes,
        ]);
    }

    /**
     * BƯỚC 5: Xử lý nộp bài thi và chấm điểm Server-side
     */
    public function submit(Request $request, int $attemptId): RedirectResponse
    {
        $attempt = TestAttempt::query()
            ->with([
                'testForm',
                'responses.question.options',
                'responses.question.section.skill',
            ])
            ->findOrFail($attemptId);

        if ($attempt->status === 'COMPLETED') {
            return redirect()->route('thi-thu.ket-qua', $attemptId);
        }

        $answers = (array) $request->input('answers', []);
        $writingTexts = (array) $request->input('writing_text', []);

        $correctCount = 0;
        $lcCorrect = 0;
        $rcCorrect = 0;
        $spkScore = 0.0;
        $wrtScore = 0.0;

        DB::transaction(function () use ($attempt, $answers, $writingTexts, &$correctCount, &$lcCorrect, &$rcCorrect, &$spkScore, &$wrtScore): void {
            foreach ($attempt->responses as $response) {
                $q = $response->question;
                if (! $q) {
                    continue;
                }

                $qId = $q->question_id;
                $secCode = strtoupper((string) ($q->section?->code ?? ''));
                $skillCode = strtoupper((string) ($q->section?->skill?->code ?? ''));

                $isSpeaking = str_starts_with($secCode, 'SPK_');
                $isWriting = str_starts_with($secCode, 'WRITING_');

                if ($isWriting) {
                    $text = trim((string) ($writingTexts[$qId] ?? $answers[$qId] ?? ''));
                    $response->update([
                        'text_response' => $text,
                        'score' => ! empty($text) ? 1.00 : 0.00,
                        'submitted_at' => now(),
                    ]);
                    if (! empty($text)) {
                        $wrtScore += 1.00;
                    }
                } elseif ($isSpeaking) {
                    $mediaPath = $answers[$qId] ?? null;
                    $response->update([
                        'media_url' => $mediaPath,
                        'score' => ! empty($mediaPath) ? 1.00 : 0.00,
                        'submitted_at' => now(),
                    ]);
                    if (! empty($mediaPath)) {
                        $spkScore += 1.00;
                    }
                } else {
                    $userChoice = trim((string) ($answers[$qId] ?? ''));
                    $correctOption = $q->options->firstWhere('is_correct', true);
                    $correctLabel = strtoupper((string) ($correctOption?->label ?? ''));

                    $isCorrect = (! empty($userChoice) && strtoupper($userChoice) === $correctLabel);
                    $score = $isCorrect ? (float) ($q->points ?? 1.00) : 0.00;

                    $response->update([
                        'text_response' => ! empty($userChoice) ? strtoupper($userChoice) : null,
                        'score' => $score,
                        'submitted_at' => now(),
                    ]);

                    if ($isCorrect) {
                        $correctCount++;
                        if ($skillCode === 'LC' || in_array($secCode, ['PART1', 'PART2', 'PART3', 'PART4'], true)) {
                            $lcCorrect++;
                        } else {
                            $rcCorrect++;
                        }
                    }
                }
            }

            // Tính điểm quy đổi TOEIC thang 495
            $lcScore = $lcCorrect > 0 ? min(495, round(($lcCorrect / max(1, 100)) * 495, 2)) : 0;
            $rcScore = $rcCorrect > 0 ? min(495, round(($rcCorrect / max(1, 100)) * 495, 2)) : 0;
            $totalScore = $lcScore + $rcScore;
            if ($totalScore <= 0 && $correctCount > 0) {
                $totalScore = $correctCount;
            }

            ScoreResult::query()->updateOrCreate(
                ['attempt_id' => $attempt->attempt_id],
                [
                    'listening_score' => $lcScore,
                    'reading_score' => $rcScore,
                    'speaking_score' => $spkScore,
                    'writing_score' => $wrtScore,
                    'total_score' => $totalScore,
                    'graded_at' => now(),
                ]
            );

            $attempt->update([
                'status' => 'COMPLETED',
                'ended_at' => now(),
                'submitted_at' => now(),
            ]);
        });

        return redirect()->route('thi-thu.ket-qua', $attemptId);
    }

    /**
     * BƯỚC 5 (tiếp): Xem kết quả chi tiết bài thi
     */
    public function result(int $attemptId): View
    {
        $attempt = TestAttempt::query()
            ->with([
                'testForm.blueprint',
                'scoreResult',
                'responses.question.section.skill',
                'responses.question.options',
            ])
            ->findOrFail($attemptId);

        $totalQuestions = $attempt->responses->count();
        $answeredCount = 0;
        $correctCount = 0;
        $partStats = [];

        foreach ($attempt->responses as $resp) {
            $q = $resp->question;
            if (! $q) {
                continue;
            }

            $partCode = strtoupper((string) ($q->section?->code ?? 'UNKNOWN'));
            if (! isset($partStats[$partCode])) {
                $partStats[$partCode] = [
                    'code' => $partCode,
                    'name' => $q->section?->name ?? $partCode,
                    'total' => 0,
                    'correct' => 0,
                    'percentage' => 0,
                ];
            }

            $partStats[$partCode]['total']++;

            $hasAnswered = ! empty($resp->text_response) || ! empty($resp->media_url);
            if ($hasAnswered) {
                $answeredCount++;
            }

            $correctOption = $q->options->firstWhere('is_correct', true);
            $correctLabel = strtoupper((string) ($correctOption?->label ?? ''));
            $userAns = strtoupper((string) ($resp->text_response ?? ''));

            if (! empty($userAns) && $userAns === $correctLabel) {
                $correctCount++;
                $partStats[$partCode]['correct']++;
            }
        }

        foreach ($partStats as &$stat) {
            $stat['percentage'] = $stat['total'] > 0 ? (int) round(($stat['correct'] / $stat['total']) * 100) : 0;
        }
        unset($stat);

        $accuracy = $totalQuestions > 0 ? (int) round(($correctCount / $totalQuestions) * 100) : 0;
        $incorrectCount = max(0, $answeredCount - $correctCount);
        $unansweredCount = max(0, $totalQuestions - $answeredCount);

        $durationMinutes = $attempt->ended_at && $attempt->started_at
            ? max(1, $attempt->started_at->diffInMinutes($attempt->ended_at))
            : 0;

        return view('pages.thi-thu-ket-qua', [
            'attempt' => $attempt,
            'test' => $attempt->testForm,
            'totalQuestions' => $totalQuestions,
            'answeredCount' => $answeredCount,
            'correctCount' => $correctCount,
            'incorrectCount' => $incorrectCount,
            'unansweredCount' => $unansweredCount,
            'accuracy' => $accuracy,
            'durationMinutes' => $durationMinutes,
            'partStats' => $partStats,
            'scoreResult' => $attempt->scoreResult,
        ]);
    }
}
