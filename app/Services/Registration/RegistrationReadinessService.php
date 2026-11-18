<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Enums\AdmissionPeriodStatus;
use App\Enums\PreviousEducationLevel;
use App\Models\AdmissionPeriod;
use App\Models\Applicant;
use App\Models\Registration;
use App\Services\Document\DocumentCompletenessService;

class RegistrationReadinessService
{
    public function __construct(
        private DocumentCompletenessService $documentCompletenessService
    ) {}

    /**
     * @return array{
     *     is_complete: bool,
     *     can_submit_after_consent: bool,
     *     sections: array<string, array{label: string, is_complete: bool, missing: list<string>}>,
     *     missing_documents: list<string>,
     *     blocking_messages: list<string>
     * }
     */
    public function assess(Applicant $applicant, ?Registration $registration, bool $consentAccepted = false): array
    {
        $applicant->loadMissing(['user', 'address', 'father', 'mother', 'guardian', 'education']);

        $biodataMissing = $this->missingFields($applicant, [
            'full_name' => 'Nama lengkap',
            'gender' => 'Jenis kelamin',
            'birth_place' => 'Tempat lahir',
            'birth_date' => 'Tanggal lahir',
            'child_order' => 'Urutan anak',
            'siblings_count' => 'Jumlah saudara',
        ]);

        if (
            $this->isMissing($applicant->user?->nik_encrypted)
            || $this->isMissing($applicant->user?->nik_lookup_hash)
        ) {
            $biodataMissing[] = 'NIK calon siswa';
        }

        $addressMissing = $this->missingFields($applicant->address, [
            'address_line' => 'Alamat jalan/dusun',
            'kelurahan' => 'Kelurahan/desa',
            'kecamatan' => 'Kecamatan',
            'city' => 'Kota/kabupaten',
            'province' => 'Provinsi',
        ]);

        $fatherMissing = $this->missingFields($applicant->father, [
            'name' => 'Nama ayah',
            'nik' => 'NIK ayah',
            'education' => 'Pendidikan terakhir ayah',
            'occupation' => 'Pekerjaan ayah',
            'monthly_income' => 'Penghasilan ayah',
            'phone' => 'Nomor telepon ayah',
            'is_alive' => 'Status ayah',
        ]);
        $motherMissing = $this->missingFields($applicant->mother, [
            'name' => 'Nama ibu',
            'nik' => 'NIK ibu',
            'education' => 'Pendidikan terakhir ibu',
            'occupation' => 'Pekerjaan ibu',
            'monthly_income' => 'Penghasilan ibu',
            'phone' => 'Nomor telepon ibu',
            'is_alive' => 'Status ibu',
        ]);

        $educationMissing = [];
        $education = $applicant->education;

        if ($education === null || $education->education_level === null) {
            $educationMissing[] = 'Jenjang pendidikan sebelumnya';
        } elseif (
            $education->education_level !== PreviousEducationLevel::NONE
            && $this->isMissing($education->school_name)
        ) {
            $educationMissing[] = 'Nama sekolah/lembaga asal';
        }

        $period = $registration?->admissionPeriod;
        if ($registration === null) {
            $period = AdmissionPeriod::query()
                ->where('is_active', true)
                ->where('status', AdmissionPeriodStatus::ACTIVE)
                ->first();
        }

        $periodMissing = [];
        if ($registration !== null && $registration->applicant_id !== $applicant->id) {
            $periodMissing[] = 'Pendaftaran tidak terikat pada akun ini';
        } elseif ($period === null || ! $period->isOpen()) {
            $periodMissing[] = 'Periode pendaftaran tidak sedang dibuka';
        }

        $programMissing = [];
        $periodProgram = $registration?->admissionPeriodProgram;
        if (
            $registration === null
            || $registration->applicant_id !== $applicant->id
            || $registration->admission_period_program_id === null
        ) {
            $programMissing[] = 'Pilihan program pendaftaran';
        } elseif (
            $periodProgram === null
            || $period === null
            || $periodProgram->admission_period_id !== $period->id
            || ! $periodProgram->is_active
            || ! $periodProgram->program?->is_active
        ) {
            $programMissing[] = 'Program tidak tersedia pada periode aktif';
        }

        $documentSummary = $this->documentCompletenessService->summarize($registration);
        $missingDocuments = $documentSummary['missing'];
        $documentsMissing = $missingDocuments;

        if ($documentSummary['required_count'] === 0 && ! $documentSummary['is_complete']) {
            $documentsMissing[] = 'Persyaratan dokumen wajib belum dikonfigurasi';
        }

        $consentConfigured = trim((string) config('ppdb.registration.consent_statement')) !== ''
            && trim((string) config('ppdb.registration.consent_version')) !== '';
        $consentExists = $registration?->consent?->is_agreed === true;
        $consentMissing = ! $consentConfigured
            ? ['Pernyataan persetujuan belum dikonfigurasi oleh madrasah']
            : ($consentAccepted || $consentExists
                ? []
                : ['Pernyataan kebenaran data dan nama orang tua/wali']);

        $sections = [
            'biodata' => $this->section('Biodata calon siswa', $biodataMissing),
            'address' => $this->section('Alamat domisili', $addressMissing),
            'father' => $this->section('Data ayah', $fatherMissing),
            'mother' => $this->section('Data ibu', $motherMissing),
            'guardian' => $this->section('Data wali (opsional)', []),
            'education' => $this->section('Pendidikan sebelumnya', $educationMissing),
            'period' => $this->section('Periode pendaftaran', $periodMissing),
            'program' => $this->section('Pilihan program', $programMissing),
            'documents' => $this->section('Dokumen wajib', $documentsMissing),
            'consent' => $this->section('Persetujuan orang tua/wali', $consentMissing),
        ];

        $blockingMessages = [];
        foreach ($sections as $section) {
            if (! $section['is_complete']) {
                $blockingMessages[] = $section['label'].': '.implode(', ', $section['missing']).'.';
            }
        }

        $canSubmitAfterConsent = collect($sections)
            ->except('consent')
            ->every(fn (array $section): bool => $section['is_complete'])
            && $consentConfigured;

        return [
            'is_complete' => $blockingMessages === [],
            'can_submit_after_consent' => $canSubmitAfterConsent,
            'sections' => $sections,
            'missing_documents' => $missingDocuments,
            'blocking_messages' => $blockingMessages,
        ];
    }

    /**
     * @param  array<string, string>  $fields
     * @return list<string>
     */
    private function missingFields(?object $model, array $fields): array
    {
        $missing = [];

        foreach ($fields as $field => $label) {
            if ($model === null || $this->isMissing($model->{$field} ?? null)) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    private function isMissing(mixed $value): bool
    {
        return $value === null || (is_string($value) && trim($value) === '');
    }

    /**
     * @param  list<string>  $missing
     * @return array{label: string, is_complete: bool, missing: list<string>}
     */
    private function section(string $label, array $missing): array
    {
        return [
            'label' => $label,
            'is_complete' => $missing === [],
            'missing' => $missing,
        ];
    }
}
