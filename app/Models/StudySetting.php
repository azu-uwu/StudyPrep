<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudySetting extends Model
{
    protected $table = 'study_settings';

    protected $fillable = [
        'user_id',
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array',
    ];
}
