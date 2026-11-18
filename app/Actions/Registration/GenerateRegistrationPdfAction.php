<?php

declare(strict_types=1);

namespace App\Actions\Registration;

use App\Enums\PdfDocumentType;
use App\Exceptions\RegistrationPdfGenerationException;
use App\Models\Registration;
use App\Models\RegistrationLog;
use App\Models\RegistrationPdfVersion;
use App\Models\User;
use App\Services\Registration\RegistrationPdfFileService;
use App\Services\Registration\RegistrationPdfRenderer;
use App\Services\Registration\RegistrationReadinessService;
use App\Support\NikManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class GenerateRegistrationPdfAction
{
    public function __construct(
        private RegistrationPdfRenderer $renderer,
        private RegistrationPdfFileService $fileService,
        private RegistrationReadinessService $readinessService
    ) {}

    public function execute(
        Registration $candidate,
        User $user,
        PdfDocumentType $type
    ): RegistrationPdfVersion {
        $snapshot = $this->createSnapshot($candidate, $user, $type);
        $verificationToken = null;
        $stored = false;
        $diskName = (string) config('ppdb.documents.disk');
        $filePath = '';

        try {
            if ($type === PdfDocumentType::FINAL) {
                $verificationToken = bin2hex(random_bytes(32));
            }

            $verificationUrl = $verificationToken === null
                ? null
                : $this->verificationUrl($verificationToken, $snapshot['document']['registration_number']);
            $pdfBytes = $this->renderer->render($type, $snapshot['document'], $verificationUrl);

            if (! str_starts_with($pdfBytes, '%PDF-')) {
                throw new RuntimeException('Renderer tidak menghasilkan dokumen PDF yang valid.');
            }

            $filePath = $this->filePath($candidate->id, $type);

            return DB::transaction(function () use (
                $candidate,
                $user,
                $type,
                $snapshot,
                $verificationToken,
                $pdfBytes,
                $diskName,
                $filePath,
                &$stored
            ): RegistrationPdfVersion {
                $registration = Registration::query()
                    ->whereKey($candidate->id)
                    ->whereHas('applicant', fn ($query) => $query->where('user_id', $user->id))
                    ->lockForUpdate()
                    ->firstOrFail();
                $registration->load([
                    'applicant.user',
                    'applicant.address',
                    'applicant.father',
                    'applicant.mother',
                    'applicant.guardian',
                    'applicant.education',
                    'admissionPeriod',
                    'admissionPeriodProgram.program',
                    'consent',
                ]);

                if ((int) $registration->data_revision !== $snapshot['data_revision']) {
                    throw ValidationException::withMessages([
                        'pdf' => 'Data pendaftaran berubah saat PDF dibuat. Silakan buat ulang PDF.',
                    ]);
                }

                $this->assertCanGenerate($registration, $type);

                $this->fileService->store($diskName, $filePath, $pdfBytes);
                $stored = true;
                $fileHash = hash('sha256', $pdfBytes);

                $versionNumber = (int) $registration->pdfVersions()
                    ->where('document_type', $type->value)
                    ->max('version_number') + 1;

                $version = $registration->pdfVersions()->create([
                    'document_type' => $type,
                    'version_number' => $versionNumber,
                    'data_revision' => $snapshot['data_revision'],
                    'file_path' => $filePath,
                    'file_hash' => $fileHash,
                    'storage_disk' => $diskName,
                    'mime_type' => 'application/pdf',
                    'file_size_bytes' => strlen($pdfBytes),
                    'verification_token_hash' => $verificationToken === null
                        ? null
                        : hash('sha256', $verificationToken),
                    'generated_by' => $user->id,
                    'generated_at' => now(),
                ]);

                $registration->logs()->create([
                    'user_id' => $user->id,
                    'action' => 'pdf_generated',
                    'from_revision' => $snapshot['data_revision'],
                    'to_revision' => $snapshot['data_revision'],
                    'changes' => [
                        'document_type' => $type->value,
                        'version_number' => $versionNumber,
                    ],
                    'created_at' => now(),
                ]);

                return $version;
            }, attempts: 3);
        } catch (Throwable $exception) {
            if ($stored) {
                try {
                    $this->fileService->delete($diskName, $filePath);
                } catch (Throwable $cleanupFailure) {
                    report($cleanupFailure);

                    throw new RegistrationPdfGenerationException(
                        'PDF gagal disimpan dan berkas sementara tidak dapat dibersihkan.',
                        previous: $exception,
                    );
                }
            }

            if ($exception instanceof ValidationException || $exception instanceof ModelNotFoundException) {
                throw $exception;
            }

            $this->recordFailure($snapshot, $user, $type);

            throw new RegistrationPdfGenerationException(
                'PDF tidak dapat dibuat saat ini. Silakan coba kembali.',
                previous: $exception,
            );
        }
    }

    /**
     * @return array{data_revision: int, document: array<string, mixed>}
     */
    private function createSnapshot(Registration $candidate, User $user, PdfDocumentType $type): array
    {
        return DB::transaction(function () use ($candidate, $user, $type): array {
            $registration = Registration::query()
                ->whereKey($candidate->id)
                ->whereHas('applicant', fn ($query) => $query->where('user_id', $user->id))
                ->lockForUpdate()
                ->firstOrFail();
            $registration->load([
                'applicant.user',
                'applicant.address',
                'applicant.father',
                'applicant.mother',
                'applicant.guardian',
                'applicant.education',
                'admissionPeriod',
                'admissionPeriodProgram.program',
                'consent',
            ]);

            $this->assertCanGenerate($registration, $type);

            return [
                'data_revision' => (int) $registration->data_revision,
                'document' => $this->documentData($registration, $type),
            ];
        });
    }

    private function assertCanGenerate(Registration $registration, PdfDocumentType $type): void
    {
        if ($type === PdfDocumentType::DRAFT && ! $registration->isDraft()) {
            throw ValidationException::withMessages([
                'pdf' => 'Draf PDF hanya dapat dibuat selama pendaftaran berstatus DRAFT.',
            ]);
        }

        if ($type === PdfDocumentType::FINAL) {
            if (
                ! $registration->isSubmitted()
                || $registration->registration_number === null
                || $registration->submitted_at === null
            ) {
                throw ValidationException::withMessages([
                    'pdf' => 'Bukti PDF final hanya dapat dibuat setelah pendaftaran berhasil dikirim.',
                ]);
            }

            $readiness = $this->readinessService->assess(
                $registration->applicant,
                $registration,
                forPdf: true,
            );

            if (! $readiness['is_complete']) {
                throw ValidationException::withMessages([
                    'pdf' => 'Bukti PDF final belum dapat dibuat: '.implode(' ', $readiness['blocking_messages']),
                ]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function documentData(Registration $registration, PdfDocumentType $type): array
    {
        $applicant = $registration->applicant;
        $user = $applicant->user;
        $education = $applicant->education;
        $period = $registration->admissionPeriod;
        $program = $registration->admissionPeriodProgram?->program;
        $nik = $user?->getDecryptedNik() ?? '';

        return [
            'registration_id' => $registration->id,
            'ministry_name' => config('ppdb.pdf.ministry_name'),
            'school_name' => config('ppdb.pdf.school_name'),
            'collection_instruction' => config('ppdb.pdf.collection_instruction'),
            'document_type' => $type,
            'registration_number' => $registration->registration_number ?: 'Belum diterbitkan',
            'data_revision' => (int) $registration->data_revision,
            'generated_at' => now()->locale('id')->translatedFormat('d F Y, H:i').' WIB',
            'submitted_at' => $registration->submitted_at?->copy()->locale('id')->translatedFormat('d F Y, H:i').' WIB',
            'period_name' => $period?->name ?: 'Belum ditentukan',
            'academic_year' => $period?->academic_year ?: 'Belum ditentukan',
            'period_start_date' => $period?->start_date?->copy()->locale('id')->translatedFormat('d F Y'),
            'period_end_date' => $period?->end_date?->copy()->locale('id')->translatedFormat('d F Y'),
            'age_at_reference' => $applicant->birth_date && $period?->age_reference_date
                ? $applicant->calculateAgeAt($period->age_reference_date)['formatted']
                : 'Belum ditentukan',
            'applicant' => [
                'full_name' => $applicant->full_name,
                'nik' => $type === PdfDocumentType::FINAL ? $nik : NikManager::mask($nik),
                'gender' => $applicant->gender?->label() ?? 'Belum diisi',
                'birth_place' => $applicant->birth_place,
                'birth_date' => $applicant->birth_date?->copy()->locale('id')->translatedFormat('d F Y'),
                'nisn' => $applicant->nisn,
                'child_order' => $applicant->child_order,
                'siblings_count' => $applicant->siblings_count,
            ],
            'address' => $applicant->address?->full_address,
            'father' => [
                'name' => $applicant->father?->name,
                'phone' => $applicant->father?->phone,
                'occupation' => $applicant->father?->occupation,
            ],
            'mother' => [
                'name' => $applicant->mother?->name,
                'phone' => $applicant->mother?->phone,
                'occupation' => $applicant->mother?->occupation,
            ],
            'guardian' => $applicant->guardian === null ? null : [
                'name' => $applicant->guardian->name,
                'phone' => $applicant->guardian->phone,
                'occupation' => $applicant->guardian->occupation,
            ],
            'education' => [
                'level' => $education?->education_level?->label() ?? 'Belum diisi',
                'school_name' => $education?->school_name,
                'npsn' => $education?->npsn,
                'city' => $education?->city,
            ],
            'program_name' => $program?->name ?: 'Belum dipilih',
            'consent' => $registration->consent?->is_agreed
                ? [
                    'statement_text' => $registration->consent->statement_text,
                    'accepted_by_name' => $registration->consent->accepted_by_name,
                    'agreed_at' => $registration->consent->agreed_at?->copy()->locale('id')->translatedFormat('d F Y, H:i').' WIB',
                ]
                : null,
            'photo_data_uri' => $this->photoDataUri($registration),
        ];
    }

    private function photoDataUri(Registration $registration): ?string
    {
        $photo = $registration->documents()
            ->with('requirement')
            ->get()
            ->first(function ($document): bool {
                $label = Str::lower(($document->requirement?->code ?? '').' '.($document->requirement?->name ?? ''));

                return Str::contains($label, ['pas foto', 'pas_foto']);
            });

        if ($photo === null) {
            return null;
        }

        $disk = Storage::disk((string) config('ppdb.documents.disk'));
        if (! $disk->exists($photo->file_path)) {
            throw ValidationException::withMessages([
                'pdf' => 'Berkas pas foto tidak ditemukan. Unggah ulang pas foto sebelum membuat PDF.',
            ]);
        }

        $bytes = $disk->get($photo->file_path);
        $image = getimagesizefromstring($bytes);
        if (
            $image === false
            || ! in_array($image['mime'] ?? null, ['image/jpeg', 'image/png'], true)
            || ($image[0] * $image[1]) > 20000000
        ) {
            throw ValidationException::withMessages([
                'pdf' => 'Berkas pas foto harus berupa JPG/PNG yang dapat dibaca dan berukuran gambar wajar.',
            ]);
        }

        if ($image['mime'] === 'image/png' && ! extension_loaded('gd')) {
            throw ValidationException::withMessages([
                'pdf' => 'Server belum mendukung pas foto PNG. Aktifkan ekstensi PHP GD atau unggah pas foto dalam format JPG.',
            ]);
        }

        return 'data:'.$image['mime'].';base64,'.base64_encode($bytes);
    }

    private function verificationUrl(string $token, string $registrationNumber): string
    {
        if (app()->isProduction()) {
            $appUrl = (string) config('app.url');
            $host = strtolower(rtrim((string) parse_url($appUrl, PHP_URL_HOST), '.'));
            $scheme = strtolower((string) parse_url($appUrl, PHP_URL_SCHEME));
            $localHost = $host === ''
                || in_array($host, ['localhost', '127.0.0.1', '::1'], true)
                || preg_match('/\.(local|localhost|test|internal|example)$/', $host) === 1;
            $privateIp = filter_var($host, FILTER_VALIDATE_IP) !== false
                && filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;

            if ($scheme !== 'https' || $localHost || $privateIp) {
                throw new RuntimeException('APP_URL harus berupa alamat HTTPS publik untuk membuat QR verifikasi.');
            }
        }

        return route('public.registration-pdfs.verify', [
            'token' => $token,
            'registration_number' => $registrationNumber,
        ]);
    }

    /**
     * @param  array{data_revision: int, document: array<string, mixed>}  $snapshot
     */
    private function recordFailure(array $snapshot, User $user, PdfDocumentType $type): void
    {
        try {
            RegistrationLog::query()->create([
                'registration_id' => $snapshot['document']['registration_id'],
                'user_id' => $user->id,
                'action' => 'pdf_generation_failed',
                'from_revision' => $snapshot['data_revision'],
                'to_revision' => $snapshot['data_revision'],
                'changes' => [
                    'document_type' => $type->value,
                    'error_code' => 'generation_failed',
                ],
                'created_at' => now(),
            ]);
        } catch (Throwable $loggingFailure) {
            report($loggingFailure);
        }
    }

    private function filePath(int $registrationId, PdfDocumentType $type): string
    {
        $basePath = trim((string) config('ppdb.documents.pdf_path'), '/');
        if ($basePath === '' || Str::contains($basePath, ['..', '\\'])) {
            throw new RuntimeException('Path penyimpanan PDF tidak valid.');
        }

        return $basePath.'/'.$registrationId.'/'.$type->value.'/'.Str::uuid().'.pdf';
    }
}
