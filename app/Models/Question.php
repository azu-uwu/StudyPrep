<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'section_id',
    'question_type_id',
    'question_set_id',
    'content',
    'explanation',
    'difficulty',
    'points',
    'status',
])]
class Question extends Model
{
    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

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
            'WRITING_T1' => 'Writing T1',
            'WRITING_T2' => 'Writing T2',
            default => $this->section?->name ?? 'Chưa phân loại',
        };
    }

    public function getSourceTagAttribute(): string
    {
        return $this->questionSet?->title ?? 'Chưa gắn bộ đề';
    }

    public function getSkillDisplayAttribute(): string
    {
        return $this->section?->skill?->skill_name ?? 'Chưa phân loại';
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
