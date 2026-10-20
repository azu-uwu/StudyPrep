<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
