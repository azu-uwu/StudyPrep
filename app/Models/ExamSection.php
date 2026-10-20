<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'skill_id',
    'code',
    'name',
    'sequence_no',
    'default_time_limit',
])]
class ExamSection extends Model
{
    public $timestamps = false;

    protected $table = 'exam_section';

    protected $primaryKey = 'section_id';

    /**
     * @return BelongsTo<Skill, $this>
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'skill_id', 'skill_id');
    }

    /**
     * @return HasMany<Question, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'section_id', 'section_id');
    }
}
