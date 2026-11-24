<?php

declare(strict_types=1);

namespace App\Actions\Interview;

use App\Models\AdmissionPeriod;
use App\Models\InterviewSession;
use App\Models\InterviewStage;
use App\Models\Registration;
use App\Models\RegistrationPdfVersion;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class LookupApplicantForInterviewAction
{
    /**
     * Resolve the stage assigned to the given interviewer user for the active period.
     */
    public static function resolveStageForUser(User $user, ?AdmissionPeriod $period = null): ?InterviewStage
    {
        $period = $period ?? (
            AdmissionPeriod::query()->where('is_active', true)->first()
            ?? AdmissionPeriod::query()->latest('start_date')->first()
        );

        if (! $period) {
            return null;
        }

        $targetOrder = null;
        $targetCode = null;

        if ($user->hasRole('interviewer_stage_1')) {
            $targetOrder = 1;
            $targetCode = 'STAGE_1';
        } elseif ($user->hasRole('interviewer_stage_2')) {
            $targetOrder = 2;
            $targetCode = 'STAGE_2';
        } elseif ($user->hasRole('interviewer_stage_3')) {
            $targetOrder = 3;
            $targetCode = 'STAGE_3';
        }

        if ($targetOrder === null && $targetCode === null) {
            return null;
        }

        return InterviewStage::query()
            ->where('admission_period_id', $period->id)
            ->where(function ($query) use ($targetOrder, $targetCode): void {
                $query->where('code', $targetCode)
                    ->orWhere('stage_order', $targetOrder);
            })
            ->first();
    }

    /**
     * Look up and validate a registration for an interviewer session.
     *
     * @return array{registration: Registration, stage: InterviewStage}
     *
     * @throws ValidationException
     */
    public function execute(User $interviewer, string $identifier): array
    {
        // 1. Resolve current active period and stage
        $period = AdmissionPeriod::query()->where('is_active', true)->first()
            ?? AdmissionPeriod::query()->latest('start_date')->first();

        if (! $period) {
            throw ValidationException::withMessages([
                'identifier' => 'Tidak ada periode penerimaan siswa baru yang sedang aktif.',
            ]);
        }

        $stage = self::resolveStageForUser($interviewer, $period);

        if (! $stage) {
            throw ValidationException::withMessages([
                'identifier' => 'Tahap wawancara untuk akun Anda tidak ditemukan pada periode aktif.',
            ]);
        }

        if (! $stage->is_active) {
            throw ValidationException::withMessages([
                'identifier' => 'Tahap wawancara sedang tidak aktif.',
            ]);
        }

        // 2. Resolve registration from identifier (QR URL, verification token, or queue number)
        $registration = $this->resolveRegistration($identifier, $period->id);

        if (! $registration) {
            throw ValidationException::withMessages([
                'identifier' => 'Data calon siswa dengan nomor/kode pendaftaran tersebut tidak ditemukan.',
            ]);
        }

        // 3. Validate admission period
        if ((int) $registration->admission_period_id !== (int) $stage->admission_period_id) {
            throw ValidationException::withMessages([
                'identifier' => 'Calon siswa terdaftar pada periode pendaftaran yang berbeda.',
            ]);
        }

        // 4. Validate document verification by counter staff (verified_at !== null)
        if (! $registration->isVerified()) {
            throw ValidationException::withMessages([
                'identifier' => 'Berkas calon siswa belum disahkan oleh panitia loket verifikasi.',
            ]);
        }

        $studentName = $registration->applicant?->full_name ?? 'Calon siswa';

        // 5. Check if this stage has already been completed and locked
        $alreadyCompleted = InterviewSession::query()
            ->where('registration_id', $registration->id)
            ->where('interview_stage_id', $stage->id)
            ->where('status', 'COMPLETED')
            ->exists();

        if ($alreadyCompleted) {
            throw ValidationException::withMessages([
                'identifier' => "Penilaian tahap ini untuk calon siswa {$studentName} sudah selesai dan terkunci.",
            ]);
        }

        // 6. Sequential Gate Validation
        $this->validateSequentialGate($stage, $registration, $studentName);

        return [
            'registration' => $registration,
            'stage' => $stage,
        ];
    }

    /**
     * Resolve registration record from raw input string.
     */
    protected function resolveRegistration(string $identifier, int $periodId): ?Registration
    {
        $raw = trim($identifier);

        // Check if input is a URL containing registration_number query param
        if (str_contains($raw, '?') || str_contains($raw, '&')) {
            $parsedUrl = parse_url($raw);
            if (isset($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $queryParams);
                if (! empty($queryParams['registration_number'])) {
                    $reg = Registration::query()
                        ->where('admission_period_id', $periodId)
                        ->where('registration_number', trim((string) $queryParams['registration_number']))
                        ->first();
                    if ($reg) {
                        return $reg;
                    }
                }
            }
        }

        // Check if input contains QR verification token URL or raw 64-char hex token
        if (preg_match('/([a-f0-9]{64})/i', $raw, $matches)) {
            $token = $matches[1];
            $pdfVersion = RegistrationPdfVersion::query()
                ->where('verification_token_hash', hash('sha256', $token))
                ->with('registration')
                ->first();

            if ($pdfVersion?->registration) {
                return $pdfVersion->registration;
            }
        }

        // Otherwise, treat as queue number (e.g. "270001", "27-0001")
        $cleanNumber = preg_replace('/[^0-9]/', '', $raw);

        if (! empty($cleanNumber)) {
            $reg = Registration::query()
                ->where('admission_period_id', $periodId)
                ->where('registration_number', $cleanNumber)
                ->first();

            if ($reg) {
                return $reg;
            }
        }

        // Exact match fallback
        return Registration::query()
            ->where('admission_period_id', $periodId)
            ->where('registration_number', $raw)
            ->first();
    }

    /**
     * Validate sequential gate constraints based on stage.
     *
     * @throws ValidationException
     */
    protected function validateSequentialGate(InterviewStage $currentStage, Registration $registration, string $studentName): void
    {
        $stageOrder = $currentStage->stage_order;

        // Stage 1 has no prerequisites
        if ($stageOrder === 1 || $currentStage->code === 'STAGE_1') {
            return;
        }

        // Stage 2 requires Stage 1 COMPLETED
        if ($stageOrder === 2 || $currentStage->code === 'STAGE_2') {
            $stage1 = InterviewStage::query()
                ->where('admission_period_id', $currentStage->admission_period_id)
                ->where(function ($q): void {
                    $q->where('code', 'STAGE_1')->orWhere('stage_order', 1);
                })
                ->first();

            $stage1Completed = $stage1 && InterviewSession::query()
                ->where('registration_id', $registration->id)
                ->where('interview_stage_id', $stage1->id)
                ->where('status', 'COMPLETED')
                ->exists();

            if (! $stage1Completed) {
                throw ValidationException::withMessages([
                    'identifier' => "Calon siswa belum menyelesaikan Wawancara Tahap 1 (BTQ/Iqro'). Selesaikan Tahap 1 terlebih dahulu.",
                ]);
            }

            return;
        }

        // Stage 3 (Khusus Unggulan):
        // 1. Candidate must be registered in UNGGULAN program
        $programCode = strtoupper((string) ($registration->admissionPeriodProgram?->program?->code ?? ''));
        if (! str_contains($programCode, 'UNGGULAN')) {
            throw ValidationException::withMessages([
                'identifier' => 'Calon siswa terdaftar pada Program Reguler dan tidak dijadwalkan untuk Wawancara Tahap 3.',
            ]);
        }

        // 2. Both Stage 1 and Stage 2 must be COMPLETED
        $stage1 = InterviewStage::query()
            ->where('admission_period_id', $currentStage->admission_period_id)
            ->where(function ($q): void {
                $q->where('code', 'STAGE_1')->orWhere('stage_order', 1);
            })
            ->first();

        $stage2 = InterviewStage::query()
            ->where('admission_period_id', $currentStage->admission_period_id)
            ->where(function ($q): void {
                $q->where('code', 'STAGE_2')->orWhere('stage_order', 2);
            })
            ->first();

        $stage1Completed = $stage1 && InterviewSession::query()
            ->where('registration_id', $registration->id)
            ->where('interview_stage_id', $stage1->id)
            ->where('status', 'COMPLETED')
            ->exists();

        $stage2Completed = $stage2 && InterviewSession::query()
            ->where('registration_id', $registration->id)
            ->where('interview_stage_id', $stage2->id)
            ->where('status', 'COMPLETED')
            ->exists();

        if (! $stage1Completed || ! $stage2Completed) {
            throw ValidationException::withMessages([
                'identifier' => 'Calon siswa belum menyelesaikan Wawancara Tahap 1 dan/atau Tahap 2. Wawancara Tahap 3 hanya dapat dilakukan setelah kedua tahap sebelumnya selesai.',
            ]);
        }
    }
}
