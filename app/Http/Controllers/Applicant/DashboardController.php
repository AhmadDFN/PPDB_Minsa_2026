<?php

declare(strict_types=1);

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Services\Applicant\ApplicantProfileService;
use App\Support\NikManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ApplicantProfileService $profileService
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
        $completion = $this->profileService->getCompletionSummary($applicant, $activePeriod);
        $age = $this->profileService->calculateAge($applicant, $activePeriod);
        $registration = $completion['registration'];

        if ($registration === null && $activePeriod === null) {
            $registration = $applicant->registrations()
                ->with(['admissionPeriod', 'admissionPeriodProgram.program'])
                ->latest('id')
                ->first();
        }

        return view('applicant.dashboard', [
            'user' => $user,
            'applicant' => $applicant,
            'maskedNik' => $maskedNik,
            'activePeriod' => $activePeriod,
            'completion' => $completion,
            'age' => $age,
            'registration' => $registration,
        ]);
    }
}
