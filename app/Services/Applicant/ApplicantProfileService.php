<?php

declare(strict_types=1);

namespace App\Services\Applicant;

use App\Enums\AdmissionPeriodStatus;
use App\Enums\PreviousEducationLevel;
use App\Models\AdmissionPeriod;
use App\Models\Applicant;
use App\Models\Registration;
use App\Services\Document\DocumentCompletenessService;
use Carbon\Carbon;

class ApplicantProfileService
{
    public function __construct(
        protected DocumentCompletenessService $documentCompletenessService
    ) {}

    /**
     * Retrieve current active admission period.
     */
    public function getActiveAdmissionPeriod(): ?AdmissionPeriod
    {
        return AdmissionPeriod::query()
            ->where('status', AdmissionPeriodStatus::ACTIVE)
            ->where('is_active', true)
            ->with(['periodPrograms.program', 'programs'])
            ->get()
            ->first(fn (AdmissionPeriod $period): bool => $period->isOpen());
    }

    /**
     * Calculate dynamic age of an applicant against the admission period's age reference date.
     * Age is NEVER persisted permanently in the database.
     *
     * @return array{years: int, months: int, days: int, formatted: string, reference_date: ?string}
     */
    public function calculateAge(Applicant $applicant, ?AdmissionPeriod $period = null): array
    {
        $targetPeriod = $period ?? $this->getActiveAdmissionPeriod();
        $refDate = $targetPeriod?->age_reference_date ?? Carbon::now();

        $calc = $applicant->calculateAgeAt($refDate);
        $calc['reference_date'] = $targetPeriod?->age_reference_date?->format('d F Y') ?? Carbon::now()->format('d F Y');

        return $calc;
    }

    /**
     * Calculate comprehensive profile completion summary dynamically based on saved data.
     *
     * @return array{
     *     biodata: array{status: string, label: string, is_complete: bool},
     *     address: array{status: string, label: string, is_complete: bool},
     *     father: array{status: string, label: string, is_complete: bool},
     *     mother: array{status: string, label: string, is_complete: bool},
     *     guardian: array{status: string, label: string, is_complete: bool, has_guardian: bool},
     *     education: array{status: string, label: string, is_complete: bool},
     *     program: array{status: string, label: string, is_complete: bool, selected_program: ?string},
     *     documents: array{required_count: int, uploaded_count: int, missing: list<string>, percentage: int, is_complete: bool},
     *     overall_percentage: int,
     *     is_all_complete: bool,
     *     registration: ?Registration
     * }
     */
    public function getCompletionSummary(Applicant $applicant, ?AdmissionPeriod $period = null): array
    {
        $applicant->loadMissing(['address', 'guardians', 'education']);

        $activePeriod = $period ?? $this->getActiveAdmissionPeriod();
        $registration = null;

        if ($activePeriod) {
            $registration = Registration::query()
                ->where('applicant_id', $applicant->id)
                ->where('admission_period_id', $activePeriod->id)
                ->with(['admissionPeriodProgram.program'])
                ->first();
        }

        // 1. Biodata Status (mandatory: full_name, gender, birth_place, birth_date, child_order, siblings_count)
        $hasBasicBio = ! empty($applicant->full_name) && ! empty($applicant->gender) && ! empty($applicant->birth_place) && ! empty($applicant->birth_date);
        $hasCompleteBio = $hasBasicBio
            && $applicant->child_order !== null
            && $applicant->siblings_count !== null
            && filled($applicant->religion)
            && filled($applicant->family_card_number_encrypted)
            && ($applicant->nisn_unavailable_reason || filled($applicant->nisn))
            && filled($applicant->hobby)
            && filled($applicant->aspiration)
            && filled($applicant->residence_status)
            && filled($applicant->travel_time)
            && filled($applicant->transportation)
            && filled($applicant->social_assistance_number_encrypted);
        $biodataStatus = [
            'status' => $hasCompleteBio ? 'lengkap' : ($hasBasicBio ? 'belum_lengkap' : 'belum_diisi'),
            'label' => $hasCompleteBio ? 'Lengkap' : ($hasBasicBio ? 'Belum Lengkap' : 'Belum Diisi'),
            'is_complete' => $hasCompleteBio,
        ];

        // 2. Address Status (mandatory: address_line, kelurahan, kecamatan, city, province)
        $address = $applicant->address;
        $hasAddress = $address && ! empty($address->address_line) && ! empty($address->rt) && ! empty($address->rw)
            && ! empty($address->postal_code) && ! empty($address->distance_to_school_km)
            && ! empty($address->kelurahan) && ! empty($address->kecamatan) && ! empty($address->city) && ! empty($address->province);
        $addressStatus = [
            'status' => $hasAddress ? 'lengkap' : 'belum_diisi',
            'label' => $hasAddress ? 'Lengkap' : 'Belum Diisi',
            'is_complete' => (bool) $hasAddress,
        ];

        // 3. Father Status (mandatory: name, education, occupation, monthly_income)
        $father = $applicant->father;
        $hasFather = $father && ! empty($father->name) && ! empty($father->nik_encrypted)
            && ! empty($father->birth_place) && ! empty($father->birth_date) && ! empty($father->education)
            && ! empty($father->occupation) && ! empty($father->monthly_income) && ! empty($father->phone)
            && ! empty($father->address_line) && ! empty($father->postal_code) && ! empty($father->residence_status);
        $fatherStatus = [
            'status' => $hasFather ? 'lengkap' : 'belum_diisi',
            'label' => $hasFather ? 'Lengkap' : 'Belum Diisi',
            'is_complete' => (bool) $hasFather,
        ];

        // 4. Mother Status (mandatory: name, education, occupation, monthly_income)
        $mother = $applicant->mother;
        $hasMother = $mother && ! empty($mother->name) && ! empty($mother->nik_encrypted)
            && ! empty($mother->birth_place) && ! empty($mother->birth_date) && ! empty($mother->education)
            && ! empty($mother->occupation) && ! empty($mother->monthly_income) && ! empty($mother->phone)
            && ! empty($mother->address_line) && ! empty($mother->postal_code) && ! empty($mother->residence_status);
        $motherStatus = [
            'status' => $hasMother ? 'lengkap' : 'belum_diisi',
            'label' => $hasMother ? 'Lengkap' : 'Belum Diisi',
            'is_complete' => (bool) $hasMother,
        ];

        // 5. Guardian Status (optional)
        $guardian = $applicant->guardian;
        $hasGuardian = $guardian && ! empty($guardian->name);
        $guardianStatus = [
            'status' => $hasGuardian ? 'lengkap' : 'tidak_diisi',
            'label' => $hasGuardian ? 'Diisi (Lengkap)' : 'Tidak Diisi (Opsional)',
            'is_complete' => true, // Optional, does not block completion
            'has_guardian' => (bool) $hasGuardian,
        ];

        // 6. Previous Education Status (mandatory: level; if level != none, school_name is required)
        $education = $applicant->education;
        $hasEducation = false;
        if ($education) {
            if ($education->education_level === PreviousEducationLevel::NONE) {
                $hasEducation = true;
            } elseif (! empty($education->school_name) && ! empty($education->npsn)) {
                $hasEducation = true;
            }
        }
        $educationStatus = [
            'status' => $hasEducation ? 'lengkap' : 'belum_diisi',
            'label' => $hasEducation ? 'Lengkap' : 'Belum Diisi',
            'is_complete' => $hasEducation,
        ];

        // 7. Program Selection Status
        $hasProgram = $registration && $registration->admission_period_program_id !== null;
        $selectedProgramName = $registration?->admissionPeriodProgram?->program?->name;
        $programStatus = [
            'status' => $hasProgram ? 'lengkap' : 'belum_dipilih',
            'label' => $hasProgram ? "Dipilih ({$selectedProgramName})" : 'Belum Dipilih',
            'is_complete' => (bool) $hasProgram,
            'selected_program' => $selectedProgramName,
        ];

        $documentStatus = $this->documentCompletenessService->summarize($registration);

        // Calculate overall progress across profile, program, and required documents.
        $mandatoryItems = [
            $biodataStatus['is_complete'],
            $addressStatus['is_complete'],
            $fatherStatus['is_complete'],
            $motherStatus['is_complete'],
            $educationStatus['is_complete'],
            $programStatus['is_complete'],
            $documentStatus['is_complete'],
        ];

        $completedCount = count(array_filter($mandatoryItems));
        $totalMandatory = count($mandatoryItems);
        $percentage = (int) round(($completedCount / $totalMandatory) * 100);

        return [
            'biodata' => $biodataStatus,
            'address' => $addressStatus,
            'father' => $fatherStatus,
            'mother' => $motherStatus,
            'guardian' => $guardianStatus,
            'education' => $educationStatus,
            'program' => $programStatus,
            'documents' => $documentStatus,
            'overall_percentage' => $percentage,
            'is_all_complete' => $completedCount === $totalMandatory,
            'registration' => $registration,
        ];
    }
}
