<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Authentication\ResetPasswordAction;
use App\Actions\Authentication\VerifyOtpAction;
use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\Authentication\OtpService;
use App\Support\NikManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use InvalidArgumentException;

class ApplicantPasswordResetController extends Controller
{
    /**
     * Show initial forgot password screen where applicant enters NIK.
     */
    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle forgot password submission and issue recovery OTP without exposing account existence.
     */
    public function sendResetOtp(
        ForgotPasswordRequest $request,
        OtpService $otpService
    ): RedirectResponse {
        $nik = $request->validated('nik');
        $throttleKey = 'forgot-password:'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'nik' => "Terlalu banyak permintaan pemulihan kata sandi. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        RateLimiter::hit($throttleKey, 600);

        try {
            $normalizedNik = NikManager::normalize($nik);
            $nikHash = NikManager::hash($normalizedNik);
        } catch (InvalidArgumentException) {
            return back()->withErrors(['nik' => 'Format NIK tidak valid.']);
        }

        /** @var User|null $user */
        $user = User::where('nik_lookup_hash', $nikHash)
            ->where('account_type', AccountType::APPLICANT)
            ->first();

        // Generic response message to prevent account enumeration
        $genericMessage = 'Jika NIK terdaftar dan nomor telepon telah terverifikasi, kode OTP pemulihan kata sandi telah dikirimkan ke nomor WhatsApp Anda.';

        if (! $user) {
            return redirect()->route('password.reset.verify.form')
                ->with('info', $genericMessage);
        }

        // Only active accounts with verified phone can use automated recovery
        if ($user->account_status !== AccountStatus::ACTIVE || $user->phone_verified_at === null) {
            return back()->with(
                'warning',
                'Nomor telepon akun ini belum terverifikasi atau akun belum aktif. Silakan hubungi panitia PPDB MIN 1 Kota Madiun di PTSP untuk bantuan pemulihan akun.'
            );
        }

        // Issue password reset OTP
        $otpService->generateAndSend($user, OtpPurpose::PASSWORD_RESET);
        session(['password_reset_user_id' => $user->id]);

        return redirect()->route('password.reset.verify.form')
            ->with('success', $genericMessage);
    }

    /**
     * Show OTP verification screen for password reset.
     */
    public function showVerifyOtpForm(): View|RedirectResponse
    {
        $userId = session('password_reset_user_id');
        $user = $userId ? User::find($userId) : null;
        $maskedPhone = $user && $user->phone ? $this->maskPhone((string) $user->phone) : null;

        return view('auth.verify-reset-otp', [
            'user' => $user,
            'maskedPhone' => $maskedPhone,
        ]);
    }

    /**
     * Verify password recovery OTP.
     */
    public function verifyResetOtp(
        VerifyOtpRequest $request,
        VerifyOtpAction $action
    ): RedirectResponse {
        $userId = session('password_reset_user_id');

        if (! $userId) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi pemulihan kata sandi tidak ditemukan atau telah kedaluwarsa.');
        }

        $user = User::findOrFail($userId);

        $throttleKey = 'verify-reset-otp:'.$user->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'otp' => "Terlalu banyak percobaan verifikasi. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        RateLimiter::hit($throttleKey, 300);

        $result = $action->execute($user, OtpPurpose::PASSWORD_RESET, $request->validated('otp'));

        if (! $result['success']) {
            return back()->withErrors(['otp' => $result['message']]);
        }

        RateLimiter::clear($throttleKey);

        session([
            'password_reset_verified_user_id' => $user->id,
        ]);
        session()->forget('password_reset_user_id');

        return redirect()->route('password.reset.form')->with(
            'success',
            'Kode OTP terverifikasi! Silakan buat kata sandi baru Anda.'
        );
    }

    /**
     * Resend recovery OTP token.
     */
    public function resendResetOtp(
        Request $request,
        OtpService $otpService
    ): RedirectResponse {
        $userId = session('password_reset_user_id');

        if (! $userId) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi pemulihan tidak ditemukan.');
        }

        $user = User::findOrFail($userId);

        $throttleKey = 'resend-reset-otp:'.$user->id.'|'.$request->ip();
        if ($otpService->isSendRateLimited($throttleKey, 3, 300)) {
            $seconds = $otpService->sendRateLimitAvailableIn($throttleKey);

            return back()->with('error', "Mohon tunggu {$seconds} detik sebelum meminta pengiriman ulang kode OTP.");
        }

        $otpService->hitSendRateLimiter($throttleKey, 300);
        $otpService->generateAndSend($user, OtpPurpose::PASSWORD_RESET);

        return back()->with('success', 'Kode OTP baru telah berhasil dikirimkan.');
    }

    /**
     * Show form to input new password.
     */
    public function showResetPasswordForm(): View|RedirectResponse
    {
        $userId = session('password_reset_verified_user_id');

        if (! $userId) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi pembuatan kata sandi baru tidak valid atau belum diverifikasi.');
        }

        $user = User::find($userId);

        if (! $user) {
            session()->forget('password_reset_verified_user_id');

            return redirect()->route('password.request');
        }

        return view('auth.reset-password', [
            'user' => $user,
        ]);
    }

    /**
     * Update password with newly submitted password.
     */
    public function resetPassword(
        ResetPasswordRequest $request,
        ResetPasswordAction $action
    ): RedirectResponse {
        $userId = session('password_reset_verified_user_id');

        if (! $userId) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi pembuatan kata sandi baru tidak valid.');
        }

        $user = User::findOrFail($userId);

        try {
            $action->execute($user, $request->validated('password'));

            session()->forget('password_reset_verified_user_id');

            return redirect()->route('login')->with(
                'success',
                'Kata sandi Anda berhasil diperbarui! Silakan masuk menggunakan kata sandi baru Anda.'
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['password' => $e->getMessage()]);
        }
    }

    /**
     * Mask phone number for privacy display.
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
