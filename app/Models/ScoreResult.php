<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'attempt_id',
    'listening_score',
    'reading_score',
    'speaking_score',
    'writing_score',
    'total_score',
    'graded_at',
])]
class ScoreResult extends Model
{
    public $timestamps = false;

    protected $table = 'score_result';

    protected $primaryKey = 'result_id';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'listening_score' => 'decimal:2',
            'reading_score' => 'decimal:2',
            'speaking_score' => 'decimal:2',
            'writing_score' => 'decimal:2',
            'total_score' => 'decimal:2',
            'graded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<TestAttempt, $this>
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class, 'attempt_id', 'attempt_id');
    }
}
