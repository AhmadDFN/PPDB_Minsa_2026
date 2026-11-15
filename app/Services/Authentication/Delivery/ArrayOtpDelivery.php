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
     * Get all dispatched OTP entries.
     *
     * @return list<array{phone: string, otp: string, purpose: string, user_id: int}>
     */
    public static function all(): array
    {
        return self::$dispatched;
    }

    /**
     * Clear the dispatched store.
     */
    public static function clear(): void
    {
        self::$dispatched = [];
    }
}
