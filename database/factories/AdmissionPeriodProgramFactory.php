<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdmissionPeriodProgram>
 */
class AdmissionPeriodProgramFactory extends Factory
{
    protected $model = AdmissionPeriodProgram::class;

    public function definition(): array
    {
        return [
            'admission_period_id' => AdmissionPeriod::factory(),
            'program_id' => Program::factory(),
            'is_active' => true,
            'notes' => null,
        ];
    }
}
