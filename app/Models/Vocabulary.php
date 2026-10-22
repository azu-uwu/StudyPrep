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
     * Get target level (e.g. 750+, 800+, 650+, 550+, 450+)
     */
    public function getTargetLevelAttribute(): string
    {
        $pos = (string) ($this->part_of_speech ?? '');
        if (str_contains($pos, '|')) {
            $parts = explode('|', $pos);
            return trim($parts[1] ?? '650+');
        }

        // Default level mapping based on POS or word length if not specified
        $hash = abs(crc32($this->word ?? '')) % 5;
        $levels = ['450+', '550+', '650+', '750+', '800+'];
        return $levels[$hash];
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
