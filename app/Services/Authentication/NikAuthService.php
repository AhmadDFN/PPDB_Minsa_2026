<?php

declare(strict_types=1);

namespace App\Services\Authentication;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use App\Support\NikManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use InvalidArgumentException;

class NikAuthService
{
    /**
     * Authenticate an applicant by NIK and password.
     * Returns generic error messages to prevent NIK enumeration.
     *
     * @return array{success: bool, user: ?User, message: string, code: string}
     */
    public function authenticate(string $nik, string $password, string $ipAddress): array
    {
        $throttleKey = $this->throttleKey($nik, $ipAddress);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return [
                'success' => false,
                'user' => null,
                'message' => "Terlalu banyak percobaan masuk yang gagal. Silakan coba lagi dalam {$seconds} detik.",
                'code' => 'RATE_LIMITED',
            ];
        }

        try {
            $normalizedNik = NikManager::normalize($nik);
            $nikHash = NikManager::hash($normalizedNik);
        } catch (InvalidArgumentException) {
            RateLimiter::hit($throttleKey, 60);

            return [
                'success' => false,
                'user' => null,
                'message' => 'NIK atau kata sandi yang Anda masukkan salah.',
                'code' => 'INVALID_CREDENTIALS',
            ];
        }

        /** @var User|null $user */
        $user = User::where('nik_lookup_hash', $nikHash)->first();

        // Generic error if account not found, or not an applicant, or password mismatch
        if (! $user || $user->account_type !== AccountType::APPLICANT || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return [
                'success' => false,
                'user' => null,
                'message' => 'NIK atau kata sandi yang Anda masukkan salah.',
                'code' => 'INVALID_CREDENTIALS',
            ];
        }

        // Account exists and password matches, now verify account status
        if ($user->account_status === AccountStatus::PENDING_ACTIVATION) {
            return [
                'success' => false,
                'user' => $user,
                'message' => 'Akun pendaftaran Anda belum diaktifkan. Silakan selesaikan aktivasi akun melalui kode OTP terlebih dahulu.',
                'code' => 'PENDING_ACTIVATION',
            ];
        }

        if ($user->account_status === AccountStatus::SUSPENDED) {
            return [
                'success' => false,
                'user' => null,
                'message' => 'Akun Anda sedang dinonaktifkan oleh panitia PPDB. Silakan hubungi panitia PPDB MIN 1 Kota Madiun.',
                'code' => 'ACCOUNT_SUSPENDED',
            ];
        }

        if ($user->account_status !== AccountStatus::ACTIVE) {
            return [
                'success' => false,
                'user' => null,
                'message' => 'Status akun tidak valid untuk masuk ke sistem.',
                'code' => 'INVALID_STATUS',
            ];
        }

        // Clear rate limiter upon successful authentication
        RateLimiter::clear($throttleKey);

        return [
            'success' => true,
            'user' => $user,
            'message' => 'Autentikasi berhasil.',
            'code' => 'SUCCESS',
        ];
    }

    /**
     * Compute a throttle key combining hashed NIK and client IP address.
     */
    public function throttleKey(string $nik, string $ipAddress): string
    {
        $clean = preg_replace('/\D/', '', $nik);

        return 'login:applicant:'.Str::transliterate($clean.'|'.$ipAddress);
    }

    /**
     * Clear rate limiter for an applicant.
     */
    public function clearThrottle(string $nik, string $ipAddress): void
    {
        RateLimiter::clear($this->throttleKey($nik, $ipAddress));
    }
}
