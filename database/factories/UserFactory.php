<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_name' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password_hash' => 'password',
            'full_name' => fake()->name(),
            'avatar_url' => null,
            'role' => 'user',
            'energy' => 5,
            'created_at' => now(),
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'email_verified_at' => now(),
            'status' => 'ACTIVE',
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => 'admin',
            'full_name' => 'Nguyễn Admin',
        ]);
    }

    public function locked(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'LOCKED',
            'locked_until' => now()->addDay(),
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }
}
