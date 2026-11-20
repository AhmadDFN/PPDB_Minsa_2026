<?php

declare(strict_types=1);

namespace App\Actions\Registration;

use App\Enums\AdmissionPeriodStatus;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Program;
use App\Models\Registration;
use App\Models\RegistrationLog;
use App\Models\User;
use App\Services\Registration\RegistrationNumberGeneratorService;
use App\Services\Registration\RegistrationReadinessService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SubmitRegistrationAction
{
    public function __construct(
        private RegistrationReadinessService $readinessService,
        private RegistrationNumberGeneratorService $numberGenerator
    ) {}

    public function execute(
        User $user,
        string $acceptedByName,
        ?string $ipAddress,
        ?string $userAgent
    ): Registration {
        return DB::transaction(function () use (
            $user,
            $acceptedByName,
            $ipAddress,
            $userAgent
        ): Registration {
            $applicant = $user->applicant()->firstOrFail();
            $candidate = $applicant->registrations()->latest('id')->first();

            if ($candidate?->isSubmitted()) {
                return $candidate;
            }

            if ($candidate === null) {
                throw ValidationException::withMessages([
                    'registration' => 'Pilih program pada periode pendaftaran sebelum mengirim formulir.',
                ]);
            }

            $period = AdmissionPeriod::query()
                ->whereKey($candidate->admission_period_id)
                ->lockForUpdate()
                ->firstOrFail();

            $registration = $applicant->registrations()
                ->whereKey($candidate->id)
                ->lockForUpdate()
                ->with(['admissionPeriodProgram.program', 'consent'])
                ->firstOrFail();

            if ($registration->isSubmitted()) {
                return $registration;
            }

            if ($registration->status !== RegistrationStatus::DRAFT) {
                throw ValidationException::withMessages([
                    'registration' => 'Status pendaftaran tidak dapat dikirim.',
                ]);
            }

            if (
                ! $period->isOpen()
                || $period->status !== AdmissionPeriodStatus::ACTIVE
                || ! $period->is_active
            ) {
                throw ValidationException::withMessages([
                    'registration' => 'Pendaftaran hanya dapat dikirim selama periode pendaftaran dibuka.',
                ]);
            }

            $periodProgram = AdmissionPeriodProgram::query()
                ->whereKey($registration->admission_period_program_id)
                ->where('admission_period_id', $period->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if ($periodProgram === null) {
                throw ValidationException::withMessages([
                    'registration' => 'Program yang dipilih sudah tidak dibuka pada periode ini.',
                ]);
            }

            $program = Program::query()
                ->whereKey($periodProgram->program_id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if ($program === null) {
                throw ValidationException::withMessages([
                    'registration' => 'Program yang dipilih sudah tidak aktif.',
                ]);
            }

            $registration->setRelation('admissionPeriod', $period);
            $registration->setRelation('admissionPeriodProgram', $periodProgram);
            $periodProgram->setRelation('program', $program);

            $readiness = $this->readinessService->assess($applicant, $registration, true);
            if (! $readiness['is_complete']) {
                throw ValidationException::withMessages([
                    'registration' => $readiness['blocking_messages'],
                ]);
            }

            $consentStatement = trim((string) config('ppdb.registration.consent_statement'));
            $consentVersion = trim((string) config('ppdb.registration.consent_version'));
            if ($consentStatement === '' || $consentVersion === '') {
                throw new RuntimeException('Pernyataan dan versi persetujuan pendaftaran belum dikonfigurasi.');
            }

            if (empty($registration->registration_number)) {
                $registration->registration_number = $this->numberGenerator->generateForPeriod($period);
            }

            $registration->consent()->updateOrCreate(
                ['registration_id' => $registration->id],
                [
                    'statement_text' => $consentStatement,
                    'consent_version' => $consentVersion,
                    'accepted_by_name' => trim($acceptedByName),
                    'is_agreed' => true,
                    'agreed_at' => now(),
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                ],
            );

            $registration->status = RegistrationStatus::SUBMITTED;
            $registration->submitted_at = now();
            $registration->save();

            RegistrationLog::query()->create([
                'registration_id' => $registration->id,
                'user_id' => $user->id,
                'action' => 'submitted',
                'from_revision' => $registration->data_revision,
                'to_revision' => $registration->data_revision,
                'changes' => [
                    'from_status' => RegistrationStatus::DRAFT->value,
                    'to_status' => RegistrationStatus::SUBMITTED->value,
                ],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'created_at' => now(),
            ]);

            return $registration->fresh([
                'applicant',
                'admissionPeriod',
                'admissionPeriodProgram.program',
                'consent',
            ]);
        });
    }
}
