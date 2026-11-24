<?php

declare(strict_types=1);

namespace App\Actions\Registration;

use App\Enums\DocumentVerificationStatus;
use App\Models\DocumentRequirement;
use App\Models\InterviewBatch;
use App\Models\Registration;
use App\Models\RegistrationLog;
use App\Models\User;
use App\Services\Communication\CommunicationMessageTemplateService;
use App\Services\Communication\WhatsAppProviderResolver;
use App\Services\Registration\RegistrationNumberGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApproveAndAssignQueueNumberAction
{
    public function __construct(
        private RegistrationNumberGeneratorService $numberGenerator,
        private WhatsAppProviderResolver $whatsAppResolver,
        private CommunicationMessageTemplateService $templateService
    ) {}

    /**
     * Approve registration, assign official queue number, map interview batch, and notify via WhatsApp.
     *
     * @throws ValidationException
     */
    public function execute(Registration $registration, User $adminUser, ?string $ipAddress = null, ?string $userAgent = null): Registration
    {
        return DB::transaction(function () use ($registration, $adminUser, $ipAddress, $userAgent): Registration {
            /** @var Registration $lockedRegistration */
            $lockedRegistration = Registration::query()
                ->whereKey($registration->id)
                ->lockForUpdate()
                ->with(['applicant.user', 'admissionPeriod', 'admissionPeriodProgram.program', 'documents.requirement'])
                ->firstOrFail();

            if (! $lockedRegistration->isSubmitted()) {
                throw ValidationException::withMessages([
                    'status' => 'Pendaftaran hanya dapat disahkan jika berstatus SUBMITTED.',
                ]);
            }

            if ($lockedRegistration->isVerified()) {
                return $lockedRegistration;
            }

            $period = $lockedRegistration->admissionPeriod;
            if (! $period) {
                throw ValidationException::withMessages([
                    'period' => 'Periode pendaftaran tidak ditemukan.',
                ]);
            }

            // 1. Validasi kelengkapan dan persetujuan seluruh dokumen persyaratan wajib
            $requiredRequirements = DocumentRequirement::query()
                ->where('admission_period_id', $period->id)
                ->where('is_required', true)
                ->where(function ($query) use ($lockedRegistration): void {
                    $query->whereNull('program_id')
                        ->orWhere('program_id', $lockedRegistration->admissionPeriodProgram?->program_id);
                })
                ->get();

            $approvedDocReqIds = $lockedRegistration->documents
                ->filter(fn ($doc): bool => $doc->verification_status === DocumentVerificationStatus::APPROVED)
                ->pluck('document_requirement_id')
                ->all();

            foreach ($requiredRequirements as $req) {
                if (! in_array($req->id, $approvedDocReqIds, true)) {
                    throw ValidationException::withMessages([
                        'documents' => "Dokumen wajib [{$req->name}] belum berstatus APPROVED. Seluruh dokumen wajib harus disetujui sebelum mengesahkan pendaftaran.",
                    ]);
                }
            }

            // 2. Terbitkan nomor urut global (Opsi B: 270001..)
            $queueData = $this->numberGenerator->generateQueueNumber($period);
            $queueSequence = $queueData['queue_sequence'];
            $registrationNumber = $queueData['registration_number'];

            // 3. Auto-mapping Interview Batch berdasarkan rentang kuota harian
            $batch = InterviewBatch::query()
                ->where('admission_period_id', $period->id)
                ->where('is_active', true)
                ->where('sequence_start', '<=', $queueSequence)
                ->where('sequence_end', '>=', $queueSequence)
                ->first();

            // 4. Update data pendaftaran
            $lockedRegistration->queue_sequence = $queueSequence;
            $lockedRegistration->registration_number = $registrationNumber;
            $lockedRegistration->interview_batch_id = $batch?->id;
            $lockedRegistration->verified_at = now();
            $lockedRegistration->verified_by = $adminUser->id;
            $lockedRegistration->save();

            // 5. Catat Audit Log
            RegistrationLog::query()->create([
                'registration_id' => $lockedRegistration->id,
                'user_id' => $adminUser->id,
                'action' => 'verified_and_queued',
                'from_revision' => $lockedRegistration->data_revision,
                'to_revision' => $lockedRegistration->data_revision,
                'changes' => [
                    'queue_sequence' => $queueSequence,
                    'registration_number' => $registrationNumber,
                    'interview_batch_id' => $batch?->id,
                    'batch_title' => $batch?->title,
                    'interview_date' => $batch?->interview_date?->toDateString(),
                ],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'created_at' => now(),
            ]);

            // 6. Kirim Notifikasi WhatsApp Fonnte (Graceful degradation)
            $this->sendWhatsAppNotification($lockedRegistration, $batch);

            return $lockedRegistration->fresh([
                'applicant.user',
                'admissionPeriod',
                'admissionPeriodProgram.program',
                'interviewBatch',
                'verifiedByAdmin',
            ]);
        });
    }

    private function sendWhatsAppNotification(Registration $registration, ?InterviewBatch $batch): void
    {
        try {
            $user = $registration->applicant?->user;
            $phone = (string) ($user?->phone ?? '');
            if ($phone === '') {
                return;
            }

            $provider = $this->whatsAppResolver->resolve();
            if (! $provider) {
                return;
            }

            $dateFormatted = $batch?->interview_date
                ? $batch->interview_date->translatedFormat('d F Y')
                : 'Menyusul';
            $timeFormatted = $batch?->start_time
                ? substr((string) $batch->start_time, 0, 5).' - '.substr((string) $batch->end_time, 0, 5).' WIB'
                : '07:30 WIB';

            $defaultMessage = "Assalamu'alaikum Wr. Wb.\n\n"
                ."Kabar gembira! Berkas pendaftaran an. *{$registration->applicant?->full_name}* telah DIVERIFIKASI dan DISETUJUI oleh Panitia PPDB MIN 1 Kota Madiun.\n\n"
                ."*Nomor Urut Pendaftaran:* {$registration->registration_number}\n"
                ."*Program:* {$registration->admissionPeriodProgram?->program?->name}\n"
                ."*Jadwal Wawancara:* {$dateFormatted} ({$timeFormatted})\n"
                ."*Sesi / Ruang:* {$batch?->title}\n\n"
                ."Silakan unduh kembali Bukti Pendaftaran Resmi ber-QR di portal pendaftaran dan bawa berkas fisik asli saat hadir di madrasah.\n\n"
                ."Wassalamu'alaikum Wr. Wb.\nPanitia PPDB MIN 1 Kota Madiun";

            $message = $this->templateService->render(
                CommunicationMessageTemplateService::DOCUMENT_APPROVED_SCHEDULE,
                [
                    'institution_name' => config('app.name', 'MIN 1 Kota Madiun'),
                    'applicant_name' => (string) ($registration->applicant?->full_name ?? ''),
                    'registration_number' => (string) $registration->registration_number,
                    'program_name' => (string) ($registration->admissionPeriodProgram?->program?->name ?? ''),
                    'batch_title' => (string) ($batch?->title ?? 'Gelombang 1'),
                    'interview_date' => $dateFormatted,
                    'interview_time' => $timeFormatted,
                ],
                $defaultMessage
            );

            $provider->sendMessage($phone, $message);
        } catch (\Throwable) {
            // Graceful degradation: failure to send WhatsApp does not rollback database verification
        }
    }
}
