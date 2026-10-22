<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable([
    'user_name',
    'email',
    'password_hash',
    'full_name',
    'avatar_url',
    'role',
    'energy',
    'failed_login_attempts',
    'locked_until',
    'email_verified_at',
    'status',
])]
#[Hidden(['password_hash', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    protected $primaryKey = 'user_id';

    /**
     * Schema dumps do not include updated_at / remember_token.
     */
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'locked_until' => 'datetime',
            'created_at' => 'datetime',
            'password_hash' => 'hashed',
            'energy' => 'integer',
            'failed_login_attempts' => 'integer',
        ];
    }

    public function getAuthPassword(): string
    {
        return (string) $this->password_hash;
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function isAdmin(): bool
    {
        return in_array(Str::lower((string) $this->role), ['admin', 'super_admin'], true);
    }

    public function isLocked(): bool
    {
        if (Str::upper((string) $this->status) === 'LOCKED') {
            return true;
        }

        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE');
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    #[Scope]
    protected function locked(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where('status', 'LOCKED')
                ->orWhere(function (Builder $nested): void {
                    $nested->whereNotNull('locked_until')
                        ->where('locked_until', '>', now());
                });
        });
    }

    /**
     * @return HasMany<TestAttempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class, 'user_id', 'user_id');
    }

    public function getInitialsAttribute(): string
    {
        $name = trim((string) ($this->full_name ?: $this->user_name));
        $words = preg_split('/\s+/u', $name);
        if (empty($words) || $words[0] === '') {
            return 'US';
        }
        if (count($words) === 1) {
            return mb_strtoupper(mb_substr($words[0], 0, 2));
        }
        return mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1));
    }

    public function getAvatarBgAttribute(): string
    {
        $colors = [
            '#e0e7ff', // light indigo
            '#ccfbf1', // light teal
            '#fee2e2', // light coral
            '#ede9fe', // light purple
            '#dbeafe', // light blue
            '#fef3c7', // light amber
        ];
        return $colors[$this->user_id % count($colors)];
    }

    public function getAvatarTextColorAttribute(): string
    {
        $colors = [
            '#4338ca', // indigo
            '#0f766e', // teal
            '#b91c1c', // red
            '#6d28d9', // purple
            '#1d4ed8', // blue
            '#b45309', // amber
        ];
        return $colors[$this->user_id % count($colors)];
    }

    public function getStpIdAttribute(): string
    {
        return sprintf('#STP-%05d', $this->user_id);
    }

    public function getRoleDisplayAttribute(): string
    {
        $role = Str::lower((string) $this->role);
        return match ($role) {
            'teacher', 'instructor', 'giang_vien' => 'Giảng viên TOEIC',
            'admin', 'super_admin' => 'Quản trị viên',
            default => 'Học viên',
        };
    }

    public function getRoleBadgeClassAttribute(): string
    {
        $role = Str::lower((string) $this->role);
        return match ($role) {
            'teacher', 'instructor', 'giang_vien' => 'role-badge-teacher',
            'admin', 'super_admin' => 'role-badge-admin',
            default => 'role-badge-student',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isLocked()) {
            return 'Bị khóa';
        }
        if (Str::upper((string) $this->status) === 'PENDING' || ($this->email_verified_at === null && $this->created_at?->diffInDays() <= 7)) {
            return 'Chờ kích hoạt';
        }
        return 'Hoạt động';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->isLocked()) {
            return 'status-badge-locked';
        }
        if (Str::upper((string) $this->status) === 'PENDING' || ($this->email_verified_at === null && $this->created_at?->diffInDays() <= 7)) {
            return 'status-badge-pending';
        }
        return 'status-badge-active';
    }

    public function getPackageTitleAttribute(): string
    {
        if (in_array(Str::lower((string) $this->role), ['teacher', 'instructor', 'giang_vien'], true)) {
            return 'Writing & Speaking';
        }
        if ($this->isAdmin()) {
            return 'Hệ thống StudyPREP';
        }
        if (str_contains(Str::lower((string) $this->full_name), 'thảo')) {
            return 'TOEIC Master 800+';
        }
        if (str_contains(Str::lower((string) $this->full_name), 'uyên')) {
            return 'Học thử 7 ngày';
        }
        if (str_contains(Str::lower((string) $this->full_name), 'huy')) {
            return 'TOEIC Cấp tốc 30 ngày';
        }
        return 'TOEIC Toàn diện 650+';
    }

    public function getPackageSubtitleAttribute(): string
    {
        if (in_array(Str::lower((string) $this->role), ['teacher', 'instructor', 'giang_vien'], true)) {
            return 'Phụ trách 8 lớp học';
        }
        if ($this->isAdmin()) {
            return 'Toàn quyền quản trị';
        }
        if (str_contains(Str::lower((string) $this->full_name), 'thảo')) {
            return 'Hạn dùng: 15/12/2025';
        }
        if (str_contains(Str::lower((string) $this->full_name), 'uyên')) {
            return 'Còn 2 ngày trải nghiệm';
        }
        if (str_contains(Str::lower((string) $this->full_name), 'huy')) {
            return 'Thi ngày 28/04/2025';
        }
        return 'Hạn dùng 12 tháng';
    }

    /**
     * @return array{stat1: string, stat2: string, percent: int, color: string}
     */
    public function getProgressInfoAttribute(): array
    {
        if (in_array(Str::lower((string) $this->role), ['teacher', 'instructor', 'giang_vien'], true)) {
            return [
                'stat1' => 'Đã chấm 412 bài',
                'stat2' => 'Rating 4.9/5.0',
                'percent' => 92,
                'color' => '#059669', // emerald
            ];
        }
        if ($this->isAdmin()) {
            return [
                'stat1' => 'Vận hành hệ thống',
                'stat2' => '100% SLA',
                'percent' => 100,
                'color' => '#4338ca',
            ];
        }
        if (str_contains(Str::lower((string) $this->full_name), 'thảo')) {
            return [
                'stat1' => '745 / 990',
                'stat2' => '75% bài học',
                'percent' => 75,
                'color' => '#4338ca', // indigo
            ];
        }
        if (str_contains(Str::lower((string) $this->full_name), 'uyên')) {
            return [
                'stat1' => '490 / 990',
                'stat2' => '2 bài test',
                'percent' => 32,
                'color' => '#94a3b8', // slate/gray
            ];
        }
        if (str_contains(Str::lower((string) $this->full_name), 'huy')) {
            return [
                'stat1' => '810 / 990',
                'stat2' => '82% lộ trình',
                'percent' => 82,
                'color' => '#0d9488', // teal
            ];
        }

        return [
            'stat1' => '620 / 990',
            'stat2' => '60% lộ trình',
            'percent' => 60,
            'color' => '#4338ca',
        ];
    }

    public function getLastOnlineTextAttribute(): string
    {
        if (str_contains(Str::lower((string) $this->full_name), 'thảo')) {
            return 'Vừa xong';
        }
        if (in_array(Str::lower((string) $this->role), ['teacher', 'instructor', 'giang_vien'], true)) {
            return '10 phút trước';
        }
        if (str_contains(Str::lower((string) $this->full_name), 'uyên')) {
            return '5 ngày trước';
        }
        if (str_contains(Str::lower((string) $this->full_name), 'huy')) {
            return '2 giờ trước';
        }

        if ($this->created_at) {
            return $this->created_at->diffForHumans();
        }

        return 'Vừa xong';
    }
}
