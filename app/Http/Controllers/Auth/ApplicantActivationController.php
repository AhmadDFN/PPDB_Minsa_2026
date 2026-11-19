<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Authentication\CompleteApplicantActivationAction;
use App\Actions\Authentication\VerifyOtpAction;
use App\Enums\AccountStatus;
use App\Enums\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CompleteActivationRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\Authentication\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use InvalidArgumentException;

class ApplicantActivationController extends Controller
{
    /**
     * Show OTP verification screen for account activation.
     */
    public function showOtpForm(): View|RedirectResponse
    {
        $userId = session('activation_user_id');

        if (! $userId) {
            return redirect()->route('register')
                ->with('error', 'Sesi aktivasi tidak ditemukan atau telah kedaluwarsa. Silakan lakukan pendaftaran terlebih dahulu.');
        }

        $user = User::find($userId);

        if (! $user) {
            session()->forget('activation_user_id');

            return redirect()->route('register')
                ->with('error', 'Data pengguna tidak ditemukan. Silakan mendaftar kembali.');
        }

        if ($user->account_status === AccountStatus::ACTIVE) {
            session()->forget('activation_user_id');

            return redirect()->route('login')
                ->with('info', 'Akun pendaftaran Anda sudah aktif. Silakan masuk menggunakan NIK dan kata sandi.');
        }

        $maskedPhone = $this->maskPhone((string) $user->phone);

        return view('auth.applicant-verify-otp', [
            'user' => $user,
            'maskedPhone' => $maskedPhone,
        ]);
    }

    /**
     * Verify submitted OTP code for account activation.
     */
    public function verifyOtp(
        VerifyOtpRequest $request,
        VerifyOtpAction $action
    ): RedirectResponse {
        $userId = session('activation_user_id');

        if (! $userId) {
            return redirect()->route('register')
                ->with('error', 'Sesi aktivasi telah berakhir. Silakan ulangi pendaftaran.');
        }

        $user = User::findOrFail($userId);

        $throttleKey = 'otp-verify:'.$user->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'otp' => "Terlalu banyak percobaan verifikasi. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        RateLimiter::hit($throttleKey, 300);

        $result = $action->execute($user, OtpPurpose::ACTIVATION, $request->validated('otp'));

        if (! $result['success']) {
            return back()->withErrors(['otp' => $result['message']]);
        }

        RateLimiter::clear($throttleKey);

        // Move to password creation step
        session([
            'activation_verified_user_id' => $user->id,
        ]);
        session()->forget('activation_user_id');

        return redirect()->route('activation.password.form')->with(
            'success',
            'Nomor telepon berhasil diverifikasi! Sekarang silakan buat kata sandi untuk akun pendaftaran Anda.'
        );
    }

    /**
     * Resend a fresh OTP token.
     */
    public function resendOtp(
        Request $request,
        OtpService $otpService
    ): RedirectResponse {
        $userId = session('activation_user_id');

        if (! $userId) {
            return redirect()->route('register')
                ->with('error', 'Sesi aktivasi tidak ditemukan.');
        }

        $user = User::findOrFail($userId);

        $throttleKey = 'resend-otp:'.$user->id.'|'.$request->ip();
        if ($otpService->isSendRateLimited($throttleKey, 3, 300)) {
            $seconds = $otpService->sendRateLimitAvailableIn($throttleKey);

            return back()->with('error', "Mohon tunggu {$seconds} detik sebelum meminta pengiriman ulang kode OTP.");
        }

        try {
            $otpService->generateAndSend($user, OtpPurpose::ACTIVATION);
            $otpService->hitSendRateLimiter($throttleKey, 300);

            return back()->with('success', 'Kode verifikasi OTP baru telah berhasil dikirimkan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show password creation screen.
     */
    public function showPasswordForm(): View|RedirectResponse
    {
        $userId = session('activation_verified_user_id');

        if (! $userId) {
            return redirect()->route('register')
                ->with('error', 'Sesi pembuatan kata sandi tidak ditemukan atau belum diverifikasi.');
        }

        $user = User::find($userId);

        if (! $user) {
            session()->forget('activation_verified_user_id');

            return redirect()->route('register');
        }

        return view('auth.applicant-set-password', [
            'user' => $user,
        ]);
    }

    /**
     * Save new password and activate applicant account.
     */
    public function savePassword(
        CompleteActivationRequest $request,
        CompleteApplicantActivationAction $action
    ): RedirectResponse {
        $userId = session('activation_verified_user_id');

        if (! $userId) {
            return redirect()->route('register')
                ->with('error', 'Sesi pembuatan kata sandi tidak valid.');
        }

        $user = User::findOrFail($userId);

        try {
            $action->execute($user, $request->validated('password'));

            session()->forget('activation_verified_user_id');

            return redirect()->route('login')->with(
                'success',
                'Aktivasi akun berhasil! Akun Anda telah aktif. Silakan masuk menggunakan NIK dan kata sandi yang telah Anda buat.'
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['password' => $e->getMessage()]);
        }
    }

    /**
     * Mask phone number for privacy (e.g. 0812****7890).
     */
    protected function maskPhone(string $phone): string
    {
        $len = strlen($phone);
        if ($len < 8) {
            return '********';
        }

        return substr($phone, 0, 4).str_repeat('*', max(4, $len - 7)).substr($phone, -3);
    }
}
