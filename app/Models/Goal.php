<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    protected $table = 'goals';

    protected $primaryKey = 'goal_id';

    // Bảng hiện chỉ có created_at, không có updated_at
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'exam_type',
        'certificate',
        'target_listening',
        'target_reading',
        'target_speaking',
        'target_writing',
        'target_date',
        'target_date_lr',
        'target_date_sw',
    ];

    protected $casts = [
        'target_listening' => 'integer',
        'target_reading' => 'integer',
        'target_speaking' => 'integer',
        'target_writing' => 'integer',
        'target_date' => 'date',
        'target_date_lr' => 'date',
        'target_date_sw' => 'date',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'user_id'
        );
    }

    public function getTargetLRAttribute(): ?int
    {
        if (
            $this->target_listening === null ||
            $this->target_reading === null
        ) {
            return null;
        }

        return $this->target_listening
             + $this->target_reading;
    }

    public function getTargetSWAttribute(): ?int
    {
        if (
            $this->target_speaking === null ||
            $this->target_writing === null
        ) {
            return null;
        }

        return $this->target_speaking
             + $this->target_writing;
    }
}
