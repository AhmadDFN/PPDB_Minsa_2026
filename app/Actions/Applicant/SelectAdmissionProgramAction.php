<?php

declare(strict_types=1);

namespace App\Actions\Applicant;

use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Applicant;
use App\Models\Registration;
use InvalidArgumentException;

class SelectAdmissionProgramAction
{
    /**
     * Persist applicant's program choice into a DRAFT registration for the active admission period.
     *
     * @throws InvalidArgumentException
     */
    public function execute(Applicant $applicant, int $admissionPeriodProgramId, AdmissionPeriod $activePeriod): Registration
    {
        if (! $activePeriod->isOpen()) {
            throw new InvalidArgumentException('Periode pendaftaran sedang ditutup.');
        }

        // 1. Verify that the selected period-program belongs to the active period and is open
        /** @var AdmissionPeriodProgram|null $periodProgram */
        $periodProgram = AdmissionPeriodProgram::query()
            ->where('id', $admissionPeriodProgramId)
            ->where('admission_period_id', $activePeriod->id)
            ->where('is_active', true)
            ->whereHas('program', fn ($query) => $query->where('is_active', true))
            ->with('program')
            ->first();

        if (! $periodProgram) {
            throw new InvalidArgumentException('Program pendidikan yang dipilih tidak tersedia atau tidak aktif pada periode ini.');
        }

        // 2. Find or create registration draft for this applicant in this period
        $registration = Registration::firstOrNew([
            'applicant_id' => $applicant->id,
            'admission_period_id' => $activePeriod->id,
        ]);

        if ($registration->exists && $registration->isSubmitted()) {
            throw new InvalidArgumentException('Program tidak dapat diubah setelah pendaftaran dikirim.');
        }

        $registration->admission_period_program_id = $periodProgram->id;

        // Set status to DRAFT if newly created
        if (! $registration->exists) {
            $registration->status = RegistrationStatus::DRAFT;
            $registration->data_revision = 1;
        }

        $registration->save();

        return $registration->fresh(['admissionPeriodProgram.program']);
    }
}
