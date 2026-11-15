<?php

declare(strict_types=1);

namespace App\Services\Authentication\Delivery;

use App\Contracts\OtpDeliveryInterface;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class LogOtpDelivery implements OtpDeliveryInterface
{
    /**
     * Send OTP by writing to application log (development/testing only).
     * In production, the plaintext OTP is NEVER written to logs.
     */
    public function send(string $phone, string $otp, string $purpose, User $user): bool
    {
        if (App::isProduction()) {
            Log::info('Permintaan pengiriman OTP dicatat (mode produksi). Plaintext OTP dirahasiakan.', [
                'user_id' => $user->id,
                'purpose' => $purpose,
                'phone_masked' => substr($phone, 0, 4).'****'.substr($phone, -3),
            ]);

            return true;
        }

        // Non-production: log clearly for developer convenience
        Log::info('[DEV OTP SIMULATOR] Kode Verifikasi PPDB MIN 1 Kota Madiun', [
            'user_id' => $user->id,
            'phone' => $phone,
            'otp' => $otp,
            'purpose' => $purpose,
        ]);

        return true;
    }
}
