<?php

declare(strict_types=1);

namespace App\Services\Authentication\Delivery;

use App\Contracts\OtpDeliveryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LogOtpDelivery implements OtpDeliveryInterface
{
    public function send(string $phone, string $otp, string $purpose, User $user): bool
    {
        Log::info('Dispatched OTP code via LOG driver.', [
            'user_id' => $user->id,
            'phone_masked' => FonnteOtpDelivery::maskPhone($phone),
            'purpose' => $purpose,
        ]);

        return true;
    }

    public function sendTemporaryPassword(
        string $phone,
        string $temporaryPassword,
        User $user,
        string $registrationNumber,
        bool $isResend = false
    ): bool {
        Log::info('Dispatched temporary password via LOG driver.', [
            'user_id' => $user->id,
            'phone_masked' => FonnteOtpDelivery::maskPhone($phone),
            'registration_number' => $registrationNumber,
            'is_resend' => $isResend,
        ]);

        return true;
    }
}
