<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function vocabularies(): HasMany
    {
        return $this->hasMany(Vocabulary::class, 'topic_id', 'topic_id');
    }
}
