<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'test_form_id',
    'session_type',
    'started_at',
    'ended_at',
    'submitted_at',
    'status',
])]
class TestAttempt extends Model
{
    public const SESSION_TYPE_PRACTICE = 'PRACTICE';

    public const SESSION_TYPE_PRACTICE_PART = 'PRACTICE_PART';

    public const SESSION_TYPE_FULL_TEST = 'FULL_TEST';

    public const SESSION_TYPE_EXAM = 'EXAM';

    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';

    public const STATUS_COMPLETED = 'COMPLETED';

    public $timestamps = false;

    protected $table = 'attempts';

    protected $primaryKey = 'attempt_id';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * @return BelongsTo<TestForm, $this>
     */
    public function testForm(): BelongsTo
    {
        return $this->belongsTo(TestForm::class, 'test_form_id', 'test_form_id');
    }

    /**
     * @return HasOne<ScoreResult, $this>
     */
    public function scoreResult(): HasOne
    {
        return $this->hasOne(ScoreResult::class, 'attempt_id', 'attempt_id');
    }

    /**
     * @return HasMany<AttemptResponse, $this>
     */
    public function attemptResponses(): HasMany
    {
        return $this->hasMany(AttemptResponse::class, 'attempt_id', 'attempt_id');
    }

    /**
     * @return HasMany<AttemptResponse, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(AttemptResponse::class, 'attempt_id', 'attempt_id');
    }
}
