<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Models\AdmissionPeriodProgram;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RegistrationNumberGeneratorService
{
    public function next(AdmissionPeriodProgram $periodProgram): string
    {
        $periodProgram->loadMissing(['admissionPeriod', 'program']);

        $prefix = trim((string) config('ppdb.registration.number_prefix'));
        $academicYear = str_replace('/', '-', trim((string) $periodProgram->admissionPeriod?->academic_year));
        $programCode = strtoupper(trim((string) $periodProgram->program?->code));

        if (
            $prefix === ''
            || preg_match('/\A[A-Za-z0-9-]+\z/', $prefix) !== 1
            || preg_match('/\A[0-9-]+\z/', $academicYear) !== 1
            || preg_match('/\A[A-Z0-9-]+\z/', $programCode) !== 1
        ) {
            throw new RuntimeException('Konfigurasi nomor pendaftaran atau program tidak valid.');
        }

        DB::table('registration_sequences')->insertOrIgnore([
            'admission_period_program_id' => $periodProgram->id,
            'last_sequence' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sequence = DB::table('registration_sequences')
            ->where('admission_period_program_id', $periodProgram->id)
            ->lockForUpdate()
            ->first();

        if ($sequence === null) {
            throw new RuntimeException('Urutan nomor pendaftaran tidak dapat dikunci.');
        }

        $nextSequence = (int) $sequence->last_sequence + 1;
        DB::table('registration_sequences')
            ->where('id', $sequence->id)
            ->update([
                'last_sequence' => $nextSequence,
                'updated_at' => now(),
            ]);

        $padding = max(1, (int) config('ppdb.registration.sequence_padding', 5));
        $number = sprintf(
            '%s/%s/%s/%s',
            $prefix,
            $academicYear,
            $programCode,
            str_pad((string) $nextSequence, $padding, '0', STR_PAD_LEFT),
        );

        if (strlen($number) > 50) {
            throw new RuntimeException('Nomor pendaftaran melebihi panjang maksimum yang diizinkan.');
        }

        return $number;
    }
}
