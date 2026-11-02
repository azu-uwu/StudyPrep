<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttemptResponse extends Model
{
    protected $table = 'attempt_response';

    protected $primaryKey = 'response_id';

    public $timestamps = false;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'text_response',
        'media_url',
        'score',
        'feedback',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'submitted_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class, 'attempt_id', 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }
}
