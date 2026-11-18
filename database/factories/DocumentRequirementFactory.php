<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AdmissionPeriod;
use App\Models\DocumentRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentRequirement>
 */
class DocumentRequirementFactory extends Factory
{
    protected $model = DocumentRequirement::class;

    public function definition(): array
    {
        return [
            'admission_period_id' => AdmissionPeriod::factory(),
            'program_id' => null, // applies to all programs
            'code' => fake()->unique()->lexify('DOC_????'),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'is_required' => true,
            'allowed_mimes' => 'application/pdf,image/jpeg,image/png',
            'max_size_kb' => 5120,
            'sort_order' => 1,
        ];
    }
}
