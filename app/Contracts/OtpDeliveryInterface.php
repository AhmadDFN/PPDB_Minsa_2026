<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;

interface OtpDeliveryInterface
{
    /**
     * Send OTP verification code to the given recipient.
     *
     * @param  string  $phone  Recipient phone number
     * @param  string  $otp  Plain 6-digit OTP code
     * @param  string  $purpose  Purpose (e.g. account_activation, password_reset)
     * @param  User  $user  Associated user model
     * @return bool True if dispatched successfully
     */
    public function send(string $phone, string $otp, string $purpose, User $user): bool;
}
