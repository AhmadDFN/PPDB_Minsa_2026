<?php

declare(strict_types=1);

namespace App\Services\Authentication;

use App\Contracts\OtpDeliveryInterface;
use App\Enums\OtpPurpose;
use App\Models\User;
use App\Models\UserOtpToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use InvalidArgumentException;
use RuntimeException;

class OtpService
{
    public function __construct(
        protected OtpDeliveryInterface $delivery
    ) {}

    /**
     * Compute HMAC-SHA256 hash using the dedicated OTP secret key.
     * Binds user ID, purpose, plain OTP, and phone to prevent cross-context token reuse.
     *
     * @throws RuntimeException
     */
    public static function computeHash(int|string $userId, string $purpose, string $plainOtp, string $phone): string
    {
        $key = config('ppdb.otp.hmac_key');
        if (empty($key) || ! is_string($key)) {
            throw new RuntimeException('Kunci rahasia OTP_HMAC_KEY belum dikonfigurasi pada sistem.');
        }

        $appKey = config('app.key');
        if (! empty($appKey) && $key === $appKey) {
            throw new RuntimeException('OTP_HMAC_KEY tidak boleh sama dengan APP_KEY.');
        }

        $nikKey = config('ppdb.nik_hmac_key');
        if (! empty($nikKey) && $key === $nikKey) {
            throw new RuntimeException('OTP_HMAC_KEY tidak boleh sama dengan NIK_HMAC_KEY.');
        }

        $normalizedPhone = preg_replace('/\D/', '', $phone);
        $context = "{$userId}|{$purpose}|{$plainOtp}|{$normalizedPhone}";

        return hash_hmac('sha256', $context, $key);
    }

    /**
     * Generate and dispatch a fresh 6-digit OTP token for the specified user and purpose.
     *
     * @return array{token: UserOtpToken, plain_otp: string}
     *
     * @throws RuntimeException
     */
    public function generateAndSend(User $user, OtpPurpose|string $purpose, ?string $phone = null): array
    {
        $targetPhone = $phone ?? $user->phone;
        if (empty($targetPhone)) {
            throw new InvalidArgumentException('Nomor telepon tujuan pengiriman OTP tidak boleh kosong.');
        }

        $purposeValue = $purpose instanceof OtpPurpose ? $purpose->value : $purpose;

        // Invalidate previous unconsumed tokens for this user and purpose
        $this->invalidatePrevious($user, $purposeValue);

        // Generate cryptographically secure 6-digit OTP code
        $length = (int) config('ppdb.otp.length', 6);
        $min = (int) ('1'.str_repeat('0', $length - 1));
        $max = (int) str_repeat('9', $length);
        $plainOtp = (string) random_int($min, $max);

        // Compute HMAC hash
        $otpHash = self::computeHash($user->id, $purposeValue, $plainOtp, $targetPhone);

        $expiryMinutes = (int) config('ppdb.otp.expiry_minutes', 5);
        $maxAttempts = (int) config('ppdb.otp.max_attempts', 5);

        $token = UserOtpToken::create([
            'user_id' => $user->id,
            'phone' => $targetPhone,
            'purpose' => $purposeValue,
            'otp_hash' => $otpHash,
            'attempts' => 0,
            'max_attempts' => $maxAttempts,
            'expires_at' => Carbon::now()->addMinutes($expiryMinutes),
            'verified_at' => null,
        ]);

        // Dispatch via delivery abstraction
        $this->delivery->send($targetPhone, $plainOtp, $purposeValue, $user);

        return [
            'token' => $token,
            'plain_otp' => $plainOtp,
        ];
    }

    /**
     * Invalidate all unverified tokens for a user and purpose.
     */
    public function invalidatePrevious(User $user, string $purpose): void
    {
        UserOtpToken::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->update([
                'expires_at' => Carbon::now()->subMinute(),
            ]);
    }

    /**
     * Verify submitted OTP using database locking to prevent concurrent reuse.
     *
     * @return array{success: bool, message: string, token: ?UserOtpToken}
     */
    public function verify(User $user, OtpPurpose|string $purpose, string $plainOtp): array
    {
        $purposeValue = $purpose instanceof OtpPurpose ? $purpose->value : $purpose;

        return DB::transaction(function () use ($user, $purposeValue, $plainOtp) {
            /** @var UserOtpToken|null $token */
            $token = UserOtpToken::where('user_id', $user->id)
                ->where('purpose', $purposeValue)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $token) {
                return [
                    'success' => false,
                    'message' => 'Kode OTP tidak ditemukan atau belum pernah diminta.',
                    'token' => null,
                ];
            }

            if ($token->isVerified()) {
                return [
                    'success' => false,
                    'message' => 'Kode OTP ini sudah digunakan sebelumnya. Silakan minta kode baru.',
                    'token' => $token,
                ];
            }

            if ($token->isExpired()) {
                return [
                    'success' => false,
                    'message' => 'Kode OTP telah kedaluwarsa. Silakan minta kode verifikasi baru.',
                    'token' => $token,
                ];
            }

            if ($token->isExhausted()) {
                return [
                    'success' => false,
                    'message' => 'Batas maksimal percobaan OTP telah terlampaui. Silakan minta kode baru.',
                    'token' => $token,
                ];
            }

            // Increment attempt count
            $token->increment('attempts');

            $expectedHash = self::computeHash($user->id, $purposeValue, $plainOtp, $token->phone);

            if (! hash_equals($token->otp_hash, $expectedHash)) {
                $remaining = max(0, $token->max_attempts - $token->attempts);
                $message = $remaining > 0
                    ? "Kode OTP tidak cocok. Sisa percobaan: {$remaining} kali."
                    : 'Batas maksimal percobaan OTP telah habis. Silakan kirim ulang kode baru.';

                return [
                    'success' => false,
                    'message' => $message,
                    'token' => $token,
                ];
            }

            // OTP verified successfully
            $token->update([
                'verified_at' => Carbon::now(),
            ]);

            return [
                'success' => true,
                'message' => 'Kode OTP berhasil diverifikasi.',
                'token' => $token,
            ];
        });
    }

    /**
     * Check if sending OTP is rate limited.
     */
    public function isSendRateLimited(string $identifier, int $maxAttempts = 3, int $decaySeconds = 300): bool
    {
        return RateLimiter::tooManyAttempts("otp-send:{$identifier}", $maxAttempts);
    }

    /**
     * Hit the send OTP rate limiter.
     */
    public function hitSendRateLimiter(string $identifier, int $decaySeconds = 300): void
    {
        RateLimiter::hit("otp-send:{$identifier}", $decaySeconds);
    }

    /**
     * Seconds until send rate limit resets.
     */
    public function sendRateLimitAvailableIn(string $identifier): int
    {
        return RateLimiter::availableIn("otp-send:{$identifier}");
    }
}
