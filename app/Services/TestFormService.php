<?php

namespace App\Services;

use App\Models\ExamSection;
use App\Models\Question;
use App\Models\Skill;
use App\Models\TestBlueprint;
use App\Models\TestForm;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestFormService
{
    public const PER_PAGE = 10;

    /**
     * Đảm bảo bảng skills và exam_section có đầy đủ tất cả 15 Part chuẩn TOEIC ETS
     * (Listening Part 1–4, Reading Part 5–7, Speaking 5 phần, Writing 3 phần).
     */
    public function ensureStandardSectionsExist(): void
    {
        if (! Schema::hasTable('skills') || ! Schema::hasTable('exam_section')) {
            return;
        }

        $skills = [
            1 => ['code' => 'LC', 'skill_name' => 'Listening'],
            2 => ['code' => 'RC', 'skill_name' => 'Reading'],
            3 => ['code' => 'SW', 'skill_name' => 'Speaking & Writing'],
        ];

        foreach ($skills as $skillId => $skillData) {
            if (! Skill::query()->where('skill_id', $skillId)->orWhere('code', $skillData['code'])->exists()) {
                DB::table('skills')->insertOrIgnore([
                    'skill_id' => $skillId,
                    'code' => $skillData['code'],
                    'skill_name' => $skillData['skill_name'],
                ]);
            }
        }

        $skillIdsByCode = Skill::query()->pluck('skill_id', 'code');
        $lcId = (int) ($skillIdsByCode['LC'] ?? 1);
        $rcId = (int) ($skillIdsByCode['RC'] ?? 2);
        $swId = (int) ($skillIdsByCode['SW'] ?? 3);

        $standardSections = [
            ['code' => 'PART1', 'skill_id' => $lcId, 'name' => 'Listening - Part 1: Photographs (Mô tả tranh)', 'sequence_no' => 1],
            ['code' => 'PART2', 'skill_id' => $lcId, 'name' => 'Listening - Part 2: Question-Response (Hỏi - Đáp)', 'sequence_no' => 2],
            ['code' => 'PART3', 'skill_id' => $lcId, 'name' => 'Listening - Part 3: Conversations (Hội thoại ngắn)', 'sequence_no' => 3],
            ['code' => 'PART4', 'skill_id' => $lcId, 'name' => 'Listening - Part 4: Short Talks (Bài nói ngắn)', 'sequence_no' => 4],
            ['code' => 'PART5', 'skill_id' => $rcId, 'name' => 'Reading - Part 5: Incomplete Sentences (Hoàn thành câu)', 'sequence_no' => 5],
            ['code' => 'PART6', 'skill_id' => $rcId, 'name' => 'Reading - Part 6: Text Completion (Hoàn thành đoạn văn)', 'sequence_no' => 6],
            ['code' => 'PART7', 'skill_id' => $rcId, 'name' => 'Reading - Part 7: Reading Comprehension (Đọc hiểu)', 'sequence_no' => 7],
            ['code' => 'WRITING_T1', 'skill_id' => $swId, 'name' => 'Writing - Part 1: Write a Sentence (Viết câu theo tranh)', 'sequence_no' => 8],
            ['code' => 'WRITING_T2', 'skill_id' => $swId, 'name' => 'Writing - Part 2: Respond to a Request (Viết email phản hồi)', 'sequence_no' => 9],
            ['code' => 'WRITING_T3', 'skill_id' => $swId, 'name' => 'Writing - Part 3: Opinion Essay (Viết bài luận)', 'sequence_no' => 10],
            ['code' => 'SPK_T1', 'skill_id' => $swId, 'name' => 'Speaking - Part 1: Read a Text Aloud (Đọc to văn bản)', 'sequence_no' => 11],
            ['code' => 'SPK_T2', 'skill_id' => $swId, 'name' => 'Speaking - Part 2: Describe a Picture (Mô tả tranh)', 'sequence_no' => 12],
            ['code' => 'SPK_T3', 'skill_id' => $swId, 'name' => 'Speaking - Part 3: Respond to Questions (Trả lời câu hỏi)', 'sequence_no' => 13],
            ['code' => 'SPK_T4', 'skill_id' => $swId, 'name' => 'Speaking - Part 4: Respond Using Information (Trả lời theo thông tin)', 'sequence_no' => 14],
            ['code' => 'SPK_T5', 'skill_id' => $swId, 'name' => 'Speaking - Part 5: Express an Opinion (Trình bày quan điểm)', 'sequence_no' => 15],
        ];

        $existingByCode = ExamSection::query()->get()->keyBy('code');
        foreach ($standardSections as $sec) {
            if (! isset($existingByCode[$sec['code']])) {
                ExamSection::query()->create($sec);
            } else {
                $existing = $existingByCode[$sec['code']];
                if ($existing->name !== $sec['name'] || (int) $existing->sequence_no !== $sec['sequence_no']) {
                    $existing->update([
                        'name' => $sec['name'],
                        'sequence_no' => $sec['sequence_no'],
                    ]);
                }
            }
        }
    }

    /**
     * Danh sách đề thi đã lọc, phân trang và đánh giá cấu trúc câu hỏi hợp lệ trên từng Part.
     *
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginate(
        string $search,
        string $examType,
        string $status,
        ?int $highlightTestId = null,
        int $perPage = self::PER_PAGE
    ): LengthAwarePaginator {
        $query = TestForm::query()
            ->with([
                'blueprint',
                'questions' => fn ($q) => $q->select('questions.question_id', 'questions.section_id', 'questions.status', 'questions.content')
                    ->with('section:section_id,code'),
            ]);

        $this->applySearch($query, $search);

        if (in_array($examType, TestForm::examTypes(), true)) {
            $query->ofExamType($examType);
        }

        if (in_array($status, TestForm::statuses(), true)) {
            $query->withCompletionStatus($status);
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('test_form_id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (TestForm $testForm): array => $this->toListRow($testForm, $highlightTestId));
    }

    /**
     * @return array{total: int, lr_count: int, sw_count: int, completed: int}
     */
    public function stats(): array
    {
        return [
            'total' => TestForm::query()->count(),
            'lr_count' => TestForm::query()->whereHas('blueprint', function (Builder $q): void {
                $q->whereIn('exam_type', [
                    TestForm::EXAM_TYPE_LR,
                    TestForm::EXAM_TYPE_LISTENING,
                    TestForm::EXAM_TYPE_READING,
                ]);
            })->count(),
            'sw_count' => TestForm::query()->whereHas('blueprint', function (Builder $q): void {
                $q->whereIn('exam_type', [
                    TestForm::EXAM_TYPE_SW,
                    TestForm::EXAM_TYPE_SPEAKING,
                    TestForm::EXAM_TYPE_WRITING,
                ]);
            })->count(),
            'completed' => TestForm::query()->withCompletionStatus(TestForm::STATUS_COMPLETED)->count(),
        ];
    }

    /**
     * Tạo đề thi mới (0 câu hỏi) gắn với blueprint chuẩn TOEIC ETS.
     *
     * @param  array{title: string, exam_type: string}  $data
     */
    public function create(array $data): TestForm
    {
        $this->ensureStandardSectionsExist();
        $definition = ExamSampleDataService::BLUEPRINTS[$data['exam_type']] ?? ExamSampleDataService::BLUEPRINTS[TestForm::EXAM_TYPE_LR];

        return DB::transaction(function () use ($data, $definition): TestForm {
            $blueprint = TestBlueprint::query()->updateOrCreate(
                ['exam_type' => $data['exam_type']],
                [
                    'name' => $definition['name'],
                    'version' => now()->format('Y'),
                    'total_time' => $definition['total_time'],
                    'status' => 'ACTIVE',
                ],
            );

            return TestForm::query()->create([
                'blueprint_id' => $blueprint->blueprint_id,
                'title' => trim($data['title']),
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Cập nhật tên đề thi và loại đề thi; nếu đổi loại đề khi đã có câu hỏi,
     * tự động gỡ (detach) các câu hỏi không thuộc kỹ năng/Part của loại đề mới.
     *
     * @param  array{title: string, exam_type: string}  $data
     * @return array{test_form: TestForm, detached_count: int}
     */
    public function update(TestForm $testForm, array $data): array
    {
        $this->ensureStandardSectionsExist();
        $definition = ExamSampleDataService::BLUEPRINTS[$data['exam_type']] ?? ExamSampleDataService::BLUEPRINTS[TestForm::EXAM_TYPE_LR];
        $allowedPartCodes = $this->allowedPartCodesFor($data['exam_type']);

        return DB::transaction(function () use ($testForm, $data, $definition, $allowedPartCodes): array {
            $blueprint = TestBlueprint::query()->updateOrCreate(
                ['exam_type' => $data['exam_type']],
                [
                    'name' => $definition['name'],
                    'version' => now()->format('Y'),
                    'total_time' => $definition['total_time'],
                    'status' => 'ACTIVE',
                ],
            );

            $testForm->update([
                'title' => trim($data['title']),
                'blueprint_id' => $blueprint->blueprint_id,
            ]);

            $detachedCount = 0;
            if (Schema::hasTable('test_form_question')) {
                $attachedQuestionIds = DB::table('test_form_question')
                    ->where('test_form_id', $testForm->test_form_id)
                    ->pluck('question_id')
                    ->all();

                if (! empty($attachedQuestionIds)) {
                    $incompatibleQuestionIds = Question::query()
                        ->whereIn('question_id', $attachedQuestionIds)
                        ->where(function (Builder $q) use ($allowedPartCodes): void {
                            $q->whereDoesntHave('section')
                                ->orWhereHas('section', fn (Builder $secQ) => $secQ->whereNotIn('code', $allowedPartCodes));
                        })
                        ->pluck('question_id')
                        ->all();

                    if (! empty($incompatibleQuestionIds)) {
                        $detachedCount = DB::table('test_form_question')
                            ->where('test_form_id', $testForm->test_form_id)
                            ->whereIn('question_id', $incompatibleQuestionIds)
                            ->delete();
                    }
                }
            }

            return [
                'test_form' => $testForm->fresh(['blueprint']) ?? $testForm,
                'detached_count' => $detachedCount,
            ];
        });
    }

    /**
     * Xóa đề thi nếu chưa có lịch sử làm bài của học viên (attempts).
     *
     * @return array{deleted: bool, attempts_count: int}
     */
    public function delete(TestForm $testForm): array
    {
        $attemptsTable = $testForm->attempts()->getRelated()->getTable();
        if (Schema::hasTable($attemptsTable) && $testForm->attempts()->exists()) {
            return [
                'deleted' => false,
                'attempts_count' => $testForm->attempts()->count(),
            ];
        }

        DB::transaction(function () use ($testForm): void {
            if (Schema::hasTable('test_form_question')) {
                DB::table('test_form_question')
                    ->where('test_form_id', $testForm->test_form_id)
                    ->delete();
            }

            $testForm->delete();
        });

        return [
            'deleted' => true,
            'attempts_count' => 0,
        ];
    }

    /**
     * Gắn danh sách câu hỏi vào đề thi với kiểm soát nghiêm ngặt:
     * - Bỏ qua câu trùng lặp đã tồn tại trong đề.
     * - Chặn câu hỏi không thuộc kỹ năng/Part định nghĩa trong Blueprint của đề.
     * - Chặn câu hỏi làm vượt quá số lượng tối đa của Part hoặc tổng chỉ tiêu đề.
     *
     * @param  array<int, int|string>  $questionIds
     * @return array{attached: int, skipped: int, rejected_skill: int, rejected_limit: int, activated: int}
     */
    public function attachQuestions(
        TestForm $testForm,
        array $questionIds,
        ?int $preferredSequenceNo = null,
        bool $autoActivateDrafts = true
    ): array {
        $cleanIds = array_values(array_unique(array_filter(array_map('intval', $questionIds), fn (int $id): bool => $id > 0)));
        if (empty($cleanIds)) {
            return ['attached' => 0, 'skipped' => 0, 'rejected_skill' => 0, 'rejected_limit' => 0, 'activated' => 0];
        }

        $testForm->loadMissing('blueprint');
        $examType = $this->examTypeOf($testForm);
        $partTargets = TestForm::partTargetsFor($examType);
        $totalTarget = TestForm::targetQuestionsFor($examType);

        $candidates = Question::query()
            ->with('section:section_id,code')
            ->whereIn('question_id', $cleanIds)
            ->get()
            ->keyBy('question_id');

        if ($candidates->isEmpty()) {
            return ['attached' => 0, 'skipped' => 0, 'rejected_skill' => 0, 'rejected_limit' => 0, 'activated' => 0];
        }

        return DB::transaction(function () use ($testForm, $cleanIds, $candidates, $partTargets, $totalTarget, $preferredSequenceNo, $autoActivateDrafts): array {
            $existingRows = DB::table('test_form_question')
                ->where('test_form_id', $testForm->test_form_id)
                ->get(['question_id', 'sequence_no']);

            $existingIds = $existingRows->pluck('question_id')->map(fn ($id): int => (int) $id)->all();
            $existingLookup = array_flip($existingIds);

            $currentPartCounts = [];
            $currentTotal = 0;
            if (! empty($existingIds)) {
                $existingQuestions = Question::query()
                    ->with('section:section_id,code')
                    ->whereIn('question_id', $existingIds)
                    ->get();

                foreach ($existingQuestions as $eq) {
                    if (strtoupper((string) $eq->status) === Question::STATUS_ARCHIVED) {
                        continue;
                    }

                    $code = strtoupper((string) ($eq->section?->code ?? ''));
                    if (array_key_exists($code, $partTargets)) {
                        $currentPartCounts[$code] = ($currentPartCounts[$code] ?? 0) + 1;
                        $currentTotal++;
                    }
                }
            }

            $maxSeq = (int) ($existingRows->max('sequence_no') ?? 0);
            $nextSeq = ($preferredSequenceNo !== null && $preferredSequenceNo > $maxSeq)
                ? $preferredSequenceNo
                : ($maxSeq + 1);

            $rows = [];
            $skippedDuplicate = 0;
            $rejectedSkill = 0;
            $rejectedLimit = 0;

            foreach ($cleanIds as $qId) {
                $question = $candidates->get($qId);
                if (! $question) {
                    continue;
                }

                if (isset($existingLookup[$qId])) {
                    $skippedDuplicate++;

                    continue;
                }

                $partCode = strtoupper((string) ($question->section?->code ?? ''));
                if ($partCode === '' || ! array_key_exists($partCode, $partTargets)) {
                    $rejectedSkill++;

                    continue;
                }

                $partCount = $currentPartCounts[$partCode] ?? 0;
                if ($partCount >= $partTargets[$partCode] || $currentTotal >= $totalTarget) {
                    $rejectedLimit++;

                    continue;
                }

                $rows[] = [
                    'test_form_id' => $testForm->test_form_id,
                    'question_id' => $qId,
                    'sequence_no' => $nextSeq++,
                    'point' => (float) ($question->points ?? 1.00),
                ];

                $currentPartCounts[$partCode] = $partCount + 1;
                $currentTotal++;
                $existingLookup[$qId] = true;
            }

            $activatedCount = 0;
            if (! empty($rows)) {
                DB::table('test_form_question')->insert($rows);

                if ($autoActivateDrafts) {
                    $attachedIds = array_column($rows, 'question_id');
                    $activatedCount = Question::query()
                        ->whereIn('question_id', $attachedIds)
                        ->where('status', Question::STATUS_DRAFT)
                        ->update(['status' => Question::STATUS_ACTIVE]);
                }
            }

            return [
                'attached' => count($rows),
                'skipped' => $skippedDuplicate,
                'rejected_skill' => $rejectedSkill,
                'rejected_limit' => $rejectedLimit,
                'activated' => $activatedCount,
            ];
        });
    }

    /**
     * Lấy danh sách mã Part hợp lệ của một loại đề thi để lọc nghiêm ngặt.
     *
     * @return list<string>
     */
    public function allowedPartCodesFor(string $examType): array
    {
        return TestForm::allowedPartCodesFor($examType);
    }

    /**
     * Chi tiết đề thi theo từng kỹ năng / Part kèm câu hỏi thực tế trong CSDL.
     *
     * @return array<string, mixed>
     */
    public function detail(TestForm $testForm): array
    {
        $this->ensureStandardSectionsExist();

        $testForm->load([
            'blueprint',
            'questions' => fn ($questionQuery) => $questionQuery
                ->with(['section', 'questionType', 'questionSet', 'options'])
                ->orderBy('test_form_question.sequence_no'),
        ]);

        $examType = $this->examTypeOf($testForm);
        $definition = ExamSampleDataService::BLUEPRINTS[$examType];
        $questionsBySection = $testForm->questions->groupBy(fn (Question $question): string => strtoupper((string) $question->section?->code));

        $partCodes = $this->allowedPartCodesFor($examType);
        $sectionIds = ExamSection::query()->whereIn('code', $partCodes)->pluck('section_id', 'code');

        $skills = [];
        foreach ($definition['skills'] as $skillKey => $skill) {
            $parts = [];
            foreach ($skill['parts'] as $part) {
                $partQuestions = $questionsBySection->get(strtoupper($part['code']), collect());
                $activePartCount = $partQuestions->filter(fn (Question $q): bool => strtoupper((string) $q->status) === Question::STATUS_ACTIVE)->count();

                $parts[] = array_merge($part, [
                    'section_id' => $sectionIds[$part['code']] ?? $part['section_id'],
                    'current_count' => $activePartCount,
                    'attached_count' => $partQuestions->count(),
                    'completion_rate' => $this->completionRate($activePartCount, $part['target_count']),
                    'is_complete' => $activePartCount === $part['target_count'],
                    'is_exceeded' => $activePartCount > $part['target_count'],
                    'questions' => $this->buildPartItems($partQuestions),
                ]);
            }

            $skills[$skillKey] = [
                'name' => $skill['name'],
                'skill_code' => $skill['skill_code'] ?? strtoupper($skillKey),
                'time_limit' => $skill['time_limit'],
                'total_questions' => $skill['total_questions'],
                'parts' => $parts,
            ];
        }

        $listeningPartCodes = ['PART1', 'PART2', 'PART3', 'PART4'];
        $isListeningTest = in_array($examType, [TestForm::EXAM_TYPE_LISTENING, TestForm::EXAM_TYPE_LR], true);
        $missingAudioQuestionIds = [];
        $totalListeningQuestions = 0;

        if ($isListeningTest) {
            foreach ($testForm->questions as $question) {
                $qPartCode = strtoupper((string) ($question->section?->code ?? ''));
                if (! in_array($qPartCode, $listeningPartCodes, true)) {
                    continue;
                }
                $totalListeningQuestions++;
                $resolvedAudio = $question->questionSet?->resolved_audio_url;
                if (empty($resolvedAudio)) {
                    $missingAudioQuestionIds[] = (int) $question->question_id;
                }
            }
        }

        $quality = $this->analyzeTestQuality($testForm, $examType, $testForm->questions);

        return array_merge($this->toListRow($testForm), [
            'blueprint' => $definition,
            'skills' => $skills,
            'allowed_part_codes' => $partCodes,
            'audio_summary' => [
                'is_listening_test' => $isListeningTest,
                'total_listening_questions' => $totalListeningQuestions,
                'missing_audio_count' => count($missingAudioQuestionIds),
                'has_audio_count' => max(0, $totalListeningQuestions - count($missingAudioQuestionIds)),
                'missing_audio_question_ids' => $missingAudioQuestionIds,
            ],
            'quality' => $quality,
        ]);
    }

    /**
     * Phân tích chất lượng đề thi toàn diện (chỉ tiêu từng Part, sai kỹ năng, thiếu định dạng, trùng lặp).
     *
     * @param  Collection<int, Question>  $attachedQuestions
     * @return array{
     *     has_warnings: bool,
     *     warning_count: int,
     *     quota_issues: list<array{part_code: string, part_name: string, current: int, target: int, diff: int, type: string, message: string}>,
     *     wrong_skill_ids: list<int>,
     *     format_issues: list<array{question_id: int, code: string, part_code: string, reason: string}>,
     *     duplicate_ids: list<int>,
     *     duplicate_groups: list<array{keep_id: int, duplicate_ids: list<int>, sample: string}>,
     *     cleanup_ids: list<int>
     * }
     */
    public function analyzeTestQuality(TestForm $testForm, string $examType, Collection $attachedQuestions): array
    {
        $definition = ExamSampleDataService::BLUEPRINTS[$examType] ?? ExamSampleDataService::BLUEPRINTS[TestForm::EXAM_TYPE_LR];
        $partTargets = TestForm::partTargetsFor($examType);
        $partNames = [];
        foreach ($definition['skills'] as $skill) {
            foreach ($skill['parts'] as $part) {
                $partNames[strtoupper((string) $part['code'])] = (string) $part['name'];
            }
        }

        $countsByPart = [];
        $wrongSkillIds = [];
        $formatIssues = [];

        foreach ($attachedQuestions as $question) {
            $partCode = strtoupper((string) ($question->section?->code ?? ''));
            $qId = (int) $question->question_id;
            $qCode = '#Q-'.$qId;

            if ($partCode === '' || ! array_key_exists($partCode, $partTargets)) {
                $wrongSkillIds[] = $qId;

                continue;
            }

            if (strtoupper((string) $question->status) === Question::STATUS_ACTIVE) {
                $countsByPart[$partCode] = ($countsByPart[$partCode] ?? 0) + 1;
            }

            // Kiểm tra định dạng bắt buộc theo từng Part
            $qSet = $question->questionSet;
            $hasAudio = ! empty($qSet?->resolved_audio_url);
            $hasTranscript = trim((string) ($qSet?->transcript ?? '')) !== '';
            $hasImage = ! empty($qSet?->resolved_media_url);
            $hasStimulus = trim((string) ($qSet?->stimulus_text ?? '')) !== '';

            if (in_array($partCode, ['PART1', 'PART2', 'PART3', 'PART4'], true)) {
                if (! $hasAudio && ! $hasTranscript) {
                    $formatIssues[] = [
                        'question_id' => $qId,
                        'code' => $qCode,
                        'part_code' => $partCode,
                        'reason' => 'Câu Listening thiếu cả file Audio lẫn Transcript',
                    ];
                }
                if ($partCode === 'PART1' && ! $hasImage) {
                    $formatIssues[] = [
                        'question_id' => $qId,
                        'code' => $qCode,
                        'part_code' => $partCode,
                        'reason' => 'Part 1 (Mô tả tranh) đang thiếu hình ảnh minh họa',
                    ];
                }
            }

            if (in_array($partCode, ['PART6', 'PART7'], true) && ! $hasStimulus && ! $hasImage) {
                $formatIssues[] = [
                    'question_id' => $qId,
                    'code' => $qCode,
                    'part_code' => $partCode,
                    'reason' => "{$partCode} đang thiếu đoạn văn đọc hiểu (stimulus_text)",
                ];
            }

            if (in_array($partCode, ['PART1', 'PART2', 'PART3', 'PART4', 'PART5', 'PART6', 'PART7'], true)) {
                $options = $question->relationLoaded('options') ? $question->options : $question->options()->get();
                $expectedOptions = $partCode === 'PART2' ? 3 : 4;
                if ($options->count() < $expectedOptions) {
                    $formatIssues[] = [
                        'question_id' => $qId,
                        'code' => $qCode,
                        'part_code' => $partCode,
                        'reason' => "Thiếu đáp án trắc nghiệm (hiện có {$options->count()}/{$expectedOptions} lựa chọn)",
                    ];
                } elseif (! $options->contains(fn ($opt): bool => (bool) $opt->is_correct)) {
                    $formatIssues[] = [
                        'question_id' => $qId,
                        'code' => $qCode,
                        'part_code' => $partCode,
                        'reason' => 'Chưa đánh dấu đáp án đúng',
                    ];
                }
            }
        }

        $quotaIssues = [];
        $hasAnyAttached = $attachedQuestions->isNotEmpty();
        foreach ($partTargets as $code => $target) {
            $current = $countsByPart[$code] ?? 0;
            $shortPart = $partNames[$code] ?? $code;
            if (str_contains($shortPart, ':')) {
                $shortPart = trim(explode(':', $shortPart)[0]);
            }

            if ($current > $target) {
                $diff = $current - $target;
                $quotaIssues[] = [
                    'part_code' => $code,
                    'part_name' => $shortPart,
                    'current' => $current,
                    'target' => $target,
                    'diff' => $diff,
                    'type' => 'excess',
                    'message' => "{$shortPart} đang có {$current}/{$target} câu — thừa {$diff} câu",
                ];
            } elseif ($hasAnyAttached && $current < $target) {
                $diff = $target - $current;
                $quotaIssues[] = [
                    'part_code' => $code,
                    'part_name' => $shortPart,
                    'current' => $current,
                    'target' => $target,
                    'diff' => $diff,
                    'type' => 'missing',
                    'message' => "{$shortPart} đang có {$current}/{$target} câu — thiếu {$diff} câu",
                ];
            }
        }

        $duplicateAnalysis = $this->detectDuplicateQuestions($attachedQuestions);
        $duplicateIds = $duplicateAnalysis['duplicate_ids'];
        $duplicateGroups = $duplicateAnalysis['duplicate_groups'];

        $cleanupIds = array_values(array_unique(array_merge($wrongSkillIds, $duplicateIds)));
        $warningCount = count($quotaIssues) + count($wrongSkillIds) + count($formatIssues) + count($duplicateIds);

        return [
            'has_warnings' => $warningCount > 0,
            'warning_count' => $warningCount,
            'quota_issues' => $quotaIssues,
            'wrong_skill_ids' => array_values(array_unique($wrongSkillIds)),
            'format_issues' => $formatIssues,
            'duplicate_ids' => $duplicateIds,
            'duplicate_groups' => $duplicateGroups,
            'cleanup_ids' => $cleanupIds,
        ];
    }

    /**
     * Gỡ nhanh toàn bộ câu hỏi trùng lặp nội dung và câu hỏi sai kỹ năng khỏi đề thi trong 1 click.
     *
     * @return array{detached_wrong_skill: int, detached_duplicates: int, total_detached: int}
     */
    public function cleanupQualityIssues(TestForm $testForm): array
    {
        $testForm->load([
            'blueprint',
            'questions' => fn ($q) => $q->with(['section', 'questionSet', 'options'])
                ->orderBy('test_form_question.sequence_no'),
        ]);

        $examType = $this->examTypeOf($testForm);
        $quality = $this->analyzeTestQuality($testForm, $examType, $testForm->questions);

        $wrongSkillIds = $quality['wrong_skill_ids'];
        $duplicateIds = $quality['duplicate_ids'];
        $cleanupIds = $quality['cleanup_ids'];

        $totalDetached = 0;
        if (! empty($cleanupIds) && Schema::hasTable('test_form_question')) {
            $totalDetached = DB::table('test_form_question')
                ->where('test_form_id', $testForm->test_form_id)
                ->whereIn('question_id', $cleanupIds)
                ->delete();
        }

        return [
            'detached_wrong_skill' => count($wrongSkillIds),
            'detached_duplicates' => count($duplicateIds),
            'total_detached' => $totalDetached,
        ];
    }

    /**
     * Tạo khóa nhận diện duy nhất cho câu hỏi dựa trên Part, nội dung và toàn bộ ngữ liệu đính kèm
     * (ảnh, audio, transcript, đoạn văn/stimulus, các phương án trả lời) để tránh báo trùng giả.
     */
    public function buildQuestionFingerprint(Question $question): string
    {
        $sectionKey = $question->section_id !== null
            ? (string) $question->section_id
            : strtoupper((string) ($question->section?->code ?? ''));

        $content = $this->normalizeFingerprintPart((string) $question->content);
        $questionSet = $question->questionSet;

        $mediaUrl = $this->normalizeFingerprintPart($questionSet?->media_url);
        $audioUrl = $this->normalizeFingerprintPart($questionSet?->audio_url);
        $transcript = $this->normalizeFingerprintPart($questionSet?->transcript);
        $stimulus = $this->normalizeFingerprintPart($questionSet?->stimulus_text);

        $options = $question->relationLoaded('options')
            ? $question->options
            : $question->options()->get();

        $optionParts = $options
            ->sortBy(fn ($opt) => strtoupper((string) $opt->label))
            ->map(function ($opt): string {
                $label = strtoupper(trim((string) $opt->label));
                $text = $this->normalizeFingerprintPart((string) $opt->option_text);

                return $label.':'.$text;
            })
            ->values()
            ->all();

        return implode('||', [
            'sec='.$sectionKey,
            'cnt='.$content,
            'img='.$mediaUrl,
            'aud='.$audioUrl,
            'trs='.$transcript,
            'stm='.$stimulus,
            'opt='.implode('|', $optionParts),
        ]);
    }

    private function normalizeFingerprintPart(?string $value): string
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return '';
        }

        return mb_strtolower((string) preg_replace('/\s+/u', ' ', $trimmed), 'UTF-8');
    }

    /**
     * Phát hiện câu hỏi trùng lặp (trùng question_id hoặc trùng fingerprint đầy đủ gồm nội dung + ngữ liệu đính kèm).
     *
     * @param  Collection<int, Question>  $attachedQuestions
     * @return array{duplicate_ids: list<int>, duplicate_groups: list<array{keep_id: int, duplicate_ids: list<int>, sample: string}>}
     */
    private function detectDuplicateQuestions(Collection $attachedQuestions): array
    {
        $seenQuestionIds = [];
        $seenByContentKey = [];
        $duplicateIds = [];
        $groupsByKey = [];

        $genericPlaceholders = [
            'look at the picture and listen to the four statements.',
            'listen to the question and choose the best response.',
        ];

        foreach ($attachedQuestions as $question) {
            $qId = (int) $question->question_id;

            if (isset($seenQuestionIds[$qId])) {
                $duplicateIds[] = $qId;

                continue;
            }
            $seenQuestionIds[$qId] = true;

            $rawContent = trim((string) $question->content);
            $normalized = $this->normalizeFingerprintPart($rawContent);
            if ($normalized === '') {
                continue;
            }

            $questionSet = $question->questionSet;
            $hasAttachedContext = $this->normalizeFingerprintPart($questionSet?->media_url) !== ''
                || $this->normalizeFingerprintPart($questionSet?->audio_url) !== ''
                || $this->normalizeFingerprintPart($questionSet?->transcript) !== ''
                || $this->normalizeFingerprintPart($questionSet?->stimulus_text) !== ''
                || ($question->relationLoaded('options') ? $question->options->isNotEmpty() : $question->options()->exists());

            if (in_array($normalized, $genericPlaceholders, true) && ! $hasAttachedContext) {
                continue;
            }

            $key = $this->buildQuestionFingerprint($question);
            if (! isset($seenByContentKey[$key])) {
                $seenByContentKey[$key] = [
                    'keep_id' => $qId,
                    'sample' => mb_strimwidth($rawContent, 0, 90, '...'),
                ];
            } else {
                $duplicateIds[] = $qId;
                if (! isset($groupsByKey[$key])) {
                    $groupsByKey[$key] = [
                        'keep_id' => $seenByContentKey[$key]['keep_id'],
                        'duplicate_ids' => [],
                        'sample' => $seenByContentKey[$key]['sample'],
                    ];
                }
                $groupsByKey[$key]['duplicate_ids'][] = $qId;
            }
        }

        return [
            'duplicate_ids' => array_values(array_unique($duplicateIds)),
            'duplicate_groups' => array_values($groupsByKey),
        ];
    }

    /**
     * Tìm theo tên đề (LIKE) hoặc mã đề dạng TEST-001 / 001 / 1.
     *
     * @param  Builder<TestForm>  $query
     */
    private function applySearch(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $searchQuery) use ($search): void {
            $searchQuery->where('title', 'like', '%'.$search.'%');

            if (preg_match('/^(?:test\s*-?\s*)?0*(\d+)$/i', $search, $matches) === 1) {
                $searchQuery->orWhere('test_form_id', (int) $matches[1]);
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function toListRow(TestForm $testForm, ?int $highlightTestId = null): array
    {
        $examType = $this->examTypeOf($testForm);
        $definition = ExamSampleDataService::BLUEPRINTS[$examType];
        $targetQuestions = TestForm::targetQuestionsFor($examType);

        $attachedQuestions = $testForm->relationLoaded('questions')
            ? $testForm->questions
            : $testForm->questions()->with('section:section_id,code')->get();

        $activeCountsByPart = [];
        $unapprovedCount = 0;

        foreach ($attachedQuestions as $question) {
            if (strtoupper((string) $question->status) !== Question::STATUS_ACTIVE) {
                $unapprovedCount++;

                continue;
            }

            $code = strtoupper((string) ($question->section?->code ?? '__UNKNOWN__'));
            $activeCountsByPart[$code] = ($activeCountsByPart[$code] ?? 0) + 1;
        }

        $evaluation = TestForm::evaluateStructure($activeCountsByPart, $examType);
        $validQuestions = $evaluation['valid_count'];
        $duplicateAnalysis = $this->detectDuplicateQuestions($attachedQuestions);
        $duplicateCount = count($duplicateAnalysis['duplicate_ids']);

        return [
            'id' => $testForm->test_form_id,
            'code' => $testForm->test_code,
            'title' => $testForm->title,
            'exam_type' => $examType,
            'type_name' => $definition['name'],
            'short_type_name' => $definition['short_name'] ?? $definition['name'],
            'badge_bg' => $definition['badge_bg'] ?? '#eef2ff',
            'badge_color' => $definition['badge_color'] ?? '#4338ca',
            'badge_border' => $definition['badge_border'] ?? '#c7d2fe',
            'icon' => $definition['icon'] ?? 'headphones',
            'total_time' => $definition['total_time'],
            'target_questions' => $targetQuestions,
            'current_questions' => $validQuestions,
            'total_attached_questions' => $attachedQuestions->count(),
            'wrong_skill_count' => $evaluation['wrong_skill_count'],
            'duplicate_count' => $duplicateCount,
            'unapproved_count' => $unapprovedCount,
            'completion_rate' => $this->completionRate($validQuestions, $targetQuestions),
            'status' => $evaluation['status'],
            'issue_reason' => $evaluation['issue_reason'],
            'created_at' => $testForm->created_at?->format('d/m/Y H:i') ?? '-',
            'is_newly_created' => $highlightTestId !== null && $highlightTestId === $testForm->test_form_id,
        ];
    }

    private function examTypeOf(TestForm $testForm): string
    {
        $examType = (string) $testForm->blueprint?->exam_type;

        return in_array($examType, TestForm::examTypes(), true) ? $examType : TestForm::EXAM_TYPE_LR;
    }

    /**
     * Tính tỷ lệ phần trăm thực tế (không cắt trần min(100, ...) để phản ánh trung thực khi vượt chỉ tiêu).
     */
    private function completionRate(int $currentQuestions, int $targetQuestions): float
    {
        if ($targetQuestions <= 0) {
            return 0.0;
        }

        return round($currentQuestions / $targetQuestions * 100, 1);
    }

    /**
     * Gom câu hỏi của một Part: câu đơn giữ nguyên, câu dùng chung ngữ cảnh gom theo question_set.
     *
     * @param  Collection<int, Question>  $questions
     * @return list<array<string, mixed>>
     */
    private function buildPartItems(Collection $questions): array
    {
        $items = [];
        $groupPositions = [];

        foreach ($questions as $question) {
            $questionRow = $this->toQuestionRow($question);

            if ($question->question_set_id === null) {
                $items[] = array_merge($questionRow, ['is_grouped' => false]);

                continue;
            }

            if (! isset($groupPositions[$question->question_set_id])) {
                $groupPositions[$question->question_set_id] = count($items);
                $mediaUrl = $question->questionSet?->resolved_media_url;
                $audioUrl = $question->questionSet?->resolved_audio_url;
                $mediaType = strtoupper((string) ($question->questionSet?->media_type ?? ''));
                $isImageMedia = $mediaType === 'IMAGE' || (is_string($mediaUrl) && preg_match('/\.(png|jpe?g|gif|webp|bmp)$/i', $mediaUrl) === 1);
                $items[] = [
                    'is_grouped' => true,
                    'group_id' => $question->question_set_id,
                    'group_title' => $question->questionSet?->title ?? 'Nhóm câu hỏi #'.$question->question_set_id,
                    'stimulus_text' => $question->questionSet?->stimulus_text,
                    'media_url' => $mediaUrl,
                    'media_type' => $mediaType !== '' ? $mediaType : ($isImageMedia ? 'IMAGE' : ($audioUrl ? 'AUDIO' : null)),
                    'image_url' => $isImageMedia ? $mediaUrl : null,
                    'audio_url' => $audioUrl,
                    'transcript' => $question->questionSet?->transcript,
                    'child_questions' => [],
                ];
            }

            $items[$groupPositions[$question->question_set_id]]['child_questions'][] = $questionRow;
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private function toQuestionRow(Question $question): array
    {
        $mediaUrl = $question->questionSet?->resolved_media_url;
        $audioUrl = $question->questionSet?->resolved_audio_url;
        $mediaType = strtoupper((string) ($question->questionSet?->media_type ?? ''));
        $isImageMedia = $mediaType === 'IMAGE' || (is_string($mediaUrl) && preg_match('/\.(png|jpe?g|gif|webp|bmp)$/i', $mediaUrl) === 1);

        return [
            'id' => $question->question_id,
            'question_id' => $question->question_id,
            'section_id' => $question->section_id,
            'part_code' => $question->section?->code ?? 'PART5',
            'sequence_no' => (int) $question->pivot->sequence_no,
            'question_code' => $question->question_code_display,
            'content' => $question->content,
            'explanation' => $question->explanation ?? '',
            'difficulty' => $question->difficulty,
            'status' => strtoupper((string) $question->status),
            'question_type' => $question->question_type_display,
            'points' => (float) $question->pivot->point,
            'media_url' => $mediaUrl,
            'media_type' => $mediaType !== '' ? $mediaType : ($isImageMedia ? 'IMAGE' : ($audioUrl ? 'AUDIO' : null)),
            'image_url' => $isImageMedia ? $mediaUrl : null,
            'audio_url' => $audioUrl,
            'transcript' => $question->questionSet?->transcript,
            'stimulus_text' => $question->questionSet?->stimulus_text,
            'correct_option' => $question->options->firstWhere('is_correct', true)?->label ?? '',
            'options' => $question->options
                ->map(fn ($option): array => [
                    'label' => $option->label,
                    'content' => $option->content,
                    'is_correct' => (bool) $option->is_correct,
                ])
                ->values()
                ->all(),
        ];
    }
}
