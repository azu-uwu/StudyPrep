<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'exam_type',
    'version',
    'total_time',
    'status',
])]
class TestBlueprint extends Model
{
    protected $table = 'test_blueprint';

    protected $primaryKey = 'blueprint_id';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_time' => 'integer',
        ];
    }

    /**
     * @return HasMany<TestForm, $this>
     */
    public function testForms(): HasMany
    {
        return $this->hasMany(TestForm::class, 'blueprint_id', 'blueprint_id');
    }
}
