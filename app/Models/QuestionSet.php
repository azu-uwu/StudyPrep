<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionSet extends Model
{
    use HasFactory;

    protected $table = 'question_set';

    protected $primaryKey = 'question_set_id';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'stimulus_text',
        'media_url',
        'audio_url',
        'media_type',
        'transcript',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'resolved_media_url',
        'resolved_audio_url',
    ];

    /**
     * Chuẩn hóa mọi định dạng đường dẫn media cũ/mới (/storage/..., storage/..., https://...)
     * để hiển thị ổn định trên cả domain gốc lẫn thư mục con (VD: http://localhost/StudyPrep/public/).
     */
    public function getResolvedMediaUrlAttribute(): ?string
    {
        return $this->normalizePublicAssetUrl($this->media_url);
    }

    /**
     * Chuẩn hóa đường dẫn file âm thanh MP3 (audio_url), có fallback tương thích dữ liệu cũ nếu audio từng lưu ở media_url.
     */
    public function getResolvedAudioUrlAttribute(): ?string
    {
        $audioRaw = trim((string) ($this->audio_url ?? ''));
        if ($audioRaw !== '') {
            return $this->normalizePublicAssetUrl($audioRaw);
        }

        $mediaRaw = trim((string) ($this->media_url ?? ''));
        if ($mediaRaw !== '') {
            $isAudioLegacy = strtoupper((string) ($this->media_type ?? '')) === 'AUDIO'
                || preg_match('/\.(mp3|wav|ogg|m4a|webm)$/i', $mediaRaw) === 1;
            if ($isAudioLegacy) {
                return $this->normalizePublicAssetUrl($mediaRaw);
            }
        }

        return null;
    }

    private function normalizePublicAssetUrl(?string $value): ?string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^(?:https?:\/\/|data:)/i', $raw) === 1) {
            return $raw;
        }

        return asset(ltrim($raw, '/'));
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'question_set_id', 'question_set_id');
    }
}
