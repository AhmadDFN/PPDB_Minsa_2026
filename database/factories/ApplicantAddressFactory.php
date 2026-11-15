<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\ApplicantAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicantAddress>
 */
class ApplicantAddressFactory extends Factory
{
    protected $model = ApplicantAddress::class;

    public function definition(): array
    {
        return [
            'applicant_id' => Applicant::factory(),
            'address_line' => fake()->streetAddress(),
            'rt' => str_pad((string) fake()->numberBetween(1, 20), 2, '0', STR_PAD_LEFT),
            'rw' => str_pad((string) fake()->numberBetween(1, 10), 2, '0', STR_PAD_LEFT),
            'kelurahan' => fake()->randomElement(['Demangan', 'Klegen', 'Mojorejo', 'Oro-oro Ombo', 'Taman']),
            'kecamatan' => 'Taman',
            'city' => 'Kota Madiun',
            'province' => 'Jawa Timur',
            'postal_code' => '63133',
            'distance_to_school_km' => fake()->randomFloat(2, 0.5, 12.0),
        ];
    }
}
