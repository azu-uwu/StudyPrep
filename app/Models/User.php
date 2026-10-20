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
}
