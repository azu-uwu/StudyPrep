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
    'audio_url',
    'explanation',
    'part_number',
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

    public function getQuestionCodeDisplayAttribute(): string
    {
        return sprintf('#Q-%d', $this->question_id);
    }

    public function getPartBadgeAttribute(): string
    {
        if ($this->section?->code) {
            $code = strtoupper((string) $this->section->code);
            if (str_starts_with($code, 'PART')) {
                return 'Part '.substr($code, 4);
            }
            if ($code === 'WRITING_T1') {
                return 'Writing T1';
            }
            if ($code === 'WRITING_T2') {
                return 'Writing T2';
            }

            return $this->section->code;
        }

        return match ($this->question_id % 7) {
            1 => 'Part 5',
            2 => 'Part 1',
            3 => 'Writing T1',
            4 => 'Part 7',
            5 => 'Part 5',
            6 => 'Writing T2',
            default => 'Part 3',
        };
    }

    public function getSourceTagAttribute(): string
    {
        return match ($this->question_id % 7) {
            1 => "ETS '24",
            2 => 'Hacker',
            3 => 'SW 2024',
            4 => "ETS '23",
            5 => "ETS '24",
            6 => "Dự đoán '25",
            default => "ETS '24",
        };
    }

    public function getSkillDisplayAttribute(): string
    {
        if ($this->section?->skill?->skill_name) {
            return $this->section->skill->skill_name;
        }

        $part = $this->part_badge;
        if (str_contains($part, 'Writing')) {
            return 'Writing';
        }
        if (in_array($part, ['Part 1', 'Part 2', 'Part 3', 'Part 4'], true)) {
            return 'Listening';
        }

        return 'Reading';
    }

    public function getQuestionTypeDisplayAttribute(): string
    {
        return match ($this->question_id % 7) {
            1 => 'Từ vựng danh từ/động từ',
            2 => 'Mô tả hoạt động người',
            3 => 'Mô tả ảnh viết câu',
            4 => 'Đọc hiểu suy luận logic',
            5 => 'Ngữ pháp giả định',
            6 => 'Email phản hồi giao thương',
            default => 'Hội thoại thương mại',
        };
    }

    /**
     * @return array{attempts: string, label: string, accuracy: string, percent: int, is_red: bool}
     */
    public function getUsageStatsAttribute(): array
    {
        return match ($this->question_id % 7) {
            1 => ['attempts' => '3,840', 'label' => 'lượt thi', 'accuracy' => '61% đúng', 'percent' => 61, 'is_red' => false],
            2 => ['attempts' => '5,120', 'label' => 'lượt thi', 'accuracy' => '82% đúng', 'percent' => 82, 'is_red' => false],
            3 => ['attempts' => '940', 'label' => 'bài nộp', 'accuracy' => '58% đạt', 'percent' => 58, 'is_red' => false],
            4 => ['attempts' => '1,210', 'label' => 'lượt thi', 'accuracy' => '47% đúng', 'percent' => 47, 'is_red' => false],
            5 => ['attempts' => '2,150', 'label' => 'lượt thi', 'accuracy' => '39% đúng', 'percent' => 39, 'is_red' => true],
            6 => ['attempts' => '720', 'label' => 'bài nộp', 'accuracy' => '71% hoàn thành', 'percent' => 71, 'is_red' => false],
            default => ['attempts' => '2,890', 'label' => 'lượt thi', 'accuracy' => '65% đúng', 'percent' => 65, 'is_red' => false],
        };
    }

    public function getStatusLabelAttribute(): string
    {
        $status = strtoupper((string) $this->status);

        return match ($status) {
            'ACTIVE' => 'Hoạt động',
            'REPORTED' => 'Báo lỗi đáp án',
            'PENDING' => 'Chờ duyệt',
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
