<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfImportStagingQuestion extends Model
{
    public const VALIDATION_VALID = 'VALID';

    public const VALIDATION_NEEDS_REVIEW = 'NEEDS_REVIEW';

    public const VALIDATION_ERROR = 'ERROR';

    protected $table = 'pdf_import_staging_questions';

    protected $fillable = [
        'import_session_id',
        'question_number',
        'part_code',
        'group_key',
        'group_title',
        'stimulus_text',
        'content',
        'options_json',
        'correct_answer',
        'transcript',
        'explanation',
        'media_url',
        'validation_status',
        'validation_issues_json',
    ];

    protected $casts = [
        'options_json' => 'array',
        'validation_issues_json' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(PdfImportSession::class, 'import_session_id', 'import_session_id');
    }
}
