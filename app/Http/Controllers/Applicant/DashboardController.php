<?php

declare(strict_types=1);

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Services\Applicant\ApplicantProfileService;
use App\Services\Registration\RegistrationEditService;
use App\Support\NikManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ApplicantProfileService $profileService,
        protected RegistrationEditService $registrationEditService
    ) {}

    /**
     * Display applicant dashboard with dynamic completion progress and admission status.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $applicant = $user->applicant;

        $maskedNik = $user->nik_encrypted ? NikManager::mask($user->getDecryptedNik() ?? '') : '****************';
        $activePeriod = $this->profileService->getActiveAdmissionPeriod();
        $registration = $this->registrationEditService->registrationForApplicant($applicant);
        $completionPeriod = $registration?->admissionPeriod ?? $activePeriod;
        $completion = $this->profileService->getCompletionSummary($applicant, $completionPeriod);
        $age = $this->profileService->calculateAge($applicant, $activePeriod);
        $registration = $completion['registration'] ?? $registration;

        return view('applicant.dashboard', [
            'user' => $user,
            'applicant' => $applicant,
            'maskedNik' => $maskedNik,
            'activePeriod' => $activePeriod,
            'completion' => $completion,
            'age' => $age,
            'registration' => $registration,
            'canEdit' => $this->registrationEditService->canEdit($registration),
            'canEditProgram' => ($activePeriod?->isOpen() ?? false)
                && ($registration === null || $registration->isDraft()),
        ]);
    }
}
