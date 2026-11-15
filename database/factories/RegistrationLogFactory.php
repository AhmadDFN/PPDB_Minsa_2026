<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Registration;
use App\Models\RegistrationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationLog>
 */
class RegistrationLogFactory extends Factory
{
    protected $model = RegistrationLog::class;

    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'user_id' => User::factory(),
            'action' => 'CREATED',
            'from_revision' => null,
            'to_revision' => 1,
            'changes' => ['status' => 'DRAFT'],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'created_at' => now(),
        ];
    }
}
