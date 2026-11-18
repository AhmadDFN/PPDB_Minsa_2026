<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Registration\RegistrationPdfVerificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RegistrationPdfVerificationController extends Controller
{
    public function show(
        Request $request,
        string $token,
        RegistrationPdfVerificationService $verificationService
    ): Response {
        $registrationNumber = $request->query('registration_number');
        $verification = $verificationService->inspect(
            $token,
            is_string($registrationNumber)
                ? $registrationNumber
                : null,
        );
        abort_if($verification === null, 404);

        return response()
            ->view('pdf.registration.verification', ['verification' => $verification])
            ->header('Cache-Control', 'no-store, max-age=0')
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
