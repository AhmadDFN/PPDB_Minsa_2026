<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AdmissionPeriodStatus;
use App\Models\AdmissionPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdmissionPeriod>
 */
class AdmissionPeriodFactory extends Factory
{
    protected $model = AdmissionPeriod::class;

    public function definition(): array
    {
        return [
            'name' => 'PPDB MIN 1 Kota Madiun 2026/2027',
            'academic_year' => '2026/2027',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(25),
            'age_reference_date' => '2026-07-01',
            'status' => AdmissionPeriodStatus::DRAFT,
            'is_active' => false,
            'description' => 'Penerimaan Peserta Didik Baru Tahun Ajaran 2026/2027.',
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => AdmissionPeriodStatus::CLOSED,
            'is_active' => false,
            'end_date' => now()->subDay(),
        ]);
    }
}
