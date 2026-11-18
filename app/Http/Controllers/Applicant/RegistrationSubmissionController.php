<?php

declare(strict_types=1);

namespace App\Http\Controllers\Applicant;

use App\Actions\Registration\SubmitRegistrationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Applicant\SubmitRegistrationRequest;
use Illuminate\Http\RedirectResponse;

class RegistrationSubmissionController extends Controller
{
    public function store(
        SubmitRegistrationRequest $request,
        SubmitRegistrationAction $action
    ): RedirectResponse {
        $action->execute(
            $request->user(),
            $request->validated('consent_name'),
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()
            ->route('applicant.registration.review')
            ->with('success', 'Pendaftaran Anda telah terkirim. Nomor registrasi tidak akan berubah pada pengiriman ulang.');
    }
}
