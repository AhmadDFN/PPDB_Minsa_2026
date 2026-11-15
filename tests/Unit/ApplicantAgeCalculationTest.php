<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Applicant;
use Carbon\Carbon;
use Tests\TestCase;

class ApplicantAgeCalculationTest extends TestCase
{
    public function test_exact_birthday_calculation(): void
    {
        $applicant = new Applicant([
            'birth_date' => '2019-07-01',
        ]);

        $result = $applicant->calculateAgeAt('2026-07-01');

        $this->assertSame(7, $result['years']);
        $this->assertSame(0, $result['months']);
        $this->assertSame(0, $result['days']);
        $this->assertSame('7 tahun 0 bulan 0 hari', $result['formatted']);
    }

    public function test_age_with_years_months_and_days(): void
    {
        $applicant = new Applicant([
            'birth_date' => '2019-10-15',
        ]);

        $result = $applicant->calculateAgeAt('2026-07-01');

        $this->assertSame(6, $result['years']);
        $this->assertSame(8, $result['months']);
        $this->assertSame(16, $result['days']);
        $this->assertSame('6 tahun 8 bulan 16 hari', $result['formatted']);
    }

    public function test_leap_year_birthday_calculation(): void
    {
        $applicant = new Applicant([
            'birth_date' => '2020-02-29', // Leap day
        ]);

        $result = $applicant->calculateAgeAt('2026-07-01');

        $this->assertSame(6, $result['years']);
        $this->assertSame(4, $result['months']);
        $this->assertSame(2, $result['days']);
        $this->assertSame('6 tahun 4 bulan 2 hari', $result['formatted']);
    }

    public function test_future_birth_date_relative_to_reference_date(): void
    {
        $applicant = new Applicant([
            'birth_date' => '2027-01-01',
        ]);

        $result = $applicant->calculateAgeAt('2026-07-01');

        $this->assertSame(0, $result['years']);
        $this->assertSame(0, $result['months']);
        $this->assertSame(0, $result['days']);
        $this->assertSame('0 tahun 0 bulan 0 hari', $result['formatted']);
    }

    public function test_null_birth_date_returns_default_unspecified(): void
    {
        $applicant = new Applicant([
            'birth_date' => null,
        ]);

        $result = $applicant->calculateAgeAt('2026-07-01');

        $this->assertSame(0, $result['years']);
        $this->assertSame(0, $result['months']);
        $this->assertSame(0, $result['days']);
        $this->assertSame('Belum ditentukan', $result['formatted']);
    }

    public function test_accepts_carbon_instance_as_reference_date(): void
    {
        $applicant = new Applicant([
            'birth_date' => '2020-05-10',
        ]);

        $carbonRef = Carbon::create(2026, 7, 1);
        $result = $applicant->calculateAgeAt($carbonRef);

        $this->assertSame(6, $result['years']);
        $this->assertSame(1, $result['months']);
        $this->assertSame(21, $result['days']);
    }
}
