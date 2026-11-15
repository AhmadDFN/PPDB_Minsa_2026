<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PreviousEducationLevel;
use App\Models\Applicant;
use App\Models\ApplicantEducation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicantEducation>
 */
class ApplicantEducationFactory extends Factory
{
    protected $model = ApplicantEducation::class;

    public function definition(): array
    {
        return [
            'applicant_id' => Applicant::factory(),
            'education_level' => fake()->randomElement([PreviousEducationLevel::TK, PreviousEducationLevel::RA, PreviousEducationLevel::PAUD]),
            'school_name' => fake()->randomElement(['TK Al-Irsyad Kota Madiun', 'RA Perwanida', 'TK Pembina', 'TK Pertiwi']),
            'npsn' => fake()->numerify('########'),
            'city' => 'Kota Madiun',
        ];
    }
}
