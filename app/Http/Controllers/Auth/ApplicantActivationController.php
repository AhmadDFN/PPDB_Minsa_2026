<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Authentication\VerifyOtpAction;
use App\Enums\AccountStatus;
use App\Enums\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\Authentication\OtpDeliveryService;
use App\Services\Authentication\OtpService;
use App\Support\NikManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class ApplicantActivationController extends Controller
{
    public function __construct(
        protected OtpDeliveryService $deliveryService
    ) {}

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
        $registrationNumber = $user->applicant?->registrations()->latest()->first()?->registration_number ?? '—';
        $applicantName = $user->applicant?->full_name ?? $user->name;

        return view('auth.applicant-verify-otp', [
            'user' => $user,
            'maskedPhone' => $maskedPhone,
            'registrationNumber' => $registrationNumber,
            'applicantName' => $applicantName,
        ]);
    }

    /**
     * Verify submitted OTP code, activate account, and dispatch auto-generated temporary password via WhatsApp.
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

        // 1. Generate strong cryptographically secure temporary password (10 alphanumeric chars)
        $plainTempPassword = self::generateTemporaryPassword(10);

        // 2. Persist account activation and temporary password flags
        $user->update([
            'account_status' => AccountStatus::ACTIVE,
            'phone_verified_at' => now(),
            'password' => Hash::make($plainTempPassword),
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);

        $registration = $user->applicant?->registrations()->latest()->first();
        $regNumber = $registration?->registration_number ?? '—';

        // 3. Dispatch temporary password via WhatsApp Fonnte (separate message)
        $pwDispatched = false;
        try {
            $pwDispatched = $this->deliveryService->sendTemporaryPassword(
                phone: (string) $user->phone,
                temporaryPassword: $plainTempPassword,
                user: $user,
                registrationNumber: $regNumber,
                isResend: false
            );
        } catch (\Throwable) {
            $pwDispatched = false;
        }

        // Clean up OTP activation session
        session()->forget('activation_user_id');
        session([
            'activated_user_id' => $user->id,
            'password_dispatched' => $pwDispatched,
        ]);

        if ($pwDispatched) {
            return redirect()->route('login')->with(
                'success',
                'Verifikasi nomor WhatsApp berhasil! Password sementara telah dikirimkan ke WhatsApp Anda. Silakan masuk menggunakan NIK dan password tersebut.'
            );
        }

        return redirect()->route('login')->with(
            'warning',
            'Verifikasi nomor WhatsApp berhasil dan akun Anda telah aktif. Namun pengiriman WhatsApp password sementara belum dapat dipastikan. Jika belum menerima pesan, silakan gunakan fitur kirim ulang password sementara.'
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
     * Resend a new temporary password to an activated applicant whose phone is verified.
     */
    public function resendTemporaryPassword(Request $request): RedirectResponse
    {
        $userId = session('activated_user_id');
        $user = null;

        if ($userId) {
            $user = User::find($userId);
        }

        if (! $user && $request->filled('nik')) {
            $nikHash = NikManager::hash(NikManager::normalize((string) $request->input('nik')));
            $user = User::where('nik_lookup_hash', $nikHash)->first();
        }

        if (! $user || ! $user->isApplicant() || $user->account_status !== AccountStatus::ACTIVE || ! $user->phone_verified_at) {
            return back()->with('error', 'Akun pendaftar tidak ditemukan atau belum menyelesaikan verifikasi nomor WhatsApp.');
        }

        if (! $user->must_change_password) {
            return redirect()->route('login')->with('info', 'Akun Anda sudah memiliki kata sandi permanen. Silakan gunakan fitur Lupa Kata Sandi jika tidak mengingatnya.');
        }

        $throttleKey = 'resend-temp-pw:'.$user->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->with('error', "Mohon tunggu {$seconds} detik sebelum meminta pengiriman ulang password sementara.");
        }

        RateLimiter::hit($throttleKey, 600);

        // Rotate temporary password
        $newPlainPassword = self::generateTemporaryPassword(10);
        $user->update([
            'password' => Hash::make($newPlainPassword),
            'must_change_password' => true,
        ]);

        $regNumber = $user->applicant?->registrations()->latest()->first()?->registration_number ?? '—';

        $dispatched = false;
        try {
            $dispatched = $this->deliveryService->sendTemporaryPassword(
                phone: (string) $user->phone,
                temporaryPassword: $newPlainPassword,
                user: $user,
                registrationNumber: $regNumber,
                isResend: true
            );
        } catch (\Throwable) {
            $dispatched = false;
        }

        if ($dispatched) {
            return back()->with('success', 'Password sementara baru telah dikirimkan ke WhatsApp Anda. Password lama kini sudah tidak berlaku.');
        }

        return back()->with('warning', 'Password sementara baru telah diterbitkan, namun koneksi pengiriman pesan WhatsApp mengalami kendala. Silakan coba kembali dalam beberapa saat.');
    }

    /**
     * Legacy password form redirect for backward compatibility.
     */
    public function showPasswordForm(): RedirectResponse
    {
        return redirect()->route('login')
            ->with('info', 'Password sementara dibuat otomatis dan dikirim melalui WhatsApp setelah verifikasi OTP.');
    }

    /**
     * Legacy save password redirect for backward compatibility.
     */
    public function savePassword(): RedirectResponse
    {
        return redirect()->route('login');
    }

    /**
     * Generate cryptographically secure alphanumeric temporary password.
     */
    public static function generateTemporaryPassword(int $length = 10): string
    {
        // Unambiguous characters avoiding 0, O, 1, I, l
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz';
        $max = strlen($chars) - 1;
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }

        return $password;
    }

    /**
     * Mask phone number for UI display.
     */
    private function maskPhone(string $phone): string
    {
        $clean = preg_replace('/\D/', '', $phone) ?? '';
        $len = strlen($clean);

        if ($len <= 4) {
            return '****';
        }

        return substr($clean, 0, 4).str_repeat('*', max(1, $len - 7)).substr($clean, -3);
    }
}
