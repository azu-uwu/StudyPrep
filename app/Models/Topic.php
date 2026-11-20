<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'name',
    'description',
    'parent_topic_id',
])]
class Topic extends Model
{
    public $timestamps = false;

    protected $table = 'topics';

    protected $primaryKey = 'topic_id';

    public function isSystem(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Chủ đề dùng chung của hệ thống (user_id IS NULL).
     *
     * @param  Builder<Topic>  $query
     */
    #[Scope]
    protected function system(Builder $query): void
    {
        $query->whereNull('user_id');
    }

    /**
     * Chủ đề cá nhân của một học viên cụ thể.
     *
     * @param  Builder<Topic>  $query
     */
    #[Scope]
    protected function ownedBy(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function vocabularies(): HasMany
    {
        return $this->hasMany(Vocabulary::class, 'topic_id', 'topic_id');
    }
}
