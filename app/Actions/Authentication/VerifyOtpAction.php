<?php

declare(strict_types=1);

namespace App\Actions\Authentication;

use App\Enums\OtpPurpose;
use App\Models\User;
use App\Services\Authentication\OtpService;
use Carbon\Carbon;

class VerifyOtpAction
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Verify submitted OTP code for a user and purpose.
     * Stamps phone_verified_at upon successful verification.
     *
     * @return array{success: bool, message: string, user: User}
     */
    public function execute(User $user, OtpPurpose|string $purpose, string $plainOtp): array
    {
        $result = $this->otpService->verify($user, $purpose, $plainOtp);

        if (! $result['success']) {
            return [
                'success' => false,
                'message' => $result['message'],
                'user' => $user,
            ];
        }

        // If purpose is account activation, stamp phone_verified_at
        $purposeValue = $purpose instanceof OtpPurpose ? $purpose->value : $purpose;
        if ($purposeValue === OtpPurpose::ACTIVATION->value) {
            $user->update([
                'phone_verified_at' => Carbon::now(),
            ]);
        }

        return [
            'success' => true,
            'message' => 'Kode OTP berhasil diverifikasi.',
            'user' => $user->fresh(),
        ];
    }
}
