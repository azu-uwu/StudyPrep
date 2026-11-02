<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'topic_id',
    'word',
    'meaning_vi',
    'part_of_speech',
    'pronunciation',
    'example_en',
    'example_vi',
    'image_url',
])]
class Vocabulary extends Model
{
    public $timestamps = false;

    protected $table = 'vocabularies';

    protected $primaryKey = 'vocabulary_id';

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'topic_id');
    }

    /**
     * Get clean part of speech (extracting any embedded level if formatted as "POS|Level")
     */
    public function getCleanPosAttribute(): string
    {
        $pos = (string) ($this->part_of_speech ?? 'Từ vựng');
        if (str_contains($pos, '|')) {
            $parts = explode('|', $pos);

            return trim($parts[0]);
        }

        return $pos;
    }

    /**
     * Get the stored target level or indicate that it is unavailable.
     */
    public function getTargetLevelAttribute(): string
    {
        $pos = (string) ($this->part_of_speech ?? '');
        if (str_contains($pos, '|')) {
            $parts = explode('|', $pos);

            return trim($parts[1] ?? '650+');
        }

        return 'Chưa phân loại';
    }

    /**
     * Get primary Vietnamese meaning
     */
    public function getCleanMeaningViAttribute(): string
    {
        $meaning = (string) ($this->meaning_vi ?? '');
        if (str_contains($meaning, '||')) {
            $parts = explode('||', $meaning);

            return trim($parts[0]);
        }
        if (str_contains($meaning, "\n")) {
            $lines = explode("\n", $meaning);

            return trim($lines[0]);
        }

        return $meaning;
    }

    /**
     * Get English definition if available
     */
    public function getCleanDefinitionEnAttribute(): string
    {
        $meaning = (string) ($this->meaning_vi ?? '');
        if (str_contains($meaning, '||')) {
            $parts = explode('||', $meaning);

            return trim($parts[1] ?? '');
        }
        if (str_contains($meaning, "\n")) {
            $lines = explode("\n", $meaning);

            return trim($lines[1] ?? '');
        }

        return '';
    }
}
