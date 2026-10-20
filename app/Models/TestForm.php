<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'blueprint_id',
    'title',
    'generation_seed',
])]
class TestForm extends Model
{
    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    protected $table = 'test_form';

    protected $primaryKey = 'test_form_id';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<TestAttempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class, 'test_form_id', 'test_form_id');
    }
}
