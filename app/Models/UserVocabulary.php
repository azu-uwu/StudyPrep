<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'vocabulary_id',
    'learned_at',
    'is_done',
])]
class UserVocabulary extends Model
{
    public $timestamps = false;

    protected $table = 'user_vocabulary';

    protected $primaryKey = 'user_voca_id';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'learned_at' => 'datetime',
            'is_done' => 'boolean',
        ];
    }

    /**
     * Học viên sở hữu bản ghi tiến độ này.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Từ vựng mà bản ghi tiến độ này theo dõi.
     *
     * @return BelongsTo<Vocabulary, $this>
     */
    public function vocabulary(): BelongsTo
    {
        return $this->belongsTo(Vocabulary::class, 'vocabulary_id', 'vocabulary_id');
    }
}
