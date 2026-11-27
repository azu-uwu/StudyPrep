<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PdfImportSession extends Model
{
    public const STATUS_PARSING = 'PARSING';

    public const STATUS_READY_FOR_REVIEW = 'READY_FOR_REVIEW';

    public const STATUS_COMMITTED = 'COMMITTED';

    public const STATUS_FAILED = 'FAILED';

    protected $table = 'pdf_import_sessions';

    protected $primaryKey = 'import_session_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'import_session_id',
        'admin_user_id',
        'question_file_name',
        'question_pdf_path',
        'answer_file_name',
        'answer_pdf_path',
        'import_scope',
        'target_test_id',
        'publish_status',
        'status',
        'summary_json',
        'validation_errors_json',
        'committed_at',
    ];

    protected $casts = [
        'summary_json' => 'array',
        'validation_errors_json' => 'array',
        'committed_at' => 'datetime',
    ];

    public function stagingQuestions(): HasMany
    {
        return $this->hasMany(PdfImportStagingQuestion::class, 'import_session_id', 'import_session_id')
            ->orderBy('question_number');
    }
}
