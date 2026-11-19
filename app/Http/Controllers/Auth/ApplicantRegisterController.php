<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Authentication\RegisterApplicantAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ApplicantRegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;

class ApplicantRegisterController extends Controller
{
    /**
     * Show initial registration form for prospective applicants.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('applicant.dashboard');
        }

        return view('auth.applicant-register');
    }

    /**
     * Handle initial applicant registration or pending registration recovery.
     */
    public function store(
        ApplicantRegisterRequest $request,
        RegisterApplicantAction $action
    ): RedirectResponse {
        try {
            $result = $action->execute($request->validated());

            // Store user id in session to facilitate multi-step activation
            session(['activation_user_id' => $result['user']->id]);

            if (! $result['otp_dispatched']) {
                return redirect()->route('activation.form')->with(
                    'warning',
                    'Permintaan pendaftaran sudah diterima, tetapi kode verifikasi belum berhasil dikirim. Silakan periksa nomor WhatsApp atau kembali ke formulir untuk memperbaiki nomor.'
                );
            }

            $successMessage = ! empty($result['is_recovery'])
                ? 'Data pendaftaran dengan NIK ini berhasil dipulihkan. Nomor WhatsApp telah diperbarui dan kode OTP verifikasi baru telah dikirimkan.'
                : 'Pendaftaran akun awal berhasil. Kode OTP verifikasi 6 digit telah dikirimkan ke nomor WhatsApp orang tua/wali.';

            return redirect()->route('activation.form')->with('success', $successMessage);
        } catch (InvalidArgumentException $e) {
            return back()
                ->withInput($request->except(['consent']))
                ->withErrors(['nik' => $e->getMessage()]);
        }
    }
}
