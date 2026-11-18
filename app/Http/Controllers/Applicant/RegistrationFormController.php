<?php

declare(strict_types=1);

namespace App\Http\Controllers\Applicant;

use App\Actions\Applicant\SaveApplicantAddressAction;
use App\Actions\Applicant\SaveApplicantEducationAction;
use App\Actions\Applicant\SaveApplicantParentsAction;
use App\Actions\Applicant\SelectAdmissionProgramAction;
use App\Actions\Applicant\UpdateApplicantBiodataAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Applicant\SelectProgramRequest;
use App\Http\Requests\Applicant\UpdateAddressRequest;
use App\Http\Requests\Applicant\UpdateBiodataRequest;
use App\Http\Requests\Applicant\UpdateEducationRequest;
use App\Http\Requests\Applicant\UpdateParentsRequest;
use App\Services\Applicant\ApplicantProfileService;
use App\Services\Registration\RegistrationEditService;
use App\Support\NikManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class RegistrationFormController extends Controller
{
    public function __construct(
        protected ApplicantProfileService $profileService,
        protected RegistrationEditService $registrationEditService
    ) {}

    /**
     * Step 1: Student Biodata Form.
     */
    public function editBiodata(Request $request): View
    {
        $applicant = $request->user()->applicant;
        $user = $request->user();
        $maskedNik = $user->nik_encrypted ? NikManager::mask($user->getDecryptedNik() ?? '') : '****************';
        $activePeriod = $this->profileService->getActiveAdmissionPeriod();
        $age = $this->profileService->calculateAge($applicant, $activePeriod);

        return view('applicant.form.biodata', [
            'applicant' => $applicant,
            'maskedNik' => $maskedNik,
            'age' => $age,
            'currentStep' => 1,
        ]);
    }

    /**
     * Step 1: Update Student Biodata.
     */
    public function updateBiodata(
        UpdateBiodataRequest $request,
        UpdateApplicantBiodataAction $action
    ): RedirectResponse {
        $applicant = $request->user()->applicant;
        $this->registrationEditService->run(
            $applicant,
            $request->user(),
            $request->ip(),
            $request->userAgent(),
            fn () => $action->execute($applicant, $request->validated()),
        );

        if ($request->input('nav_action') === 'next') {
            return redirect()->route('applicant.address.edit')
                ->with('success', 'Biodata calon siswa berhasil disimpan. Silakan lanjutkan pengisian alamat domisili.');
        }

        return back()->with('success', 'Biodata calon siswa berhasil diperbarui.');
    }

    /**
     * Step 2: Address Form.
     */
    public function editAddress(Request $request): View
    {
        $applicant = $request->user()->applicant;
        $address = $applicant->address;

        return view('applicant.form.address', [
            'applicant' => $applicant,
            'address' => $address,
            'currentStep' => 2,
        ]);
    }

    /**
     * Step 2: Save Address.
     */
    public function updateAddress(
        UpdateAddressRequest $request,
        SaveApplicantAddressAction $action
    ): RedirectResponse {
        $applicant = $request->user()->applicant;
        $this->registrationEditService->run(
            $applicant,
            $request->user(),
            $request->ip(),
            $request->userAgent(),
            fn () => $action->execute($applicant, $request->validated()),
        );

        if ($request->input('nav_action') === 'next') {
            return redirect()->route('applicant.parents.edit')
                ->with('success', 'Alamat domisili berhasil disimpan. Silakan lengkapi data orang tua.');
        }

        return back()->with('success', 'Alamat domisili berhasil diperbarui.');
    }

    /**
     * Step 3: Parents & Guardian Form.
     */
    public function editParents(Request $request): View
    {
        $applicant = $request->user()->applicant;
        $father = $applicant->father;
        $mother = $applicant->mother;
        $guardian = $applicant->guardian;

        return view('applicant.form.parents', [
            'applicant' => $applicant,
            'father' => $father,
            'mother' => $mother,
            'guardian' => $guardian,
            'currentStep' => 3,
        ]);
    }

    /**
     * Step 3: Save Parents & Guardian.
     */
    public function updateParents(
        UpdateParentsRequest $request,
        SaveApplicantParentsAction $action
    ): RedirectResponse {
        $applicant = $request->user()->applicant;
        $this->registrationEditService->run(
            $applicant,
            $request->user(),
            $request->ip(),
            $request->userAgent(),
            fn () => $action->execute($applicant, $request->validated()),
        );

        if ($request->input('nav_action') === 'next') {
            return redirect()->route('applicant.education.edit')
                ->with('success', 'Data orang tua / wali berhasil disimpan. Silakan lengkapi riwayat pendidikan asal.');
        }

        return back()->with('success', 'Data orang tua / wali berhasil diperbarui.');
    }

    /**
     * Step 4: Previous Education Form.
     */
    public function editEducation(Request $request): View
    {
        $applicant = $request->user()->applicant;
        $education = $applicant->education;

        return view('applicant.form.education', [
            'applicant' => $applicant,
            'education' => $education,
            'currentStep' => 4,
        ]);
    }

    /**
     * Step 4: Save Education.
     */
    public function updateEducation(
        UpdateEducationRequest $request,
        SaveApplicantEducationAction $action
    ): RedirectResponse {
        $applicant = $request->user()->applicant;
        $this->registrationEditService->run(
            $applicant,
            $request->user(),
            $request->ip(),
            $request->userAgent(),
            fn () => $action->execute($applicant, $request->validated()),
        );

        if ($request->input('nav_action') === 'next') {
            return redirect()->route('applicant.program.edit')
                ->with('success', 'Riwayat pendidikan asal berhasil disimpan. Silakan pilih program pendidikan.');
        }

        return back()->with('success', 'Riwayat pendidikan asal berhasil diperbarui.');
    }

    /**
     * Step 5: Program Selection Form.
     */
    public function editProgram(Request $request): View
    {
        $applicant = $request->user()->applicant;
        $activePeriod = $this->profileService->getActiveAdmissionPeriod();
        $registration = null;

        if ($activePeriod) {
            $registration = $applicant->registrations()
                ->where('admission_period_id', $activePeriod->id)
                ->first();
        }

        return view('applicant.form.program', [
            'applicant' => $applicant,
            'activePeriod' => $activePeriod,
            'registration' => $registration,
            'currentStep' => 5,
        ]);
    }

    /**
     * Step 5: Save Program Choice.
     */
    public function updateProgram(
        SelectProgramRequest $request,
        SelectAdmissionProgramAction $action
    ): RedirectResponse {
        $applicant = $request->user()->applicant;
        $activePeriod = $this->profileService->getActiveAdmissionPeriod();

        if (! $activePeriod) {
            return back()->withErrors([
                'admission_period_program_id' => 'Saat ini tidak ada periode pendaftaran PPDB yang aktif.',
            ]);
        }

        try {
            $this->registrationEditService->run(
                $applicant,
                $request->user(),
                $request->ip(),
                $request->userAgent(),
                fn () => $action->execute(
                    $applicant,
                    (int) $request->validated('admission_period_program_id'),
                    $activePeriod
                ),
            );

            return redirect()->route('applicant.dashboard')
                ->with('success', 'Pilihan program pendidikan berhasil disimpan sebagai DRAFT pendaftaran.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['admission_period_program_id' => $e->getMessage()]);
        }
    }
}
