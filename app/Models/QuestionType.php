<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'response_mode',
])]
class QuestionType extends Model
{
    public $timestamps = false;

    protected $table = 'question_type';

    protected $primaryKey = 'question_type_id';
}
