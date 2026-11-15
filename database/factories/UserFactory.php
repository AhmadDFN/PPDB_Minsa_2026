<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use App\Support\NikManager;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        $nik = '3573'.str_pad((string) fake()->unique()->numberBetween(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);

        return [
            'name' => fake()->name(),
            'email' => null,
            'nik_lookup_hash' => NikManager::hash($nik),
            'nik_encrypted' => NikManager::encrypt($nik),
            'phone' => fake()->numerify('0812########'),
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'email_verified_at' => null,
            'phone_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password123'),
            'remember_token' => Str::random(10),
        ];
    }

    public function applicant(): static
    {
        return $this->state(fn () => [
            'account_type' => AccountType::APPLICANT,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'email' => fake()->unique()->safeEmail(),
            'nik_lookup_hash' => null,
            'nik_encrypted' => null,
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    public function pendingActivation(): static
    {
        return $this->state(fn () => [
            'account_status' => AccountStatus::PENDING_ACTIVATION,
            'phone_verified_at' => null,
        ]);
    }
}
