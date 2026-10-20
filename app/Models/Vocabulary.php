<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'topic_id',
    'word',
    'meaning_vi',
    'part_of_speech',
    'pronunciation',
    'example_en',
    'example_vi',
])]
class Vocabulary extends Model
{
    public $timestamps = false;

    protected $table = 'vocabularies';

    protected $primaryKey = 'vocabulary_id';
}
