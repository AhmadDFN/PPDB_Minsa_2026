<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

/**
 * @extends Factory<Applicant>
 */
class ApplicantFactory extends Factory
{
    protected $model = Applicant::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => fake()->name(),
            'gender' => fake()->randomElement([Gender::MALE, Gender::FEMALE]),
            'birth_place' => fake()->city(),
            // Candidate for 1st grade MI: around 6-7 years old in 2026
            'birth_date' => fake()->dateTimeBetween('2019-01-01', '2020-07-01')->format('Y-m-d'),
            'student_phone' => fake()->numerify('08##########'),
            'nisn' => fake()->numerify('##########'),
            'child_order' => fake()->numberBetween(1, 9),
            'siblings_count' => fake()->numberBetween(1, 9),
            'family_card_number_encrypted' => Crypt::encryptString(fake()->numerify('################')),
            'social_assistance_number_encrypted' => Crypt::encryptString('Tidak Ada'),
            'religion' => 'Islam',
            'hobby' => 'Membaca',
            'aspiration' => 'Guru/Dosen',
            'residence_status' => 'Rumah orang tua',
            'travel_time' => '10 - 19 menit',
            'transportation' => 'Jalan Kaki',
        ];
    }
}
