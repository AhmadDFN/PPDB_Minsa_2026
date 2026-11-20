<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RegistrationNumberGeneratorService
{
    /**
     * Generate 5-digit YYNNN registration number for an admission period.
     * Sequence runs together across all programs (Reguler & Unggulan) for each intake year.
     * Starts at 001 and maxes out at 999.
     *
     * @throws RuntimeException
     */
    public function generateForPeriod(AdmissionPeriod $period): string
    {
        $intakeYear = $this->extractIntakeYear($period);

        DB::table('yearly_registration_sequences')->insertOrIgnore([
            'intake_year' => $intakeYear,
            'last_sequence' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /** @var object{id: int, intake_year: string, last_sequence: int}|null $sequence */
        $sequence = DB::table('yearly_registration_sequences')
            ->where('intake_year', $intakeYear)
            ->lockForUpdate()
            ->first();

        if ($sequence === null) {
            throw new RuntimeException('Urutan nomor pendaftaran tidak dapat dikunci.');
        }

        $nextSequence = (int) $sequence->last_sequence + 1;
        if ($nextSequence > 999) {
            throw new RuntimeException('Urutan nomor pendaftaran telah mencapai batas maksimal (999). Silakan hubungi panitia PPDB.');
        }

        DB::table('yearly_registration_sequences')
            ->where('id', $sequence->id)
            ->update([
                'last_sequence' => $nextSequence,
                'updated_at' => now(),
            ]);

        $formattedNumber = $intakeYear.str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);

        return $formattedNumber;
    }

    /**
     * Extract 2-digit intake year prefix from an admission period.
     * For example, '2026/2027' produces prefix '27'.
     */
    public function extractIntakeYear(AdmissionPeriod $period): string
    {
        $academicYear = trim((string) $period->academic_year);

        if ($academicYear === '') {
            return date('y');
        }

        $parts = preg_split('[/\\-]', $academicYear);
        $targetYear = ! empty($parts) ? (string) end($parts) : (string) date('Y');
        $cleanDigits = preg_replace('/\D/', '', $targetYear) ?? '';

        if (strlen($cleanDigits) >= 2) {
            return substr($cleanDigits, -2);
        }

        return date('y');
    }

    /**
     * Legacy generator method for backwards compatibility with existing multi-token format.
     */
    public function next(AdmissionPeriodProgram $periodProgram): string
    {
        $periodProgram->loadMissing(['admissionPeriod', 'program']);

        $prefix = trim((string) config('ppdb.registration.number_prefix'));
        $academicYearValue = trim((string) $periodProgram->admissionPeriod?->academic_year);
        $academicYear = str_replace('/', '-', $academicYearValue);
        $programCode = strtoupper(trim((string) $periodProgram->program?->code));
        $allowedProgramCodes = config('ppdb.registration.program_codes', []);
        $format = (string) config('ppdb.registration.number_format');
        $padding = (int) config('ppdb.registration.sequence_padding', 5);
        $tokens = ['{prefix}', '{academic_year}', '{program_code}', '{sequence}'];

        if (
            $prefix === ''
            || preg_match('/\A[A-Za-z0-9-]+\z/', $prefix) !== 1
            || preg_match('/\A\d{4}-\d{4}\z/', $academicYear) !== 1
            || preg_match('/\A[A-Z0-9-]+\z/', $programCode) !== 1
            || ! in_array($programCode, $allowedProgramCodes, true)
            || $padding !== 5
            || collect($tokens)->contains(fn (string $token): bool => substr_count($format, $token) !== 1)
        ) {
            throw new RuntimeException('Konfigurasi nomor pendaftaran atau program tidak valid.');
        }

        DB::table('registration_number_sequences')->insertOrIgnore([
            'academic_year' => $academicYear,
            'program_id' => $periodProgram->program_id,
            'last_sequence' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sequence = DB::table('registration_number_sequences')
            ->where('academic_year', $academicYear)
            ->where('program_id', $periodProgram->program_id)
            ->lockForUpdate()
            ->first();

        if ($sequence === null) {
            throw new RuntimeException('Urutan nomor pendaftaran tidak dapat dikunci.');
        }

        $nextSequence = (int) $sequence->last_sequence + 1;
        if ($nextSequence > 99999) {
            throw new RuntimeException('Urutan nomor pendaftaran telah mencapai batas lima digit.');
        }

        DB::table('registration_number_sequences')
            ->where('id', $sequence->id)
            ->update([
                'last_sequence' => $nextSequence,
                'updated_at' => now(),
            ]);

        $number = strtr($format, [
            '{prefix}' => $prefix,
            '{academic_year}' => $academicYear,
            '{program_code}' => $programCode,
            '{sequence}' => str_pad((string) $nextSequence, $padding, '0', STR_PAD_LEFT),
        ]);

        if (strlen($number) > 50) {
            throw new RuntimeException('Nomor pendaftaran melebihi panjang maksimum yang diizinkan.');
        }

        return $number;
    }
}
