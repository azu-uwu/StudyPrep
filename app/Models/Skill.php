<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'skill_id',
    'code',
    'skill_name',
])]
class Skill extends Model
{
    public $timestamps = false;

    protected $table = 'skills';

    protected $primaryKey = 'skill_id';

    /**
     * @return HasMany<ExamSection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(ExamSection::class, 'skill_id', 'skill_id');
    }
}
