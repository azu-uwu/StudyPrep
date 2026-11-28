<?php

namespace App\Services;

use App\Models\ExamSection;
use App\Models\Question;
use App\Models\TestAttempt;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HistoryService
{
    /**
     * Phân tích và trả về phạm vi ngày của bộ lọc cùng kỳ đối chiếu liền trước.
     *
     * @return array{start: ?Carbon, end: Carbon, prev_start: ?Carbon, prev_end: ?Carbon, label: string}
     */
    public function parseDateRange(string $range): array
    {
        $now = now();

        return match ($range) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'prev_start' => $now->copy()->subDay()->startOfDay(),
                'prev_end' => $now->copy()->subDay()->endOfDay(),
                'label' => 'Hôm nay',
            ],
            '7days' => [
                'start' => $now->copy()->subDays(7),
                'end' => $now,
                'prev_start' => $now->copy()->subDays(14),
                'prev_end' => $now->copy()->subDays(7),
                'label' => '7 ngày qua',
            ],
            '90days' => [
                'start' => $now->copy()->subDays(90),
                'end' => $now,
                'prev_start' => $now->copy()->subDays(180),
                'prev_end' => $now->copy()->subDays(90),
                'label' => '90 ngày qua',
            ],
            'all' => [
                'start' => null,
                'end' => $now,
                'prev_start' => null,
                'prev_end' => null,
                'label' => 'Tất cả thời gian',
            ],
            default => [ // '30days'
                'start' => $now->copy()->subDays(30),
                'end' => $now,
                'prev_start' => $now->copy()->subDays(60),
                'prev_end' => $now->copy()->subDays(30),
                'label' => '30 ngày qua',
            ],
        };
    }

    /**
     * Thống kê 4 thẻ số liệu tổng hợp của người dùng.
     *
     * @return array{
     *     activity_count: int,
     *     accuracy_percent: ?int,
     *     accuracy_delta: ?float,
     *     accuracy_delta_direction: ?string,
     *     study_time_seconds: int,
     *     study_time_text: string,
     *     accumulated_wrong_count: int,
     *     can_review_weak_points: bool
     * }
     */
    public function getStatistics(int $userId, string $range): array
    {
        $dateRange = $this->parseDateRange($range);

        // 1. Hoạt động ghi nhận
        $activityQuery = TestAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED']);

        if ($dateRange['start'] !== null) {
            $activityQuery->where('started_at', '>=', $dateRange['start']);
        }
        $activityCount = $activityQuery->count();

        // 2. Tỉ lệ đúng trung bình trắc nghiệm
        $currentAccuracy = $this->calculateAccuracyForWindow($userId, $dateRange['start'], $dateRange['end']);
        $accuracyPercent = $currentAccuracy !== null ? (int) round($currentAccuracy) : null;

        $accuracyDelta = null;
        $accuracyDeltaDirection = null;

        if ($dateRange['prev_start'] !== null && $currentAccuracy !== null) {
            $prevAccuracy = $this->calculateAccuracyForWindow($userId, $dateRange['prev_start'], $dateRange['prev_end']);
            if ($prevAccuracy !== null) {
                $accuracyDelta = round($currentAccuracy - $prevAccuracy, 1);
                $accuracyDeltaDirection = $accuracyDelta > 0 ? 'up' : ($accuracyDelta < 0 ? 'down' : 'flat');
            }
        }

        // 3. Thời gian học tập
        $timeQuery = TestAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED']);

        if ($dateRange['start'] !== null) {
            $timeQuery->where('started_at', '>=', $dateRange['start']);
        }

        $attempts = $timeQuery->get(['started_at', 'ended_at', 'submitted_at']);
        $totalSeconds = 0;

        foreach ($attempts as $attempt) {
            $end = $attempt->ended_at ?? $attempt->submitted_at;
            if ($attempt->started_at && $end) {
                $diff = $attempt->started_at->diffInSeconds($end, false);
                if ($diff > 0) {
                    $totalSeconds += $diff;
                }
            }
        }

        if ($totalSeconds <= 0) {
            $studyTimeText = '0 phút';
        } else {
            $hours = intdiv($totalSeconds, 3600);
            $minutes = intdiv($totalSeconds % 3600, 60);
            if ($hours > 0) {
                $studyTimeText = "{$hours}h {$minutes}m";
            } else {
                $studyTimeText = "{$minutes}m";
            }
        }

        // 4. Câu sai tích lũy trắc nghiệm (toàn bộ lịch sử luyện tập)
        $accumulatedWrongCount = $this->countAccumulatedWrongQuestions($userId);

        return [
            'activity_count' => $activityCount,
            'accuracy_percent' => $accuracyPercent,
            'accuracy_delta' => $accuracyDelta,
            'accuracy_delta_direction' => $accuracyDeltaDirection,
            'study_time_seconds' => $totalSeconds,
            'study_time_text' => $studyTimeText,
            'accumulated_wrong_count' => $accumulatedWrongCount,
            'can_review_weak_points' => $accumulatedWrongCount > 0,
        ];
    }

    /**
     * Tính tỉ lệ đúng trung bình (MCQ) trong một khoảng thời gian.
     */
    private function calculateAccuracyForWindow(int $userId, ?Carbon $start, ?Carbon $end): ?float
    {
        $query = DB::table('attempt_response')
            ->join('attempts', 'attempt_response.attempt_id', '=', 'attempts.attempt_id')
            ->join('questions', 'attempt_response.question_id', '=', 'questions.question_id')
            ->join('question_type', 'questions.question_type_id', '=', 'question_type.question_type_id')
            ->leftJoin('answer_option', function ($join): void {
                $join->on('questions.question_id', '=', 'answer_option.question_id')
                    ->where('answer_option.is_correct', '=', 1);
            })
            ->where('attempts.user_id', $userId)
            ->whereIn('attempts.status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->where('question_type.response_mode', 'SINGLE_CHOICE');

        if ($start !== null) {
            $query->where('attempts.started_at', '>=', $start);
        }
        if ($end !== null) {
            $query->where('attempts.started_at', '<=', $end);
        }

        $stats = $query->selectRaw('
            COUNT(attempt_response.response_id) as total_questions,
            SUM(CASE
                WHEN attempt_response.text_response IS NOT NULL
                     AND UPPER(TRIM(attempt_response.text_response)) = UPPER(TRIM(answer_option.label))
                THEN 1
                ELSE 0
            END) as correct_questions
        ')->first();

        $total = (int) ($stats->total_questions ?? 0);
        $correct = (int) ($stats->correct_questions ?? 0);

        if ($total === 0) {
            return null;
        }

        return ($correct / $total) * 100;
    }

    /**
     * Đếm tổng số câu hỏi trắc nghiệm đã từng làm sai thuộc Listening & Reading (distinct question_id).
     */
    public function countAccumulatedWrongQuestions(int $userId, ?int $sectionId = null, ?string $sectionCode = null): int
    {
        $query = DB::table('attempt_response')
            ->join('attempts', 'attempt_response.attempt_id', '=', 'attempts.attempt_id')
            ->join('questions', 'attempt_response.question_id', '=', 'questions.question_id')
            ->join('question_type', 'questions.question_type_id', '=', 'question_type.question_type_id')
            ->leftJoin('exam_section', 'questions.section_id', '=', 'exam_section.section_id')
            ->leftJoin('skills', 'exam_section.skill_id', '=', 'skills.skill_id')
            ->leftJoin('answer_option', function ($join): void {
                $join->on('questions.question_id', '=', 'answer_option.question_id')
                    ->where('answer_option.is_correct', '=', 1);
            })
            ->where('attempts.user_id', $userId)
            ->whereIn('attempts.status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->where('attempts.session_type', TestAttempt::SESSION_TYPE_PRACTICE)
            ->where('question_type.response_mode', 'SINGLE_CHOICE')
            ->where(function ($q): void {
                $q->whereNull('skills.code')
                    ->orWhereIn('skills.code', ['LC', 'RC']);
            })
            ->whereNotNull('attempt_response.text_response')
            ->whereRaw("TRIM(attempt_response.text_response) != ''")
            ->where(function ($sub): void {
                $sub->whereNull('answer_option.label')
                    ->orWhereRaw('UPPER(TRIM(attempt_response.text_response)) != UPPER(TRIM(answer_option.label))');
            });

        if ($sectionId !== null) {
            $query->where('questions.section_id', $sectionId);
        }

        if ($sectionCode !== null) {
            $query->where('exam_section.code', $sectionCode);
        }

        return $query->distinct('attempt_response.question_id')
            ->count('attempt_response.question_id');
    }

    /**
     * Lấy danh sách ID câu hỏi làm sai tích lũy theo người dùng và (tùy chọn) Part (Listening & Reading).
     *
     * @return array<int>
     */
    public function getAccumulatedWrongQuestionIds(int $userId, ?int $sectionId = null, ?string $sectionCode = null): array
    {
        $query = DB::table('attempt_response')
            ->join('attempts', 'attempt_response.attempt_id', '=', 'attempts.attempt_id')
            ->join('questions', 'attempt_response.question_id', '=', 'questions.question_id')
            ->join('question_type', 'questions.question_type_id', '=', 'question_type.question_type_id')
            ->leftJoin('exam_section', 'questions.section_id', '=', 'exam_section.section_id')
            ->leftJoin('skills', 'exam_section.skill_id', '=', 'skills.skill_id')
            ->leftJoin('answer_option', function ($join): void {
                $join->on('questions.question_id', '=', 'answer_option.question_id')
                    ->where('answer_option.is_correct', '=', 1);
            })
            ->where('attempts.user_id', $userId)
            ->whereIn('attempts.status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->where('attempts.session_type', TestAttempt::SESSION_TYPE_PRACTICE)
            ->where('question_type.response_mode', 'SINGLE_CHOICE')
            ->where(function ($q): void {
                $q->whereNull('skills.code')
                    ->orWhereIn('skills.code', ['LC', 'RC']);
            })
            ->whereNotNull('attempt_response.text_response')
            ->whereRaw("TRIM(attempt_response.text_response) != ''")
            ->where(function ($sub): void {
                $sub->whereNull('answer_option.label')
                    ->orWhereRaw('UPPER(TRIM(attempt_response.text_response)) != UPPER(TRIM(answer_option.label))');
            });

        if ($sectionId !== null) {
            $query->where('questions.section_id', $sectionId);
        }

        if ($sectionCode !== null) {
            $query->where('exam_section.code', $sectionCode);
        }

        return $query->distinct()
            ->pluck('attempt_response.question_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Lấy danh sách câu hỏi làm sai tích lũy (Listening & Reading).
     *
     * @return Collection<int, object>
     */
    public function getAccumulatedWrongQuestions(int $userId, ?int $sectionId = null, ?string $sectionCode = null): Collection
    {
        $query = DB::table('attempt_response')
            ->join('attempts', 'attempt_response.attempt_id', '=', 'attempts.attempt_id')
            ->join('questions', 'attempt_response.question_id', '=', 'questions.question_id')
            ->join('question_type', 'questions.question_type_id', '=', 'question_type.question_type_id')
            ->leftJoin('exam_section', 'questions.section_id', '=', 'exam_section.section_id')
            ->leftJoin('skills', 'exam_section.skill_id', '=', 'skills.skill_id')
            ->leftJoin('answer_option', function ($join): void {
                $join->on('questions.question_id', '=', 'answer_option.question_id')
                    ->where('answer_option.is_correct', '=', 1);
            })
            ->where('attempts.user_id', $userId)
            ->whereIn('attempts.status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->where('attempts.session_type', TestAttempt::SESSION_TYPE_PRACTICE)
            ->where('question_type.response_mode', 'SINGLE_CHOICE')
            ->where(function ($q): void {
                $q->whereNull('skills.code')
                    ->orWhereIn('skills.code', ['LC', 'RC']);
            })
            ->whereNotNull('attempt_response.text_response')
            ->whereRaw("TRIM(attempt_response.text_response) != ''")
            ->where(function ($sub): void {
                $sub->whereNull('answer_option.label')
                    ->orWhereRaw('UPPER(TRIM(attempt_response.text_response)) != UPPER(TRIM(answer_option.label))');
            });

        if ($sectionId !== null) {
            $query->where('questions.section_id', $sectionId);
        }

        if ($sectionCode !== null) {
            $query->where('exam_section.code', $sectionCode);
        }

        return $query->select([
            'questions.question_id',
            'questions.content',
            'questions.explanation',
            'exam_section.section_id',
            'exam_section.code as section_code',
            'exam_section.name as section_name',
            'exam_section.sequence_no',
            'skills.code as skill_code',
            'skills.skill_name',
            DB::raw('MAX(attempt_response.submitted_at) as last_attempted_at'),
        ])
            ->groupBy(
                'questions.question_id',
                'questions.content',
                'questions.explanation',
                'exam_section.section_id',
                'exam_section.code',
                'exam_section.name',
                'exam_section.sequence_no',
                'skills.code',
                'skills.skill_name'
            )
            ->orderBy('last_attempted_at', 'desc')
            ->get();
    }

    /**
     * Tổng hợp danh sách Part có câu sai tích lũy của người dùng (tối đa 7 Part Listening & Reading).
     * Chỉ hiển thị các Part có câu sai hợp lệ trong CSDL.
     *
     * @return array<int, array{
     *     section_id: int,
     *     section_code: string,
     *     section_name: string,
     *     sequence_no: int,
     *     skill_id: int,
     *     skill_code: string,
     *     skill_name: string,
     *     skill_slug: string,
     *     part_number: int,
     *     display_title: string,
     *     section_key: string,
     *     wrong_count: int,
     *     icon_svg: string,
     *     icon_bg: string,
     *     redo_url: string
     * }>
     */
    public function getAccumulatedWrongParts(int $userId): array
    {
        $rawCounts = DB::table('attempt_response')
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
            ->where('attempts.session_type', TestAttempt::SESSION_TYPE_PRACTICE)
            ->where('question_type.response_mode', 'SINGLE_CHOICE')
            ->whereIn('skills.code', ['LC', 'RC'])
            ->whereNotNull('attempt_response.text_response')
            ->whereRaw("TRIM(attempt_response.text_response) != ''")
            ->where(function ($sub): void {
                $sub->whereNull('answer_option.label')
                    ->orWhereRaw('UPPER(TRIM(attempt_response.text_response)) != UPPER(TRIM(answer_option.label))');
            })
            ->select([
                'exam_section.section_id',
                'exam_section.code as section_code',
                'exam_section.name as section_name',
                'exam_section.sequence_no',
                'skills.skill_id',
                'skills.code as skill_code',
                'skills.skill_name',
                DB::raw('COUNT(DISTINCT attempt_response.question_id) as wrong_count'),
            ])
            ->groupBy(
                'exam_section.section_id',
                'exam_section.code',
                'exam_section.name',
                'exam_section.sequence_no',
                'skills.skill_id',
                'skills.code',
                'skills.skill_name'
            )
            ->orderByRaw("
                CASE skills.code
                    WHEN 'LC' THEN 1
                    WHEN 'RC' THEN 2
                    ELSE 3
                END ASC
            ")
            ->orderBy('exam_section.sequence_no', 'asc')
            ->limit(7)
            ->get();

        $parts = [];
        foreach ($rawCounts as $row) {
            $wrongCount = (int) $row->wrong_count;
            if ($wrongCount <= 0) {
                continue;
            }

            $partNum = $this->extractPartNumber((string) $row->section_code, (int) $row->sequence_no);
            $skillCode = strtoupper((string) $row->skill_code);
            $displayTitle = $this->formatPartDisplayTitle((string) $row->section_code, (string) $row->section_name, $partNum, $skillCode);
            $skillSlug = match ($skillCode) {
                'LC' => 'nghe',
                'RC' => 'doc',
                'SPK' => 'noi',
                'WRT' => 'viet',
                default => 'doc',
            };

            $redoUrl = match ($skillSlug) {
                'nghe' => route('luyen-tap.nghe'),
                'noi' => url('/luyen-tap/noi'),
                'viet' => url('/luyen-tap/viet'),
                default => url('/luyen-tap/doc'),
            };

            $icon = $this->getPartIcon((string) $row->section_code, $partNum, $skillCode);

            $parts[] = [
                'section_id' => (int) $row->section_id,
                'section_code' => (string) $row->section_code,
                'section_name' => (string) $row->section_name,
                'sequence_no' => (int) $row->sequence_no,
                'skill_id' => (int) $row->skill_id,
                'skill_code' => $skillCode,
                'skill_name' => (string) $row->skill_name,
                'skill_slug' => $skillSlug,
                'part_number' => $partNum,
                'display_title' => $displayTitle,
                'section_key' => (string) $row->section_code,
                'wrong_count' => $wrongCount,
                'icon_svg' => $icon['svg'],
                'icon_bg' => $icon['bg'],
                'redo_url' => $redoUrl,
            ];
        }

        return $parts;
    }

    /**
     * Lấy toàn bộ câu hỏi sai của từng Part kèm lời giải chi tiết và đáp án người dùng.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAccumulatedWrongPartsWithQuestions(int $userId): array
    {
        $parts = $this->getAccumulatedWrongParts($userId);
        $result = [];

        foreach ($parts as $p) {
            $sectionId = $p['section_id'];
            $sectionCode = $p['section_code'];
            $sectionKey = $p['section_key'];

            // 1. Lấy response_id gần nhất của từng question_id sai
            $latestResponses = DB::table('attempt_response')
                ->join('attempts', 'attempt_response.attempt_id', '=', 'attempts.attempt_id')
                ->join('questions', 'attempt_response.question_id', '=', 'questions.question_id')
                ->join('question_type', 'questions.question_type_id', '=', 'question_type.question_type_id')
                ->leftJoin('answer_option', function ($join): void {
                    $join->on('questions.question_id', '=', 'answer_option.question_id')
                        ->where('answer_option.is_correct', '=', 1);
                })
                ->where('attempts.user_id', $userId)
                ->whereIn('attempts.status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
                ->where('attempts.session_type', TestAttempt::SESSION_TYPE_PRACTICE)
                ->where('question_type.response_mode', 'SINGLE_CHOICE')
                ->where('questions.section_id', $sectionId)
                ->whereNotNull('attempt_response.text_response')
                ->whereRaw("TRIM(attempt_response.text_response) != ''")
                ->where(function ($sub): void {
                    $sub->whereNull('answer_option.label')
                        ->orWhereRaw('UPPER(TRIM(attempt_response.text_response)) != UPPER(TRIM(answer_option.label))');
                })
                ->select([
                    'attempt_response.question_id',
                    'attempt_response.text_response',
                    'attempt_response.submitted_at',
                    'attempt_response.response_id',
                ])
                ->orderBy('attempt_response.submitted_at', 'desc')
                ->orderBy('attempt_response.response_id', 'desc')
                ->get()
                ->unique('question_id')
                ->keyBy('question_id');

            if ($latestResponses->isEmpty()) {
                continue;
            }

            $questionIds = $latestResponses->keys()->all();

            $loadedQuestions = Question::query()
                ->whereIn('question_id', $questionIds)
                ->with([
                    'options' => fn ($o) => $o->orderBy('label'),
                    'questionSet',
                    'section.skill',
                ])
                ->get()
                ->sortBy(fn ($q) => $latestResponses[$q->question_id]->response_id ?? 0)
                ->values();

            $formattedQuestions = [];
            foreach ($loadedQuestions as $idx => $q) {
                $optionsMap = [];
                $correctLabel = 'A';
                foreach ($q->options as $opt) {
                    $lbl = strtoupper(trim((string) $opt->label));
                    $optionsMap[$lbl] = (string) $opt->content;
                    if ($opt->is_correct) {
                        $correctLabel = $lbl;
                    }
                }

                $userPick = strtoupper(trim((string) ($latestResponses[$q->question_id]->text_response ?? '')));
                $qSet = $q->questionSet;

                $imageUrl = null;
                $audioUrl = null;
                $transcript = null;

                if ($qSet) {
                    $mediaUrl = $qSet->resolved_media_url;
                    $isImage = $mediaUrl !== null && (
                        strtoupper((string) $qSet->media_type) === 'IMAGE'
                        || preg_match('/\.(png|jpe?g|gif|webp|bmp)$/i', $mediaUrl) === 1
                    );
                    if ($isImage) {
                        $imageUrl = $mediaUrl;
                    }
                    $audioUrl = $qSet->resolved_audio_url;
                    $transcript = trim((string) ($qSet->transcript ?? ''));
                }

                $formattedQuestions[] = [
                    'id' => (int) $q->question_id,
                    'sequence_no' => $idx + 1,
                    'content' => (string) $q->content,
                    'explanation' => trim((string) ($q->explanation ?? '')),
                    'user_answer' => $userPick,
                    'correct_answer' => $correctLabel,
                    'options' => $optionsMap,
                    'stimulus_text' => $qSet?->stimulus_text,
                    'image_url' => $imageUrl,
                    'audio_url' => $audioUrl,
                    'transcript' => $transcript,
                    'status' => 'incorrect',
                ];
            }

            $headerTitle = match ($p['skill_code']) {
                'LC' => 'Part '.$p['part_number'].' — Nghe',
                'RC' => 'Part '.$p['part_number'].' — Đọc',
                'SPK' => 'Part '.$p['part_number'].' — Nói',
                'WRT' => 'Part '.$p['part_number'].' — Viết',
                default => 'Part '.$p['part_number'],
            };

            $result[$sectionKey] = [
                'section_id' => $p['section_id'],
                'section_code' => $p['section_code'],
                'part_number' => $p['part_number'],
                'header_title' => $headerTitle,
                'display_title' => $p['display_title'],
                'skill_name' => $p['skill_name'],
                'skill_slug' => $p['skill_slug'],
                'count' => count($formattedQuestions),
                'redo_url' => $p['redo_url'],
                'questions' => $formattedQuestions,
            ];

            // Đồng thời map theo số part (1..7) nếu là code dạng PART1..7 để tiện truy xuất
            if (is_numeric($p['part_number']) && ! isset($result[(string) $p['part_number']])) {
                $result[(string) $p['part_number']] = $result[$sectionKey];
            }
        }

        return $result;
    }

    /**
     * Trích xuất số Part từ code hoặc sequence_no.
     */
    private function extractPartNumber(string $sectionCode, int $sequenceNo): int
    {
        if (preg_match('/(\d+)/', $sectionCode, $matches) === 1) {
            return (int) $matches[1];
        }

        return max(1, $sequenceNo);
    }

    /**
     * Chuẩn hóa tiêu đề hiển thị cho Part.
     */
    private function formatPartDisplayTitle(string $sectionCode, string $sectionName, int $partNum, string $skillCode): string
    {
        $shortTitles = [
            'PART1' => 'Part 1: Mô tả hình',
            'PART2' => 'Part 2: Hỏi & Đáp',
            'PART3' => 'Part 3: Hội thoại ngắn',
            'PART4' => 'Part 4: Bài nói chuyện ngắn',
            'PART5' => 'Part 5: Hoàn thành câu',
            'PART6' => 'Part 6: Hoàn thành đoạn văn',
            'PART7' => 'Part 7: Đọc hiểu đoạn văn',
        ];

        if (isset($shortTitles[strtoupper($sectionCode)])) {
            return $shortTitles[strtoupper($sectionCode)];
        }

        if (preg_match('/\(([^()]+)\)/', $sectionName, $m) === 1) {
            $vnName = trim($m[1]);

            return match ($skillCode) {
                'SPK' => "Speaking - Part {$partNum}: {$vnName}",
                'WRT' => "Writing - Part {$partNum}: {$vnName}",
                default => "Part {$partNum}: {$vnName}",
            };
        }

        return $sectionName;
    }

    /**
     * Lấy icon SVG và màu nền đại diện cho Part khớp thiết kế trang Cải thiện.
     *
     * @return array{svg: string, bg: string}
     */
    private function getPartIcon(string $sectionCode, int $partNum, string $skillCode): array
    {
        $upperCode = strtoupper($sectionCode);

        if ($upperCode === 'PART1') {
            return [
                'bg' => '#E0F2FE',
                'svg' => '<svg width="40" height="40" viewBox="0 0 34 34" fill="none"><path d="M6 25L14 15L19 21L22 17L27 25H6Z" fill="#10B981"/><circle cx="21" cy="11" r="3" fill="#F59E0B"/><g transform="translate(13, 7) rotate(32)"><rect x="0" y="0" width="4" height="14" rx="1.5" fill="#F59E0B"/><path d="M0 14L2 18L4 14Z" fill="#3B82F6"/></g></svg>',
            ];
        }

        if ($upperCode === 'PART2') {
            return [
                'bg' => '#EFF6FF',
                'svg' => '<svg width="40" height="40" viewBox="0 0 34 34" fill="none"><rect x="7" y="9" width="14" height="11" rx="4" fill="#38BDF8"/><path d="M10 20L8 23V20H10Z" fill="#38BDF8"/><line x1="10" y1="13" x2="18" y2="13" stroke="white" stroke-width="1.5" stroke-linecap="round"/><line x1="10" y1="16" x2="15" y2="16" stroke="white" stroke-width="1.5" stroke-linecap="round"/><rect x="15" y="14" width="13" height="11" rx="4" fill="#EF4444"/><path d="M25 25L23 27V25H25Z" fill="#EF4444"/><text x="21.5" y="22.5" font-size="8" font-weight="800" fill="white" text-anchor="middle">A</text></svg>',
            ];
        }

        if ($upperCode === 'PART3') {
            return [
                'bg' => '#FEF3C7',
                'svg' => '<svg width="40" height="40" viewBox="0 0 34 34" fill="none"><rect x="8" y="7" width="16" height="11" rx="4" fill="#F59E0B"/><path d="M11 18L9 21V18H11Z" fill="#F59E0B"/><line x1="11" y1="11" x2="21" y2="11" stroke="white" stroke-width="1.5" stroke-linecap="round"/><line x1="11" y1="14" x2="17" y2="14" stroke="white" stroke-width="1.5" stroke-linecap="round"/><rect x="13" y="15" width="15" height="11" rx="4" fill="#0D9488"/><path d="M25 26L23 28V26H25Z" fill="#0D9488"/><line x1="16" y1="19" x2="25" y2="19" stroke="white" stroke-width="1.5" stroke-linecap="round"/><line x1="16" y1="22" x2="22" y2="22" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg>',
            ];
        }

        if ($upperCode === 'PART4') {
            return [
                'bg' => '#FDF2F8',
                'svg' => '<svg width="40" height="40" viewBox="0 0 34 34" fill="none"><circle cx="17" cy="11" r="3.5" fill="#FB7185"/><path d="M12 18C12 16 14 15 17 15C20 15 22 16 22 18V20H12V18Z" fill="#FDA4AF"/><path d="M13 20H21L20 28H14L13 20Z" fill="#94A3B8"/><rect x="11" y="19" width="12" height="2" rx="1" fill="#64748B"/><line x1="19" y1="16" x2="18" y2="19" stroke="#1E293B" stroke-width="1.2"/><circle cx="19" cy="15.5" r="1" fill="#1E293B"/></svg>',
            ];
        }

        if ($upperCode === 'PART5') {
            return [
                'bg' => '#EEF2FF',
                'svg' => '<svg width="40" height="40" viewBox="0 0 48 48" fill="none"><rect x="8" y="6" width="26" height="36" rx="5" fill="#FFFFFF" stroke="#1E293B" stroke-width="2.5"/><rect x="12" y="11" width="8" height="8" rx="2" fill="#38BDF8" stroke="#0284C7" stroke-width="1.2"/><path d="M14 15l2 2 3-3" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><rect x="12" y="22" width="8" height="8" rx="2" fill="#38BDF8" stroke="#0284C7" stroke-width="1.2"/><path d="M14 26l2 2 3-3" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><g transform="translate(32, 22) rotate(42)"><rect x="-18" y="-4" width="22" height="8" rx="1" fill="#F59E0B" stroke="#1E293B" stroke-width="1.8"/><line x1="-18" y1="0" x2="4" y2="0" stroke="#D97706" stroke-width="1.2"/><path d="M-18 -4 L-25 0 L-18 4 Z" fill="#FDE68A" stroke="#1E293B" stroke-width="1.8"/><rect x="4" y="-4" width="5" height="8" rx="1" fill="#EF4444" stroke="#1E293B" stroke-width="1.8"/></g></svg>',
            ];
        }

        if ($upperCode === 'PART6') {
            return [
                'bg' => '#EEF2FF',
                'svg' => '<svg width="40" height="40" viewBox="0 0 48 48" fill="none"><rect x="8" y="6" width="24" height="32" rx="4" fill="#EEF2FF" stroke="#4F46E5" stroke-width="2"/><rect x="16" y="12" width="24" height="30" rx="4" fill="#FFFFFF" stroke="#4F46E5" stroke-width="2"/><line x1="22" y1="18" x2="34" y2="18" stroke="#4F46E5" stroke-width="1.8" stroke-linecap="round"/><line x1="22" y1="24" x2="32" y2="24" stroke="#4F46E5" stroke-width="1.8" stroke-linecap="round"/><line x1="22" y1="30" x2="34" y2="30" stroke="#4F46E5" stroke-width="1.8" stroke-linecap="round"/></svg>',
            ];
        }

        if ($upperCode === 'PART7') {
            return [
                'bg' => '#EFF6FF',
                'svg' => '<svg width="40" height="40" viewBox="0 0 48 48" fill="none"><rect x="12" y="6" width="24" height="36" rx="4" fill="#3B82F6"/><rect x="12" y="6" width="5" height="36" rx="2" fill="#2563EB"/><line x1="32" y1="8" x2="32" y2="38" stroke="#93C5FD" stroke-width="1.2"/><path d="M18 36v8l3-2 3 2v-8z" fill="#BFDBFE"/></svg>',
            ];
        }

        if ($skillCode === 'SPK') {
            return [
                'bg' => '#FEF2F2',
                'svg' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/></svg>',
            ];
        }

        if ($skillCode === 'WRT') {
            return [
                'bg' => '#F0FDF4',
                'svg' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.83 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3Z"/></svg>',
            ];
        }

        return [
            'bg' => '#F1F5F9',
            'svg' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>',
        ];
    }

    /**
     * Đếm số lượng phiên trên 3 tab (Tất cả, Bài học, Luyện đề) theo bộ lọc hiện tại.
     *
     * @return array{all: int, lesson: int, exam: int}
     */
    public function getTypeCounts(int $userId, string $range, ?string $partFilter = null, ?string $search = null): array
    {
        $dateRange = $this->parseDateRange($range);

        $baseQuery = TestAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED']);

        if ($dateRange['start'] !== null) {
            $baseQuery->where('started_at', '>=', $dateRange['start']);
        }

        if ($partFilter && $partFilter !== 'all') {
            $baseQuery->whereHas('attemptResponses.question.section', function ($s) use ($partFilter): void {
                $s->where('code', $partFilter);
            });
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $baseQuery->where(function ($q) use ($term): void {
                $q->whereHas('testForm', fn ($tf) => $tf->where('title', 'like', "%{$term}%"))
                    ->orWhereHas('attemptResponses.question.section', function ($s) use ($term): void {
                        $s->where('name', 'like', "%{$term}%")
                            ->orWhere('code', 'like', "%{$term}%")
                            ->orWhereHas('skill', function ($sk) use ($term): void {
                                $sk->where('skill_name', 'like', "%{$term}%")
                                    ->orWhere('code', 'like', "%{$term}%");
                            });
                    });
            });
        }

        $allCount = (clone $baseQuery)->count();

        $lessonCount = (clone $baseQuery)
            ->where('session_type', TestAttempt::SESSION_TYPE_PRACTICE)
            ->count();

        $examCount = (clone $baseQuery)
            ->whereIn('session_type', [
                TestAttempt::SESSION_TYPE_PRACTICE_PART,
                TestAttempt::SESSION_TYPE_FULL_TEST,
                TestAttempt::SESSION_TYPE_EXAM,
            ])
            ->count();

        return [
            'all' => $allCount,
            'lesson' => $lessonCount,
            'exam' => $examCount,
        ];
    }

    /**
     * Lấy danh sách Part thực tế từ exam_section và skills.
     *
     * @return array<int, array{code: string, name: string, skill_name: string}>
     */
    public function getAvailableParts(): array
    {
        return ExamSection::query()
            ->with('skill')
            ->orderBy('skill_id')
            ->orderBy('sequence_no')
            ->get()
            ->map(fn ($section) => [
                'code' => (string) $section->code,
                'name' => (string) $section->name,
                'skill_name' => (string) ($section->skill?->skill_name ?? ''),
            ])
            ->values()
            ->all();
    }

    /**
     * Truy vấn danh sách lịch sử phân trang (10 bản ghi/trang).
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedHistory(int $userId, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $range = (string) ($filters['range'] ?? '30days');
        $type = (string) ($filters['type'] ?? 'all');
        $part = (string) ($filters['part'] ?? 'all');
        $sort = (string) ($filters['sort'] ?? 'newest');
        $search = isset($filters['q']) ? trim((string) $filters['q']) : null;

        $dateRange = $this->parseDateRange($range);

        $query = TestAttempt::query()
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
            ]);

        // 1. Lọc theo thời gian
        if ($dateRange['start'] !== null) {
            $query->where('started_at', '>=', $dateRange['start']);
        }

        // 2. Lọc theo loại bài
        if ($type === 'lesson') {
            $query->where('session_type', TestAttempt::SESSION_TYPE_PRACTICE);
        } elseif ($type === 'exam') {
            $query->whereIn('session_type', [
                TestAttempt::SESSION_TYPE_PRACTICE_PART,
                TestAttempt::SESSION_TYPE_FULL_TEST,
                TestAttempt::SESSION_TYPE_EXAM,
            ]);
        }

        // 3. Lọc theo Part
        if ($part !== 'all' && $part !== '') {
            $query->whereHas('attemptResponses.question.section', function ($s) use ($part): void {
                $s->where('code', $part);
            });
        }

        // 4. Tìm kiếm từ khóa
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->whereHas('testForm', fn ($tf) => $tf->where('title', 'like', "%{$search}%"))
                    ->orWhereHas('attemptResponses.question.section', function ($s) use ($search): void {
                        $s->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhereHas('skill', function ($sk) use ($search): void {
                                $sk->where('skill_name', 'like', "%{$search}%")
                                    ->orWhere('code', 'like', "%{$search}%");
                            });
                    });
            });
        }

        // 5. Sắp xếp
        if ($sort === 'oldest') {
            $query->orderBy('started_at', 'asc')->orderBy('attempt_id', 'asc');
        } elseif ($sort === 'score_desc' || $sort === 'score_asc') {
            $direction = $sort === 'score_desc' ? 'desc' : 'asc';
            $query->leftJoin('score_result', 'attempts.attempt_id', '=', 'score_result.attempt_id')
                ->select('attempts.*')
                ->orderBy('score_result.total_score', $direction)
                ->orderBy('attempts.started_at', 'desc')
                ->orderBy('attempts.attempt_id', 'desc');
        } else { // 'newest'
            $query->orderBy('started_at', 'desc')->orderBy('attempt_id', 'desc');
        }

        $paginator = $query->paginate($perPage)->withQueryString();

        // 6. Transform items for clean UI rendering
        $paginator->getCollection()->transform(function (TestAttempt $attempt) use ($userId): array {
            return $this->formatAttemptCard($attempt, $userId);
        });

        return $paginator;
    }

    /**
     * Chuẩn hóa dữ liệu một card lịch sử cho giao diện.
     *
     * @return array<string, mixed>
     */
    public function formatAttemptCard(TestAttempt $attempt, int $userId): array
    {
        $isExam = in_array($attempt->session_type, [
            TestAttempt::SESSION_TYPE_PRACTICE_PART,
            TestAttempt::SESSION_TYPE_FULL_TEST,
            TestAttempt::SESSION_TYPE_EXAM,
        ], true);

        $firstResponse = $attempt->attemptResponses->first();
        $firstQuestion = $firstResponse?->question;
        $section = $firstQuestion?->section;
        $skill = $section?->skill;

        $sectionCode = $section?->code ? (string) $section->code : null;
        $skillCode = $skill?->code ? (string) $skill->code : null;

        // Tên hiển thị & phân loại nhãn
        if ($isExam) {
            $title = $attempt->testForm?->title ?: 'Đề thi thử TOEIC';
            $iconClass = 'ls-activity-icon--exam';
            $type = 'exam';
            $modeLabel = $attempt->session_type === TestAttempt::SESSION_TYPE_PRACTICE_PART ? 'Luyện từng phần' : 'Full Test';
        } else {
            $title = $section?->name ?: ('Luyện tập '.($skill?->skill_name ?? 'kỹ năng'));
            $partLower = strtolower($sectionCode ?? 'part5');
            $iconClass = 'ls-activity-icon--'.$partLower;
            $type = 'lesson';
            $modeLabel = null;
        }

        // Tính câu đúng, câu sai, chưa trả lời
        $totalQuestions = $attempt->attemptResponses->count();
        $correctCount = 0;
        $incorrectCount = 0;
        $unansweredCount = 0;
        $hasMcq = false;

        foreach ($attempt->attemptResponses as $response) {
            $q = $response->question;
            if (! $q) {
                continue;
            }

            $isSingleChoice = ($q->questionType?->response_mode === 'SINGLE_CHOICE');
            if ($isSingleChoice) {
                $hasMcq = true;
                $userPick = $response->text_response ? strtoupper(trim((string) $response->text_response)) : null;
                $correctOpt = $q->options->firstWhere('is_correct', true);
                $correctLabel = $correctOpt ? strtoupper(trim((string) $correctOpt->label)) : null;

                if ($userPick === null || $userPick === '') {
                    $unansweredCount++;
                } elseif ($correctLabel !== null && $userPick === $correctLabel) {
                    $correctCount++;
                } else {
                    $incorrectCount++;
                }
            } else {
                if ($response->score !== null && (float) $response->score > 0) {
                    $correctCount++;
                } else {
                    $unansweredCount++;
                }
            }
        }

        // Tỉ lệ đúng / Điểm số
        $accuracyPercent = ($hasMcq && $totalQuestions > 0) ? (int) round(($correctCount / $totalQuestions) * 100) : null;

        if ($isExam) {
            $scoreVal = $attempt->scoreResult?->total_score;
            $scoreText = $scoreVal !== null ? ((int) round((float) $scoreVal).' điểm') : 'Đang chấm';
            $scoreLabel = 'ĐIỂM SỐ';
            $submetaText = $totalQuestions > 0 ? "{$correctCount}/{$totalQuestions} câu đúng" : 'Đã hoàn thành';
        } elseif ($skillCode === 'SPK' || $skillCode === 'WRT') {
            $scoreVal = $attempt->scoreResult?->total_score;
            $scoreText = $scoreVal !== null ? (round((float) $scoreVal, 1).' đ') : 'Đang chấm';
            $scoreLabel = 'KẾT QUẢ AI';
            $submetaText = $scoreVal !== null ? 'Đã chấm điểm hoàn tất' : 'Đang chờ xử lý';
        } else {
            $scoreText = $accuracyPercent !== null ? "{$accuracyPercent}%" : '0%';
            $scoreLabel = 'TỈ LỆ ĐÚNG';
            $submetaText = "{$correctCount}/{$totalQuestions} câu đúng";
        }

        // So sánh với lần làm trước
        $delta = $this->calculateComparisonDelta($attempt, $userId, $isExam, $sectionCode, $accuracyPercent);

        // Định dạng thời gian
        $startedAt = $attempt->started_at ?: Carbon::now();
        $timeFormatted = $startedAt->format('H:i • d/m');

        // Định dạng nhóm ngày
        if ($startedAt->isToday()) {
            $dateGroupKey = 'today';
            $dateGroupLabel = 'HÔM NAY';
        } elseif ($startedAt->isYesterday()) {
            $dateGroupKey = 'yesterday';
            $dateGroupLabel = 'HÔM QUA';
        } else {
            $dateGroupKey = $startedAt->format('Y-m-d');
            $dateGroupLabel = $startedAt->format('d/m/Y');
        }

        // URLs
        $resultUrl = route('lich-su.show', $attempt->attempt_id);
        $retryUrl = route('lich-su.retry', $attempt->attempt_id);

        return [
            'attempt_id' => (int) $attempt->attempt_id,
            'session_type' => (string) $attempt->session_type,
            'type' => $type,
            'is_exam' => $isExam,
            'mode_label' => $modeLabel,
            'title' => $title,
            'part_code' => $sectionCode,
            'icon_class' => $iconClass,
            'total_questions' => $totalQuestions,
            'correct_count' => $correctCount,
            'incorrect_count' => $incorrectCount,
            'unanswered_count' => $unansweredCount,
            'accuracy_percent' => $accuracyPercent,
            'score_text' => $scoreText,
            'score_label' => $scoreLabel,
            'submeta_text' => $submetaText,
            'time_formatted' => $timeFormatted,
            'date_group_key' => $dateGroupKey,
            'date_group_label' => $dateGroupLabel,
            'delta' => $delta,
            'result_url' => $resultUrl,
            'retry_url' => $retryUrl,
        ];
    }

    /**
     * So sánh kết quả với lượt làm hợp lệ gần nhất trước đó của cùng Part/Đề thi.
     *
     * @return array{type: string, text: string, class: string}
     */
    private function calculateComparisonDelta(
        TestAttempt $attempt,
        int $userId,
        bool $isExam,
        ?string $sectionCode,
        ?int $accuracyPercent
    ): array {
        if ($isExam) {
            if ($attempt->test_form_id === null) {
                return ['type' => 'neutral', 'text' => '• Lần đầu', 'class' => 'ls-delta--neutral'];
            }

            $prev = TestAttempt::query()
                ->where('user_id', $userId)
                ->where('attempt_id', '<', $attempt->attempt_id)
                ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
                ->whereIn('session_type', [
                    TestAttempt::SESSION_TYPE_PRACTICE_PART,
                    TestAttempt::SESSION_TYPE_FULL_TEST,
                    TestAttempt::SESSION_TYPE_EXAM,
                ])
                ->where('test_form_id', $attempt->test_form_id)
                ->with('scoreResult')
                ->orderBy('started_at', 'desc')
                ->orderBy('attempt_id', 'desc')
                ->first();

            if (! $prev || ! $prev->scoreResult || ! $attempt->scoreResult) {
                return ['type' => 'neutral', 'text' => '• Lần đầu', 'class' => 'ls-delta--neutral'];
            }

            $curScore = (float) $attempt->scoreResult->total_score;
            $prevScore = (float) $prev->scoreResult->total_score;
            $diff = round($curScore - $prevScore, 1);

            if ($diff > 0) {
                return ['type' => 'positive', 'text' => "+{$diff} so với lần trước", 'class' => 'ls-delta--positive'];
            } elseif ($diff < 0) {
                return ['type' => 'negative', 'text' => "{$diff} so với lần trước", 'class' => 'ls-delta--negative'];
            }

            return ['type' => 'neutral', 'text' => '0 so với lần trước', 'class' => 'ls-delta--neutral'];
        }

        // Bài luyện tập
        if (! $sectionCode || $accuracyPercent === null) {
            return ['type' => 'neutral', 'text' => '• Lần đầu', 'class' => 'ls-delta--neutral'];
        }

        $prev = TestAttempt::query()
            ->where('user_id', $userId)
            ->where('attempt_id', '<', $attempt->attempt_id)
            ->whereIn('status', [TestAttempt::STATUS_COMPLETED, 'SUBMITTED'])
            ->where('session_type', TestAttempt::SESSION_TYPE_PRACTICE)
            ->whereHas('attemptResponses.question.section', function ($s) use ($sectionCode): void {
                $s->where('code', $sectionCode);
            })
            ->with([
                'attemptResponses.question.options',
                'attemptResponses.question.questionType',
            ])
            ->orderBy('started_at', 'desc')
            ->orderBy('attempt_id', 'desc')
            ->first();

        if (! $prev) {
            return ['type' => 'neutral', 'text' => '• Lần đầu', 'class' => 'ls-delta--neutral'];
        }

        // Tính accuracy của bài trước
        $prevTotal = $prev->attemptResponses->count();
        $prevCorrect = 0;
        foreach ($prev->attemptResponses as $r) {
            $q = $r->question;
            if ($q && $q->questionType?->response_mode === 'SINGLE_CHOICE') {
                $pick = $r->text_response ? strtoupper(trim((string) $r->text_response)) : null;
                $cOpt = $q->options->firstWhere('is_correct', true);
                $cLabel = $cOpt ? strtoupper(trim((string) $cOpt->label)) : null;
                if ($pick !== null && $cLabel !== null && $pick === $cLabel) {
                    $prevCorrect++;
                }
            }
        }

        $prevAccuracy = $prevTotal > 0 ? (int) round(($prevCorrect / $prevTotal) * 100) : 0;
        $diff = $accuracyPercent - $prevAccuracy;

        if ($diff > 0) {
            return ['type' => 'positive', 'text' => "+{$diff}% so với lần trước", 'class' => 'ls-delta--positive'];
        } elseif ($diff < 0) {
            return ['type' => 'negative', 'text' => "{$diff}% so với lần trước", 'class' => 'ls-delta--negative'];
        }

        return ['type' => 'neutral', 'text' => '0% so với lần trước', 'class' => 'ls-delta--neutral'];
    }
}
