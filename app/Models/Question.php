<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'question_id',
    'section_id',
    'question_type_id',
    'question_set_id',
    'content',
    'explanation',
    'difficulty',
    'points',
    'status',
    'created_at',
])]
class Question extends Model
{
    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    public const STATUS_ACTIVE = 'ACTIVE';

    public const STATUS_DRAFT = 'DRAFT';

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_REPORTED = 'REPORTED';

    public const STATUS_ARCHIVED = 'ARCHIVED';

    protected $table = 'questions';

    protected $primaryKey = 'question_id';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Question>  $query
     * @return Builder<Question>
     */
    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * @param  Builder<Question>  $query
     * @return Builder<Question>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE');
    }

    /**
     * @param  Builder<Question>  $query
     * @return Builder<Question>
     */
    #[Scope]
    protected function reported(Builder $query): Builder
    {
        return $query->where('status', 'REPORTED');
    }

    /**
     * @return BelongsTo<ExamSection, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(ExamSection::class, 'section_id', 'section_id');
    }

    /**
     * @return BelongsTo<QuestionType, $this>
     */
    public function questionType(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class, 'question_type_id', 'question_type_id');
    }

    /**
     * @return HasMany<AnswerOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(AnswerOption::class, 'question_id', 'question_id');
    }

    /**
     * @return HasMany<AttemptResponse, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(AttemptResponse::class, 'question_id', 'question_id');
    }

    /**
     * @return HasMany<Feedback, $this>
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class, 'question_id', 'question_id');
    }

    public function getQuestionCodeDisplayAttribute(): string
    {
        return sprintf('#Q-%d', $this->question_id);
    }

    public function getPartBadgeAttribute(): string
    {
        $code = strtoupper((string) $this->section?->code);

        if (preg_match('/^PART(\d+)$/', $code, $matches) === 1) {
            return 'Part '.$matches[1];
        }

        return match ($code) {
            'WRITING_T1' => 'WRT Task 1',
            'WRITING_T2' => 'WRT Task 2',
            'WRITING_T3' => 'WRT Task 3',
            'SPK_T1' => 'SPK Task 1',
            'SPK_T2' => 'SPK Task 2',
            'SPK_T3' => 'SPK Task 3',
            'SPK_T4' => 'SPK Task 4',
            'SPK_T5' => 'SPK Task 5',
            default => $this->section?->name ?? 'Chưa phân loại',
        };
    }

    public function getSkillKeyAttribute(): string
    {
        $code = strtoupper((string) $this->section?->code);

        if (in_array($code, ['PART1', 'PART2', 'PART3', 'PART4'], true)) {
            return 'listening';
        }
        if (in_array($code, ['PART5', 'PART6', 'PART7'], true)) {
            return 'reading';
        }
        if (str_starts_with($code, 'SPK_')) {
            return 'speaking';
        }
        if (str_starts_with($code, 'WRITING_')) {
            return 'writing';
        }

        $skillCode = strtoupper((string) $this->section?->skill?->code);

        return match ($skillCode) {
            'LC' => 'listening',
            'RC' => 'reading',
            default => 'reading',
        };
    }

    public function getPartFullLabelAttribute(): string
    {
        $code = strtoupper((string) $this->section?->code);

        return match ($code) {
            'PART1' => 'Listening • Part 1: Photographs',
            'PART2' => 'Listening • Part 2: Question-Response',
            'PART3' => 'Listening • Part 3: Conversations',
            'PART4' => 'Listening • Part 4: Short Talks',
            'PART5' => 'Reading • Part 5: Incomplete Sentences',
            'PART6' => 'Reading • Part 6: Text Completion',
            'PART7' => 'Reading • Part 7: Reading Comprehension',
            'SPK_T1' => 'Speaking • Task 1: Read a Text Aloud',
            'SPK_T2' => 'Speaking • Task 2: Describe a Picture',
            'SPK_T3' => 'Speaking • Task 3: Respond to Questions',
            'SPK_T4' => 'Speaking • Task 4: Respond Using Information',
            'SPK_T5' => 'Speaking • Task 5: Express an Opinion',
            'WRITING_T1' => 'Writing • Task 1: Write a Sentence Based on a Picture',
            'WRITING_T2' => 'Writing • Task 2: Respond to a Written Request',
            'WRITING_T3' => 'Writing • Task 3: Write an Opinion Essay',
            default => $this->section?->name ?? $this->part_badge,
        };
    }

    public function getGroupQuestionsCountAttribute(): int
    {
        if (! $this->question_set_id) {
            return 1;
        }

        if ($this->relationLoaded('questionSet') && $this->questionSet && $this->questionSet->relationLoaded('questions')) {
            return max(1, $this->questionSet->questions->count());
        }

        return 1;
    }

    /**
     * Trả về cấu trúc dữ liệu đầy đủ, không đệ quy vòng tròn để render Modal Xem trước mô phỏng đề thi thật.
     *
     * @return array<string, mixed>
     */
    public function toPreviewPayload(): array
    {
        $partCode = strtoupper((string) ($this->section?->code ?? 'PART5'));
        $skillKey = $this->skill_key;
        $skillLabel = match ($skillKey) {
            'listening' => 'Listening',
            'reading' => 'Reading',
            'speaking' => 'Speaking',
            'writing' => 'Writing',
            default => 'Reading',
        };

        $etsTiming = match ($partCode) {
            'SPK_T1' => ['prep' => '45 giây', 'response' => '45 giây', 'hint' => 'Đọc to rõ ràng, nhấn trọng âm và ngắt nghỉ đúng dấu câu.'],
            'SPK_T2' => ['prep' => '45 giây', 'response' => '30 giây', 'hint' => 'Mô tả tổng quan bối cảnh, nhân vật chính, hành động và chi tiết nền.'],
            'SPK_T3' => ['prep' => '3 giây', 'response' => '15–30 giây', 'hint' => 'Trả lời trực tiếp câu hỏi, thêm 1–2 ý giải thích ngắn gọn.'],
            'SPK_T4' => ['prep' => '3 giây', 'response' => '15–30 giây', 'hint' => 'Đối chiếu chính xác thông tin từ bảng/lịch trình được cung cấp.'],
            'SPK_T5' => ['prep' => '45 giây', 'response' => '60 giây', 'hint' => 'Nêu rõ quan điểm, đưa ra 2 luận điểm kèm ví dụ thực tế.'],
            'WRITING_T1' => ['time' => '8 phút (5 câu)', 'min_words' => 8, 'hint' => 'Viết 1 câu hoàn chỉnh đúng ngữ pháp sử dụng đủ 2 từ khóa cho sẵn.'],
            'WRITING_T2' => ['time' => '10 phút / email', 'min_words' => 80, 'hint' => 'Phản hồi đầy đủ yêu cầu trong email gốc với giọng văn công sở lịch sự.'],
            'WRITING_T3' => ['time' => '30 phút', 'min_words' => 300, 'hint' => 'Bài luận tối thiểu 300 từ gồm Mở bài, Thân bài (2–3 đoạn) và Kết bài.'],
            default => null,
        };

        $mapOptions = static fn ($options): array => collect($options)
            ->sortBy('label')
            ->map(fn ($o): array => [
                'label' => strtoupper((string) $o->label),
                'content' => (string) $o->content,
                'is_correct' => (bool) $o->is_correct,
            ])
            ->values()
            ->all();

        $qSetPayload = null;
        if ($this->questionSet) {
            $resolvedUrl = $this->questionSet->resolved_media_url;
            $resolvedAudioUrl = $this->questionSet->resolved_audio_url;
            $mediaType = strtoupper((string) ($this->questionSet->media_type ?? ''));
            $isImage = $resolvedUrl !== null && ($mediaType === 'IMAGE' || preg_match('/\.(png|jpe?g|gif|webp|bmp)$/i', $resolvedUrl) === 1);
            $isAudio = $resolvedAudioUrl !== null;

            $siblingQuestions = $this->questionSet->relationLoaded('questions')
                ? $this->questionSet->questions->sortBy('question_id')->values()
                : collect([$this]);

            $qSetPayload = [
                'question_set_id' => $this->questionSet->question_set_id,
                'title' => $this->questionSet->title,
                'stimulus_text' => $this->questionSet->stimulus_text,
                'transcript' => $this->questionSet->transcript,
                'media_url' => $this->questionSet->media_url,
                'resolved_media_url' => $resolvedUrl,
                'audio_url' => $this->questionSet->audio_url,
                'resolved_audio_url' => $resolvedAudioUrl,
                'media_type' => $mediaType,
                'is_image' => $isImage,
                'is_audio' => $isAudio,
                'group_count' => $siblingQuestions->count(),
                'questions' => $siblingQuestions->map(fn (Question $sq): array => [
                    'question_id' => $sq->question_id,
                    'question_code' => $sq->question_code_display,
                    'content' => $sq->content,
                    'explanation' => $sq->explanation,
                    'options' => $mapOptions($sq->options),
                ])->all(),
            ];
        }

        return [
            'question_id' => $this->question_id,
            'question_code' => $this->question_code_display,
            'section_id' => $this->section_id,
            'part_code' => $partCode,
            'part_badge' => $this->part_badge,
            'part_full_label' => $this->part_full_label,
            'skill_key' => $skillKey,
            'skill_label' => $skillLabel,
            'source_tag' => $this->source_tag,
            'difficulty' => $this->difficulty,
            'status' => strtoupper((string) $this->status),
            'status_label' => $this->status_label,
            'content' => $this->content,
            'explanation' => $this->explanation,
            'options' => $mapOptions($this->options),
            'group_count' => $this->group_questions_count,
            'ets_timing' => $etsTiming,
            'question_set' => $qSetPayload,
        ];
    }

    /**
     * @return BelongsToMany<TestForm, $this>
     */
    public function testForms(): BelongsToMany
    {
        return $this->belongsToMany(TestForm::class, 'test_form_question', 'question_id', 'test_form_id')
            ->withPivot(['sequence_no', 'point']);
    }

    public function getSourceTagAttribute(): string
    {
        if ($this->relationLoaded('testForms') && $this->testForms->isNotEmpty()) {
            return $this->testForms->first()->test_code;
        }

        return $this->questionSet?->title ?? 'Chưa gắn bộ đề';
    }

    public function getSkillDisplayAttribute(): string
    {
        return match ($this->skill_key) {
            'listening' => 'Listening',
            'reading' => 'Reading',
            'speaking' => 'Speaking',
            'writing' => 'Writing',
            default => $this->section?->skill?->skill_name ?? 'Chưa phân loại',
        };
    }

    public function getDifficultyLabelAttribute(): string
    {
        return match (strtoupper((string) $this->difficulty)) {
            'EASY' => 'Cơ bản',
            'MEDIUM' => 'Trung bình',
            'HARD' => 'Nâng cao',
            default => 'Chưa phân loại',
        };
    }

    public function getQuestionTypeDisplayAttribute(): string
    {
        return $this->questionType?->name ?? 'Chưa phân loại';
    }

    /**
     * @return array{attempts: string, label: string, accuracy: string, percent: int, is_red: bool}
     */
    public function getUsageStatsAttribute(): array
    {
        $attempts = (int) ($this->responses_count ?? 0);
        $correct = (int) ($this->correct_responses_count ?? 0);
        $percent = $attempts > 0 ? (int) round($correct / $attempts * 100) : 0;

        return [
            'attempts' => number_format($attempts),
            'label' => 'lượt trả lời',
            'accuracy' => $attempts > 0 ? "{$percent}% đạt" : 'Chưa có dữ liệu',
            'percent' => $percent,
            'is_red' => $attempts > 0 && $percent < 50,
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        $status = strtoupper((string) $this->status);

        return match ($status) {
            'ACTIVE' => 'Hoạt động',
            'REPORTED' => 'Báo lỗi đáp án',
            'PENDING' => 'Chờ duyệt',
            'ARCHIVED' => 'Đã lưu trữ',
            default => 'Bản nháp',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        $status = strtoupper((string) $this->status);

        return match ($status) {
            'ACTIVE' => 'status-badge-active',
            'REPORTED' => 'status-badge-error',
            'PENDING' => 'status-badge-pending',
            default => 'status-badge-draft',
        };
    }

    public function questionSet(): BelongsTo
    {
        return $this->belongsTo(QuestionSet::class, 'question_set_id', 'question_set_id');
    }
}
