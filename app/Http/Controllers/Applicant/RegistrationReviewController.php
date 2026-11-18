<?php

declare(strict_types=1);

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\ApplicantGuardian;
use App\Services\Applicant\ApplicantProfileService;
use App\Services\Registration\RegistrationEditService;
use App\Services\Registration\RegistrationReadinessService;
use App\Support\NikManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationReviewController extends Controller
{
    public function show(
        Request $request,
        ApplicantProfileService $profileService,
        RegistrationReadinessService $readinessService,
        RegistrationEditService $registrationEditService
    ): View {
        $user = $request->user();
        $applicant = $user->applicant()->with([
            'address',
            'father',
            'mother',
            'guardian',
            'education',
        ])->firstOrFail();
        $registration = $registrationEditService->registrationForApplicant($applicant);
        $registration?->loadMissing('consent');
        $period = $registration?->admissionPeriod ?? $profileService->getActiveAdmissionPeriod();
        $readiness = $readinessService->assess($applicant, $registration);

        $documents = $registration?->documents()
            ->with('requirement')
            ->orderBy('document_requirement_id')
            ->get()
            ->map(fn ($document): array => [
                'requirement_name' => $document->requirement?->name ?? 'Dokumen',
                'original_name' => $document->original_name,
                'uploaded_at' => $document->uploaded_at,
            ]) ?? collect();

        $registrationIsDraft = $registration?->isDraft() ?? false;
        $canSubmit = $registrationIsDraft && $readiness['can_submit_after_consent'];
        $age = $period?->age_reference_date
            ? $applicant->calculateAgeAt($period->age_reference_date)
            : null;

        return view('applicant.registration.review', [
            'applicant' => $applicant,
            'maskedNik' => $user->getMaskedNik(),
            'address' => $applicant->address,
            'father' => $this->parentSummary($applicant->father),
            'mother' => $this->parentSummary($applicant->mother),
            'guardian' => $applicant->guardian
                ? $this->parentSummary($applicant->guardian)
                : null,
            'education' => $applicant->education,
            'registration' => $registration,
            'period' => $period,
            'program' => $registration?->admissionPeriodProgram?->program,
            'age' => $age,
            'documents' => $documents,
            'readiness' => $readiness,
            'canSubmit' => $canSubmit,
            'canEdit' => $registrationEditService->canEdit($registration),
            'canEditProgram' => ($period?->isOpen() ?? false)
                && ($registration === null || $registration->isDraft()),
            'consentStatement' => (string) config('ppdb.registration.consent_statement'),
            'consentVersion' => (string) config('ppdb.registration.consent_version'),
        ]);
    }

    /**
     * @return array{name: string, nik: string, occupation: ?string, education: ?string, phone: ?string, is_alive: bool}|null
     */
    private function parentSummary(?ApplicantGuardian $parent): ?array
    {
        if ($parent === null) {
            return null;
        }

        return [
            'name' => $parent->name,
            'nik' => $parent->nik ? NikManager::mask($parent->nik) : 'Belum diisi',
            'occupation' => $parent->occupation,
            'education' => $parent->education,
            'phone' => $parent->phone,
            'is_alive' => $parent->is_alive,
        ];
    }
}
