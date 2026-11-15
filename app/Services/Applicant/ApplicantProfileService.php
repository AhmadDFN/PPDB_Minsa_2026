<?php

declare(strict_types=1);

namespace App\Services\Applicant;

use App\Enums\AdmissionPeriodStatus;
use App\Enums\PreviousEducationLevel;
use App\Models\AdmissionPeriod;
use App\Models\Applicant;
use App\Models\Registration;
use Carbon\Carbon;

class ApplicantProfileService
{
    /**
     * Retrieve current active admission period.
     */
    public function getActiveAdmissionPeriod(): ?AdmissionPeriod
    {
        return AdmissionPeriod::query()
            ->where('status', AdmissionPeriodStatus::ACTIVE)
            ->where('is_active', true)
            ->with(['periodPrograms.program', 'programs'])
            ->first();
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
        $hasCompleteBio = $hasBasicBio && $applicant->child_order !== null && $applicant->siblings_count !== null;
        $biodataStatus = [
            'status' => $hasCompleteBio ? 'lengkap' : ($hasBasicBio ? 'belum_lengkap' : 'belum_diisi'),
            'label' => $hasCompleteBio ? 'Lengkap' : ($hasBasicBio ? 'Belum Lengkap' : 'Belum Diisi'),
            'is_complete' => $hasCompleteBio,
        ];

        // 2. Address Status (mandatory: address_line, kelurahan, kecamatan, city, province)
        $address = $applicant->address;
        $hasAddress = $address && ! empty($address->address_line) && ! empty($address->kelurahan) && ! empty($address->kecamatan) && ! empty($address->city) && ! empty($address->province);
        $addressStatus = [
            'status' => $hasAddress ? 'lengkap' : 'belum_diisi',
            'label' => $hasAddress ? 'Lengkap' : 'Belum Diisi',
            'is_complete' => (bool) $hasAddress,
        ];

        // 3. Father Status (mandatory: name, education, occupation, monthly_income)
        $father = $applicant->father;
        $hasFather = $father && ! empty($father->name) && ! empty($father->occupation);
        $fatherStatus = [
            'status' => $hasFather ? 'lengkap' : 'belum_diisi',
            'label' => $hasFather ? 'Lengkap' : 'Belum Diisi',
            'is_complete' => (bool) $hasFather,
        ];

        // 4. Mother Status (mandatory: name, education, occupation, monthly_income)
        $mother = $applicant->mother;
        $hasMother = $mother && ! empty($mother->name) && ! empty($mother->occupation);
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
            } elseif (! empty($education->school_name)) {
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

        // Calculate Overall Progress (6 mandatory components: Bio, Address, Father, Mother, Education, Program)
        $mandatoryItems = [
            $biodataStatus['is_complete'],
            $addressStatus['is_complete'],
            $fatherStatus['is_complete'],
            $motherStatus['is_complete'],
            $educationStatus['is_complete'],
            $programStatus['is_complete'],
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
            'overall_percentage' => $percentage,
            'is_all_complete' => $completedCount === $totalMandatory,
            'registration' => $registration,
        ];
    }
}
