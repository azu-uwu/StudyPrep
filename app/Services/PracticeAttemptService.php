<?php

namespace App\Services;

use App\Models\AnswerOption;
use App\Models\AttemptResponse;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\ScoreResult;
use App\Models\TestAttempt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class PracticeAttemptService
{
    /**
     * Lưu hoặc cập nhật lượt luyện tập đang làm dở (Lưu & Thoát).
     *
     * @param  array<string, mixed>  $sessionData  Dữ liệu phiên từ reading_practice_session
     * @param  array<int|string, string>|null  $incomingAnswers  Snapshot đáp án gửi kèm từ frontend nếu có
     * @param  int|null  $userId  ID người dùng đăng nhập
     *
     * @throws InvalidArgumentException
     */
    public function saveAttempt(array $sessionData, ?array $incomingAnswers = null, ?int $userId = null): TestAttempt
    {
        $userId = $userId ?? auth()->id();
        if (! $userId) {
            throw new InvalidArgumentException('Người dùng cần đăng nhập để lưu bài luyện tập.');
        }

        $part = (int) ($sessionData['part'] ?? 0);
        if (! in_array($part, [5, 6, 7], true)) {
            throw new InvalidArgumentException('Phần luyện tập không hợp lệ.');
        }

        $questionIds = array_values(array_filter(
            array_map('intval', (array) ($sessionData['question_ids'] ?? [])),
            fn ($id) => $id > 0
        ));

        if (empty($questionIds)) {
            throw new InvalidArgumentException('Danh sách câu hỏi luyện tập không được để trống.');
        }

        if (count($questionIds) !== count(array_unique($questionIds))) {
            throw new InvalidArgumentException('Danh sách câu hỏi chứa câu trùng lặp.');
        }

        $expectedSectionCode = 'PART'.$part;
        $dbQuestions = Question::query()
            ->whereIn('question_id', $questionIds)
            ->with(['section.skill', 'options'])
            ->get()
            ->keyBy('question_id');

        if ($dbQuestions->count() !== count($questionIds)) {
            throw new InvalidArgumentException('Một số câu hỏi không tồn tại trên hệ thống.');
        }

        foreach ($questionIds as $qid) {
            $q = $dbQuestions->get($qid);
            if (! $q || $q->status !== Question::STATUS_ACTIVE) {
                throw new InvalidArgumentException("Câu hỏi #{$qid} không ở trạng thái khả dụng.");
            }
            if ($q->section?->skill?->code !== 'RC' || $q->section?->code !== $expectedSectionCode) {
                throw new InvalidArgumentException("Câu hỏi #{$qid} không thuộc kỹ năng RC {$expectedSectionCode}.");
            }
        }

        $finalAnswers = $this->extractFinalAnswers($sessionData, $incomingAnswers, $dbQuestions);

        return DB::transaction(function () use ($sessionData, $userId, $questionIds, $finalAnswers) {
            $attemptId = ! empty($sessionData['attempt_id']) ? (int) $sessionData['attempt_id'] : null;

            if ($attemptId !== null) {
                $attempt = TestAttempt::query()
                    ->where('attempt_id', $attemptId)
                    ->lockForUpdate()
                    ->first();

                if (! $attempt) {
                    throw new InvalidArgumentException('Lượt luyện tập không tồn tại.');
                }

                if ((int) $attempt->user_id !== (int) $userId) {
                    throw new InvalidArgumentException('Lượt luyện tập không thuộc về người dùng hiện tại.');
                }

                if ($attempt->session_type !== TestAttempt::SESSION_TYPE_PRACTICE || $attempt->test_form_id !== null) {
                    throw new InvalidArgumentException('Lượt này không phải là lượt luyện tập hợp lệ.');
                }

                if ($attempt->status === TestAttempt::STATUS_COMPLETED) {
                    throw new InvalidArgumentException('Không thể cập nhật lượt luyện tập đã nộp bài hoàn thành.');
                }

                // Cập nhật các đáp án mới nhất vào attempt_response
                foreach ($questionIds as $idx => $qid) {
                    $userPick = $finalAnswers[$qid] ?? null;
                    AttemptResponse::query()->updateOrCreate(
                        [
                            'attempt_id' => $attempt->attempt_id,
                            'question_id' => $qid,
                        ],
                        [
                            'sequence_no' => $idx + 1,
                            'text_response' => $userPick,
                            'media_url' => null,
                            'score' => null,
                            'feedback' => null,
                            'submitted_at' => null,
                        ]
                    );
                }

                return $attempt;
            }

            // Kiểm tra xem người dùng đã có bài IN_PROGRESS cùng danh sách câu hỏi này chưa (tránh click đúp)
            $existingAttempt = TestAttempt::query()
                ->where('user_id', $userId)
                ->where('session_type', TestAttempt::SESSION_TYPE_PRACTICE)
                ->where('status', TestAttempt::STATUS_IN_PROGRESS)
                ->whereNull('test_form_id')
                ->whereHas('attemptResponses', function ($q) use ($questionIds) {
                    $q->whereIn('question_id', $questionIds);
                }, '=', count($questionIds))
                ->orderByDesc('attempt_id')
                ->lockForUpdate()
                ->first();

            if ($existingAttempt) {
                foreach ($questionIds as $idx => $qid) {
                    $userPick = $finalAnswers[$qid] ?? null;
                    AttemptResponse::query()->updateOrCreate(
                        [
                            'attempt_id' => $existingAttempt->attempt_id,
                            'question_id' => $qid,
                        ],
                        [
                            'sequence_no' => $idx + 1,
                            'text_response' => $userPick,
                            'media_url' => null,
                            'score' => null,
                            'feedback' => null,
                            'submitted_at' => null,
                        ]
                    );
                }

                return $existingAttempt;
            }

            // Tạo attempt mới
            $startedAt = ! empty($sessionData['started_at'])
                ? Carbon::parse($sessionData['started_at'])
                : now();

            $attempt = TestAttempt::query()->create([
                'user_id' => $userId,
                'test_form_id' => null,
                'session_type' => TestAttempt::SESSION_TYPE_PRACTICE,
                'started_at' => $startedAt,
                'ended_at' => null,
                'submitted_at' => null,
                'status' => TestAttempt::STATUS_IN_PROGRESS,
            ]);

            foreach ($questionIds as $idx => $qid) {
                $userPick = $finalAnswers[$qid] ?? null;
                AttemptResponse::query()->create([
                    'attempt_id' => $attempt->attempt_id,
                    'question_id' => $qid,
                    'sequence_no' => $idx + 1,
                    'text_response' => $userPick,
                    'media_url' => null,
                    'score' => null,
                    'feedback' => null,
                    'submitted_at' => null,
                ]);
            }

            return $attempt;
        });
    }

    /**
     * Tìm lượt luyện tập đang làm dở (IN_PROGRESS) theo người dùng, kỹ năng và phần luyện tập.
     */
    public function findInProgressAttempt(int $userId, string $skillCode = 'RC', string $sectionCode = 'PART5'): ?TestAttempt
    {
        return TestAttempt::query()
            ->where('user_id', $userId)
            ->where('session_type', TestAttempt::SESSION_TYPE_PRACTICE)
            ->where('status', TestAttempt::STATUS_IN_PROGRESS)
            ->whereNull('test_form_id')
            ->whereHas('attemptResponses.question.section', function ($q) use ($skillCode, $sectionCode) {
                $q->where('code', $sectionCode)
                    ->whereHas('skill', fn ($s) => $s->where('code', $skillCode));
            })
            ->whereDoesntHave('attemptResponses.question.section', function ($q) use ($sectionCode) {
                $q->where('code', '!=', $sectionCode);
            })
            ->orderByDesc('attempt_id')
            ->first();
    }

    /**
     * Lấy trạng thái bài làm dở cho cả 3 Part của Reading (Part 5, Part 6, Part 7) để tối ưu query cho trang danh sách.
     *
     * @return array<int, ?TestAttempt> Map part (5, 6, 7) => TestAttempt|null
     */
    public function getInProgressAttemptsByPart(int $userId): array
    {
        $attempts = TestAttempt::query()
            ->where('user_id', $userId)
            ->where('session_type', TestAttempt::SESSION_TYPE_PRACTICE)
            ->where('status', TestAttempt::STATUS_IN_PROGRESS)
            ->whereNull('test_form_id')
            ->with([
                'attemptResponses.question.section.skill',
            ])
            ->orderByDesc('attempt_id')
            ->get();

        $result = [
            5 => null,
            6 => null,
            7 => null,
        ];

        foreach ($attempts as $attempt) {
            $responses = $attempt->attemptResponses;
            if ($responses->isEmpty()) {
                continue;
            }

            $firstSection = $responses->first()?->question?->section;
            if (! $firstSection || $firstSection->skill?->code !== 'RC') {
                continue;
            }

            $sectionCode = $firstSection->code;
            // Kiểm tra toàn bộ câu hỏi trong attempt đều thuộc cùng section
            $allMatch = $responses->every(
                fn ($r) => $r->question?->section?->code === $sectionCode && $r->question?->section?->skill?->code === 'RC'
            );

            if (! $allMatch) {
                continue;
            }

            $partNum = match ($sectionCode) {
                'PART5' => 5,
                'PART6' => 6,
                'PART7' => 7,
                default => null,
            };

            if ($partNum !== null && $result[$partNum] === null) {
                $result[$partNum] = $attempt;
            }
        }

        return $result;
    }

    /**
     * Khôi phục dữ liệu bài luyện tập đã lưu trong DB để tải vào Session.
     *
     * @return array{
     *     attempt: TestAttempt,
     *     sessionData: array<string, mixed>,
     *     part: int,
     *     questionIds: array<int>
     * }
     */
    public function resumeAttempt(int $attemptId, int $userId): array
    {
        $attempt = TestAttempt::query()
            ->where('attempt_id', $attemptId)
            ->where('user_id', $userId)
            ->first();

        if (! $attempt) {
            throw new InvalidArgumentException('Không tìm thấy lượt luyện tập yêu cầu.');
        }

        if ($attempt->session_type !== TestAttempt::SESSION_TYPE_PRACTICE || $attempt->test_form_id !== null) {
            throw new InvalidArgumentException('Lượt này không phải là lượt luyện tập hợp lệ.');
        }

        if ($attempt->status !== TestAttempt::STATUS_IN_PROGRESS) {
            throw new InvalidArgumentException('Lượt luyện tập này đã hoàn thành hoặc không ở trạng thái đang làm.');
        }

        $responses = AttemptResponse::query()
            ->where('attempt_id', $attemptId)
            ->orderBy('sequence_no', 'asc')
            ->with([
                'question' => function ($q) {
                    $q->with([
                        'options' => fn ($opt) => $opt->select(['option_id', 'question_id', 'label', 'content', 'is_correct']),
                        'questionSet',
                        'section.skill',
                        'questionType',
                    ]);
                },
            ])
            ->get();

        if ($responses->isEmpty()) {
            throw new RuntimeException('Lượt luyện tập không có câu hỏi nào để khôi phục.');
        }

        $firstQuestion = $responses->first()->question;
        $sectionCode = $firstQuestion?->section?->code;
        if (! in_array($sectionCode, ['PART5', 'PART6', 'PART7'], true) || $firstQuestion?->section?->skill?->code !== 'RC') {
            throw new RuntimeException('Dữ liệu câu hỏi của lượt luyện tập không thuộc phần Reading hợp lệ.');
        }

        $part = match ($sectionCode) {
            'PART6' => 6,
            'PART7' => 7,
            default => 5,
        };

        $questionIds = [];
        $sequenceMap = [];
        $answers = [];
        $questionStates = [];

        foreach ($responses as $idx => $resp) {
            $qid = (int) $resp->question_id;
            $questionIds[] = $qid;
            $seqNo = $resp->sequence_no ?: ($idx + 1);
            $sequenceMap[$qid] = $seqNo;

            $userPick = $resp->text_response ? strtoupper(trim((string) $resp->text_response)) : null;
            if ($userPick !== null && in_array($userPick, ['A', 'B', 'C', 'D'], true)) {
                $answers[$qid] = $userPick;
                $questionStates[$qid] = [
                    'answered' => true,
                    'userPick' => $userPick,
                    'revealed' => false,
                ];
            } else {
                $questionStates[$qid] = [
                    'answered' => false,
                    'userPick' => null,
                    'revealed' => false,
                ];
            }
        }

        $groups = [];
        if ($part === 6 || $part === 7) {
            $groupedResponses = $responses->groupBy(fn ($r) => $r->question?->question_set_id);
            foreach ($groupedResponses as $setId => $setResponses) {
                $sId = (int) $setId;
                $setQids = $setResponses->pluck('question_id')->map(fn ($id) => (int) $id)->values()->all();
                $setAnswers = [];
                $allAnsweredInSet = true;

                foreach ($setQids as $qId) {
                    if (isset($answers[$qId])) {
                        $setAnswers[$qId] = $answers[$qId];
                    } else {
                        $allAnsweredInSet = false;
                    }
                }

                $groups[$sId] = [
                    'question_set_id' => $sId,
                    'question_ids' => $setQids,
                    'completed' => $allAnsweredInSet,
                    'answers' => $setAnswers,
                ];
            }
        }

        $count = count($questionIds);
        $sessionData = [
            'attempt_id' => $attempt->attempt_id,
            'user_id' => $userId,
            'part' => $part,
            'target_count' => $count,
            'actual_count' => $count,
            'started_at' => $attempt->started_at?->toIso8601String() ?? now()->toIso8601String(),
            'question_ids' => $questionIds,
            'sequence_map' => $sequenceMap,
            'answers' => $answers,
            'question_states' => $questionStates,
            'groups' => $groups,
            'flagged_questions' => [],
        ];

        return [
            'attempt' => $attempt,
            'sessionData' => $sessionData,
            'part' => $part,
            'questionIds' => $questionIds,
        ];
    }

    /**
     * Nộp bài luyện tập, chấm điểm tự động và lưu kết quả vào CSDL.
     *
     * @param  array<string, mixed>  $sessionData
     * @param  array<int|string, string>  $incomingAnswers
     * @return array{attempt: TestAttempt, result: array<string, mixed>}
     *
     * @throws InvalidArgumentException
     */
    public function submitAttempt(
        array $sessionData,
        array $incomingAnswers = [],
        ?int $userId = null
    ): array {
        $userId = $userId ?? auth()->id();
        if (! $userId) {
            throw new InvalidArgumentException('Người dùng cần đăng nhập để nộp bài.');
        }

        $part = (int) ($sessionData['part'] ?? 5);
        if (! in_array($part, [5, 6, 7], true)) {
            $part = 5;
        }

        $questionIds = array_values(array_filter(
            array_map('intval', (array) ($sessionData['question_ids'] ?? [])),
            fn ($id) => $id > 0
        ));

        if (empty($questionIds)) {
            throw new InvalidArgumentException('Không có câu hỏi nào để nộp bài.');
        }

        $dbQuestions = Question::query()
            ->whereIn('question_id', $questionIds)
            ->with([
                'options' => fn ($opt) => $opt->select(['option_id', 'question_id', 'label', 'content', 'is_correct']),
                'questionSet',
                'section',
            ])
            ->get()
            ->keyBy('question_id');

        if ($dbQuestions->count() !== count($questionIds)) {
            throw new InvalidArgumentException('Một số câu hỏi trong bài không tồn tại trên hệ thống.');
        }

        $finalAnswers = $this->extractFinalAnswers($sessionData, $incomingAnswers, $dbQuestions);

        return DB::transaction(function () use (
            $sessionData,
            $userId,
            $part,
            $questionIds,
            $dbQuestions,
            $finalAnswers
        ) {
            $attemptId = ! empty($sessionData['attempt_id']) ? (int) $sessionData['attempt_id'] : null;

            if ($attemptId !== null) {
                $attempt = TestAttempt::query()
                    ->where('attempt_id', $attemptId)
                    ->lockForUpdate()
                    ->first();

                if (! $attempt) {
                    throw new InvalidArgumentException('Lượt luyện tập không tồn tại.');
                }

                if ((int) $attempt->user_id !== (int) $userId) {
                    throw new InvalidArgumentException('Lượt luyện tập không thuộc về người dùng hiện tại.');
                }

                if ($attempt->status === TestAttempt::STATUS_COMPLETED) {
                    // Bài đã nộp trước đó, trả về kết quả đã lưu để ngăn nộp trùng lặp
                    $existingResult = $this->getAttemptResult($attempt->attempt_id, $userId);
                    if ($existingResult) {
                        return [
                            'attempt' => $attempt,
                            'result' => $existingResult,
                        ];
                    }
                }

                $attempt->status = TestAttempt::STATUS_COMPLETED;
                $attempt->ended_at = now();
                $attempt->submitted_at = now();
                $attempt->save();
            } else {
                // Kiểm tra xem đã có attempt vừa nộp với cùng user và danh sách câu hỏi này chưa (tránh nộp đúp)
                $recentlySubmitted = TestAttempt::query()
                    ->where('user_id', $userId)
                    ->where('session_type', TestAttempt::SESSION_TYPE_PRACTICE)
                    ->where('status', TestAttempt::STATUS_COMPLETED)
                    ->whereNull('test_form_id')
                    ->where('submitted_at', '>=', now()->subSeconds(5))
                    ->whereHas('attemptResponses', function ($q) use ($questionIds) {
                        $q->whereIn('question_id', $questionIds);
                    }, '=', count($questionIds))
                    ->orderByDesc('attempt_id')
                    ->first();

                if ($recentlySubmitted) {
                    $existingResult = $this->getAttemptResult($recentlySubmitted->attempt_id, $userId);
                    if ($existingResult) {
                        return [
                            'attempt' => $recentlySubmitted,
                            'result' => $existingResult,
                        ];
                    }
                }

                $startedAt = ! empty($sessionData['started_at'])
                    ? Carbon::parse($sessionData['started_at'])
                    : now();

                $attempt = TestAttempt::query()->create([
                    'user_id' => $userId,
                    'test_form_id' => null,
                    'session_type' => TestAttempt::SESSION_TYPE_PRACTICE,
                    'started_at' => $startedAt,
                    'ended_at' => now(),
                    'submitted_at' => now(),
                    'status' => TestAttempt::STATUS_COMPLETED,
                ]);
            }

            // Chấm điểm từng câu hỏi
            $correctCount = 0;
            $incorrectCount = 0;
            $unansweredCount = 0;
            $details = [];

            foreach ($questionIds as $idx => $qid) {
                $q = $dbQuestions->get($qid);
                $userPick = $finalAnswers[$qid] ?? null;

                $correctOpt = $q->options->firstWhere('is_correct', true);
                $correctLabel = $correctOpt ? strtoupper(trim((string) $correctOpt->label)) : null;

                if ($userPick === null) {
                    $status = 'unanswered';
                    $score = 0.00;
                    $unansweredCount++;
                } elseif ($userPick === $correctLabel) {
                    $status = 'correct';
                    $score = 1.00;
                    $correctCount++;
                } else {
                    $status = 'incorrect';
                    $score = 0.00;
                    $incorrectCount++;
                }

                AttemptResponse::query()->updateOrCreate(
                    [
                        'attempt_id' => $attempt->attempt_id,
                        'question_id' => $qid,
                    ],
                    [
                        'sequence_no' => $idx + 1,
                        'text_response' => $userPick,
                        'score' => $score,
                        'submitted_at' => now(),
                    ]
                );

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

            $total = count($questionIds);
            $scorePercent = $total > 0 ? (int) round(($correctCount / $total) * 100) : 0;

            // Lưu score_result
            ScoreResult::query()->updateOrCreate(
                ['attempt_id' => $attempt->attempt_id],
                [
                    'listening_score' => null,
                    'reading_score' => $correctCount,
                    'speaking_score' => null,
                    'writing_score' => null,
                    'total_score' => $correctCount,
                    'graded_at' => now(),
                ]
            );

            $partNames = [
                5 => 'Part 5 — Hoàn thành câu',
                6 => 'Part 6 — Hoàn thành đoạn văn',
                7 => 'Part 7 — Đọc hiểu văn bản',
            ];

            $resultData = [
                'attempt_id' => $attempt->attempt_id,
                'part' => $part,
                'part_name' => $partNames[$part] ?? ('Part '.$part.' — Đọc'),
                'total' => $total,
                'correct_count' => $correctCount,
                'incorrect_count' => $incorrectCount,
                'unanswered_count' => $unansweredCount,
                'score_percent' => $scorePercent,
                'previous_percent' => '40%',
                'submitted_at' => now()->format('H:i • d/m/Y'),
                'details' => $details,
            ];

            return [
                'attempt' => $attempt,
                'result' => $resultData,
            ];
        });
    }

    /**
     * Lấy kết quả bài luyện tập đã hoàn thành từ CSDL MySQL.
     *
     * @return array<string, mixed>|null
     */
    public function getAttemptResult(int $attemptId, int $userId): ?array
    {
        $attempt = TestAttempt::query()
            ->where('attempt_id', $attemptId)
            ->where('user_id', $userId)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->with([
                'scoreResult',
                'attemptResponses' => fn ($r) => $r->orderBy('sequence_no', 'asc')->with([
                    'question.options',
                    'question.questionSet',
                    'question.section',
                ]),
            ])
            ->first();

        if (! $attempt || $attempt->attemptResponses->isEmpty()) {
            return null;
        }

        $firstQ = $attempt->attemptResponses->first()->question;
        $sectionCode = $firstQ?->section?->code ?? 'PART5';
        $part = match ($sectionCode) {
            'PART6' => 6,
            'PART7' => 7,
            default => 5,
        };

        $correctCount = 0;
        $incorrectCount = 0;
        $unansweredCount = 0;
        $details = [];

        foreach ($attempt->attemptResponses as $idx => $resp) {
            $q = $resp->question;
            if (! $q) {
                continue;
            }

            $userPick = $resp->text_response ? strtoupper(trim((string) $resp->text_response)) : null;
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
                'id' => (int) $q->question_id,
                'part' => 'Part '.$part,
                'part_number' => $part,
                'question_number' => $resp->sequence_no ?: ($idx + 1),
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

        $total = count($details);
        $scorePercent = $total > 0 ? (int) round(($correctCount / $total) * 100) : 0;

        $partNames = [
            5 => 'Part 5 — Hoàn thành câu',
            6 => 'Part 6 — Hoàn thành đoạn văn',
            7 => 'Part 7 — Đọc hiểu văn bản',
        ];

        return [
            'attempt_id' => $attempt->attempt_id,
            'part' => $part,
            'part_name' => $partNames[$part] ?? ('Part '.$part.' — Đọc'),
            'total' => $total,
            'correct_count' => $correctCount,
            'incorrect_count' => $incorrectCount,
            'unanswered_count' => $unansweredCount,
            'score_percent' => $scorePercent,
            'previous_percent' => '40%',
            'submitted_at' => $attempt->submitted_at ? $attempt->submitted_at->format('H:i • d/m/Y') : now()->format('H:i • d/m/Y'),
            'details' => $details,
        ];
    }

    /**
     * Lấy danh sách ID câu hỏi cần luyện lại (chỉ lấy câu sai, không tính câu chưa trả lời).
     * Part 5: lấy câu sai độc lập.
     * Part 6/7: nếu câu trong nhóm sai, lấy nguyên nhóm chứa câu đó theo thứ tự gốc.
     *
     * @return array<int>
     */
    public function getRetryQuestionIds(int $attemptId, int $userId): array
    {
        $attempt = TestAttempt::query()
            ->where('attempt_id', $attemptId)
            ->where('user_id', $userId)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->with([
                'attemptResponses' => fn ($r) => $r->orderBy('sequence_no', 'asc')->with('question.options'),
            ])
            ->first();

        if (! $attempt) {
            return [];
        }

        $firstQ = $attempt->attemptResponses->first()?->question;
        $sectionCode = $firstQ?->section?->code ?? 'PART5';
        $part = match ($sectionCode) {
            'PART6' => 6,
            'PART7' => 7,
            default => 5,
        };

        // Lọc các câu bị sai: có trả lời nhưng khác đáp án đúng
        $wrongResponses = $attempt->attemptResponses->filter(function ($resp) {
            $userPick = $resp->text_response ? strtoupper(trim((string) $resp->text_response)) : null;
            if ($userPick === null) {
                return false; // Chưa trả lời không tính là sai
            }
            $correctOpt = $resp->question?->options?->firstWhere('is_correct', true);
            $correctLabel = $correctOpt ? strtoupper(trim((string) $correctOpt->label)) : null;

            return $userPick !== $correctLabel;
        });

        if ($wrongResponses->isEmpty()) {
            return [];
        }

        if ($part === 5) {
            return $wrongResponses->pluck('question_id')->map(fn ($id) => (int) $id)->values()->all();
        }

        // Part 6 & Part 7: Nếu một câu trong nhóm sai, lấy nguyên nhóm chứa câu đó
        $wrongSetIds = $wrongResponses
            ->pluck('question.question_set_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($wrongSetIds)) {
            return [];
        }

        // Lấy tất cả câu hỏi của các nhóm này theo thứ tự ban đầu trong attempt
        $allRetryQids = [];
        foreach ($attempt->attemptResponses as $resp) {
            $setId = $resp->question?->question_set_id;
            if ($setId && in_array($setId, $wrongSetIds, true)) {
                $allRetryQids[] = (int) $resp->question_id;
            }
        }

        return array_values(array_unique($allRetryQids));
    }

    /**
     * Lấy toàn bộ danh sách question_id của một lượt làm bài hợp lệ theo thứ tự sequence_no.
     * Phục vụ chức năng Ôn lại bài làm của trang Lịch sử.
     *
     * @return array<int>
     */
    public function getAttemptAllQuestionIds(int $attemptId, int $userId): array
    {
        $attempt = TestAttempt::query()
            ->where('attempt_id', $attemptId)
            ->where('user_id', $userId)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->with([
                'attemptResponses' => fn ($r) => $r->orderBy('sequence_no', 'asc'),
            ])
            ->first();

        if (! $attempt || $attempt->attemptResponses->isEmpty()) {
            return [];
        }

        return $attempt->attemptResponses
            ->pluck('question_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Trích xuất và chuẩn hóa đáp án cuối cùng từ sessionData và incomingAnswers.
     *
     * @param  array<string, mixed>  $sessionData
     * @param  array<int|string, string>|null  $incomingAnswers
     * @param  EloquentCollection<int, Question>  $dbQuestions
     * @return array<int, string|null>
     */
    private function extractFinalAnswers(
        array $sessionData,
        ?array $incomingAnswers,
        EloquentCollection $dbQuestions
    ): array {
        $finalAnswers = [];

        // 1. Đáp án từ session answers
        foreach ((array) ($sessionData['answers'] ?? []) as $qid => $val) {
            $label = $this->normalizeAnswer($val);
            if ($label !== null) {
                $finalAnswers[(int) $qid] = $label;
            }
        }

        // 2. Đáp án từ session groups (nếu có nhóm đã hoàn thành)
        if (! empty($sessionData['groups'])) {
            foreach ($sessionData['groups'] as $grp) {
                foreach ((array) ($grp['answers'] ?? []) as $qid => $val) {
                    $label = $this->normalizeAnswer($val);
                    if ($label !== null) {
                        $finalAnswers[(int) $qid] = $label;
                    }
                }
            }
        }

        // 3. Đáp án gửi trực tiếp từ request (ưu tiên cao nhất nếu hợp lệ)
        if (! empty($incomingAnswers)) {
            foreach ($incomingAnswers as $qid => $val) {
                $label = $this->normalizeAnswer($val);
                if ($label !== null) {
                    $finalAnswers[(int) $qid] = $label;
                }
            }
        }

        // Xác thực nhãn đáp án phải tồn tại trong options của câu hỏi
        $validatedAnswers = [];
        foreach ($finalAnswers as $qid => $label) {
            $q = $dbQuestions->get($qid);
            if ($q && $q->relationLoaded('options')) {
                $hasOption = $q->options->contains(
                    fn (AnswerOption $opt) => strtoupper(trim((string) $opt->label)) === $label
                );
                if ($hasOption) {
                    $validatedAnswers[$qid] = $label;
                }
            }
        }

        return $validatedAnswers;
    }

    /**
     * Chuẩn hóa nhãn đáp án (A, B, C, D).
     */
    private function normalizeAnswer(mixed $val): ?string
    {
        if ($val === null) {
            return null;
        }
        $upper = strtoupper(trim((string) $val));

        return in_array($upper, ['A', 'B', 'C', 'D'], true) ? $upper : null;
    }

    /**
     * Dựng nội dung HTML của bài đọc an toàn.
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

        if (! $isAudio && ! empty($qSet->media_url)) {
            $imgHtml = '<div style="margin-top: 16px; text-align: center;"><img src="'.e($qSet->media_url).'" alt="Reading Material" style="max-width: 100%; height: auto; border-radius: 8px;" /></div>';
            $passageHtml = $passageHtml ? ($passageHtml.$imgHtml) : $imgHtml;
        }

        return $passageHtml;
    }
}
