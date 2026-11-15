<?php

declare(strict_types=1);

namespace App\Services\Authentication;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminAuthenticationService
{
    /**
     * Authenticate an administrator by email and password.
     * Enforces account_type = admin, account_status = ACTIVE, and Spatie roles.
     *
     * @return array{success: bool, user: ?User, message: string, code: string}
     */
    public function authenticate(string $email, string $password, string $ipAddress): array
    {
        $throttleKey = $this->throttleKey($email, $ipAddress);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return [
                'success' => false,
                'user' => null,
                'message' => "Terlalu banyak percobaan masuk yang gagal. Silakan coba lagi dalam {$seconds} detik.",
                'code' => 'RATE_LIMITED',
            ];
        }

        /** @var User|null $user */
        $user = User::where('email', strtolower(trim($email)))->first();

        // Prevent admin login from authenticating applicant accounts or mismatched passwords
        if (! $user || $user->account_type !== AccountType::ADMIN || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return [
                'success' => false,
                'user' => null,
                'message' => 'Email atau kata sandi yang Anda masukkan salah.',
                'code' => 'INVALID_CREDENTIALS',
            ];
        }

        if ($user->account_status !== AccountStatus::ACTIVE) {
            return [
                'success' => false,
                'user' => null,
                'message' => 'Akun administrator Anda sedang tidak aktif. Silakan hubungi Superadmin.',
                'code' => 'ACCOUNT_INACTIVE',
            ];
        }

        // Verify Spatie roles
        if (! $user->hasAnyRole(['superadmin', 'admin_ppdb'])) {
            return [
                'success' => false,
                'user' => null,
                'message' => 'Akun Anda tidak memiliki hak akses administrator PPDB.',
                'code' => 'UNAUTHORIZED_ROLE',
            ];
        }

        RateLimiter::clear($throttleKey);

        return [
            'success' => true,
            'user' => $user,
            'message' => 'Autentikasi administrator berhasil.',
            'code' => 'SUCCESS',
        ];
    }

    /**
     * Compute a throttle key combining admin email and client IP.
     */
    public function throttleKey(string $email, string $ipAddress): string
    {
        return 'login:admin:'.Str::transliterate(strtolower(trim($email)).'|'.$ipAddress);
    }
}
