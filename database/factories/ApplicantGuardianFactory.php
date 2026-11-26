<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GuardianRelationship;
use App\Models\Applicant;
use App\Models\ApplicantGuardian;
use App\Support\NikManager;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

/**
 * @extends Factory<ApplicantGuardian>
 */
class ApplicantGuardianFactory extends Factory
{
    protected $model = ApplicantGuardian::class;

    public function definition(): array
    {
        $nik = fake()->numerify('3573010101######');

        return [
            'applicant_id' => Applicant::factory(),
            'relationship_type' => GuardianRelationship::FATHER,
            'name' => fake()->name('male'),
            'nik' => null,
            'nik_encrypted' => Crypt::encryptString($nik),
            'nik_lookup_hash' => NikManager::hash($nik),
            'birth_place' => fake()->city(),
            'birth_date' => fake()->dateTimeBetween('1975-01-01', '1995-12-31')->format('Y-m-d'),
            'education' => fake()->randomElement(['S1', 'D3', 'SMA/SMK', 'SMP']),
            'occupation' => fake()->randomElement(['PNS/ASN', 'Karyawan Swasta', 'Wiraswasta', 'Guru', 'TNI/Polri']),
            'monthly_income' => fake()->randomElement(['Rp 3.000.000 - Rp 5.000.000', 'Rp 5.000.000 - Rp 10.000.000', '> Rp 10.000.000']),
            'phone' => fake()->numerify('0812########'),
            'is_alive' => true,
            'status_label' => 'Masih Hidup',
            'residence' => 'Kota Madiun',
            'address_line' => 'Jl. Sitinggil No. 1',
            'rt' => '001',
            'rw' => '002',
            'province' => 'Jawa Timur',
            'city' => 'Kota Madiun',
            'kecamatan' => 'Taman',
            'kelurahan' => 'Demangan',
            'postal_code' => '63136',
            'residence_status' => 'Rumah Orangtua',
        ];
    }

    public function father(): static
    {
        return $this->state(fn () => [
            'relationship_type' => GuardianRelationship::FATHER,
            'name' => fake()->name('male'),
        ]);
    }

    public function mother(): static
    {
        return $this->state(fn () => [
            'relationship_type' => GuardianRelationship::MOTHER,
            'name' => fake()->name('female'),
        ]);
    }

    public function guardian(): static
    {
        return $this->state(fn () => [
            'relationship_type' => GuardianRelationship::GUARDIAN,
        ]);
    }
}
