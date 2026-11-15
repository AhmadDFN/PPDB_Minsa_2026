<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Registration;
use App\Models\RegistrationConsent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationConsent>
 */
class RegistrationConsentFactory extends Factory
{
    protected $model = RegistrationConsent::class;

    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'statement_text' => 'Saya selaku orang tua/wali menyatakan bahwa seluruh data yang diisikan adalah benar dan dapat dipertanggungjawabkan.',
            'is_agreed' => true,
            'agreed_at' => now(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
