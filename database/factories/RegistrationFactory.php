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
        // Keep the default fixture inactive so the database-level single-active
        // invariant remains valid when a test creates multiple registrations.
        $period = AdmissionPeriod::factory()->create(['status' => 'draft', 'is_active' => false]);
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
            'registration_number' => 'PPDB/2026-2027/REGULER/'.str_pad((string) fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
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
