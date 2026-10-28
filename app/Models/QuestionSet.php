<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionSet extends Model
{
    use HasFactory;

    protected $table = 'question_set';

    protected $primaryKey = 'question_set_id';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'stimulus_text',
        'media_url',
        'media_type',
        'transcript',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'question_set_id', 'question_set_id');
    }
}
