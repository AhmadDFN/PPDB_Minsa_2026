<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OtpPurpose;
use App\Models\User;
use App\Models\UserOtpToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<UserOtpToken>
 */
class UserOtpTokenFactory extends Factory
{
    protected $model = UserOtpToken::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'phone' => fake()->numerify('0812########'),
            'purpose' => OtpPurpose::ACTIVATION,
            'otp_hash' => Hash::make('123456'),
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addMinutes(5),
            'verified_at' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subMinute(),
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'verified_at' => now(),
        ]);
    }

    public function exhausted(): static
    {
        return $this->state(fn () => [
            'attempts' => 5,
            'max_attempts' => 5,
        ]);
    }
}
