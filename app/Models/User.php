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
    'remember_token',
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
     * Schema dumps do not include updated_at.
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
        return $query->where('status', 'ACTIVE')
            ->where(function (Builder $builder): void {
                $builder->whereNull('locked_until')
                    ->orWhere('locked_until', '<=', now());
            });
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

        return mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr(end($words), 0, 1));
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

        return match (Str::upper((string) $this->status)) {
            'PENDING' => 'Chờ kích hoạt',
            'ACTIVE' => 'Hoạt động',
            default => 'Không xác định',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->isLocked()) {
            return 'status-badge-locked';
        }

        return match (Str::upper((string) $this->status)) {
            'PENDING' => 'status-badge-pending',
            'ACTIVE' => 'status-badge-active',
            default => 'status-badge-locked',
        };
    }
}
