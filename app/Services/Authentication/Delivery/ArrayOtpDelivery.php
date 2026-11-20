<?php

declare(strict_types=1);

namespace App\Services\Authentication\Delivery;

use App\Contracts\OtpDeliveryInterface;
use App\Models\User;

class ArrayOtpDelivery implements OtpDeliveryInterface
{
    /**
     * In-memory store of dispatched OTPs.
     *
     * @var list<array{phone: string, otp: string, purpose: string, user_id: int}>
     */
    protected static array $dispatched = [];

    /**
     * In-memory store of dispatched temporary passwords.
     *
     * @var list<array{phone: string, temporary_password: string, user_id: int, registration_number: string, is_resend: bool}>
     */
    protected static array $dispatchedPasswords = [];

    public function send(string $phone, string $otp, string $purpose, User $user): bool
    {
        self::$dispatched[] = [
            'phone' => $phone,
            'otp' => $otp,
            'purpose' => $purpose,
            'user_id' => $user->id,
        ];

        return true;
    }

    public function sendTemporaryPassword(
        string $phone,
        string $temporaryPassword,
        User $user,
        string $registrationNumber,
        bool $isResend = false
    ): bool {
        self::$dispatchedPasswords[] = [
            'phone' => $phone,
            'temporary_password' => $temporaryPassword,
            'user_id' => $user->id,
            'nik' => $user->getDecryptedNik(),
            'registration_number' => $registrationNumber,
            'is_resend' => $isResend,
        ];

        return true;
    }

    /**
     * Get the latest OTP code sent to a specific phone number.
     */
    public static function latestOtpFor(string $phone): ?string
    {
        for ($i = count(self::$dispatched) - 1; $i >= 0; $i--) {
            if (self::$dispatched[$i]['phone'] === $phone) {
                return self::$dispatched[$i]['otp'];
            }
        }

        return null;
    }

    /**
     * Get the latest temporary password sent to a specific phone number.
     */
    public static function latestPasswordFor(string $phone): ?string
    {
        for ($i = count(self::$dispatchedPasswords) - 1; $i >= 0; $i--) {
            if (self::$dispatchedPasswords[$i]['phone'] === $phone) {
                return self::$dispatchedPasswords[$i]['temporary_password'];
            }
        }

        return null;
    }

    /**
     * Get all dispatched OTP entries.
     *
     * @return list<array{phone: string, otp: string, purpose: string, user_id: int}>
     */
    public static function all(): array
    {
        return self::$dispatched;
    }

    /**
     * Get all dispatched password entries.
     *
     * @return list<array{phone: string, temporary_password: string, user_id: int, registration_number: string, is_resend: bool}>
     */
    public static function allPasswords(): array
    {
        return self::$dispatchedPasswords;
    }

    /**
     * Clear all recorded deliveries.
     */
    public static function clear(): void
    {
        self::$dispatched = [];
        self::$dispatchedPasswords = [];
    }
}
