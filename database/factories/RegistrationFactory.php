<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Applicant;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        $period = AdmissionPeriod::factory()->create();
        $periodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
        ]);

        return [
            'registration_number' => null, // Draft has null registration_number
            'applicant_id' => Applicant::factory(),
            'admission_period_id' => $period->id,
            'admission_period_program_id' => $periodProgram->id,
            'status' => RegistrationStatus::DRAFT,
            'submitted_at' => null,
            'data_revision' => 1,
            'notes' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn () => [
            'registration_number' => 'PPDB-'.date('Y').'-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => RegistrationStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'registration_number' => null,
            'status' => RegistrationStatus::DRAFT,
            'submitted_at' => null,
        ]);
    }
}
