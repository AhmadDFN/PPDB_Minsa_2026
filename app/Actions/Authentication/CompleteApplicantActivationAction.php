<?php

declare(strict_types=1);

namespace App\Actions\Authentication;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class CompleteApplicantActivationAction
{
    /**
     * Set password and transition account status from PENDING_ACTIVATION to ACTIVE.
     * Requires phone to have been verified prior to activation.
     *
     * @throws InvalidArgumentException
     */
    public function execute(User $user, string $password): User
    {
        if ($user->phone_verified_at === null) {
            throw new InvalidArgumentException('Nomor telepon harus diverifikasi melalui OTP terlebih dahulu.');
        }

        if ($user->account_status !== AccountStatus::PENDING_ACTIVATION && $user->account_status !== AccountStatus::ACTIVE) {
            throw new InvalidArgumentException('Status akun tidak memenuhi syarat untuk aktivasi.');
        }

        $user->update([
            'password' => Hash::make($password),
            'account_status' => AccountStatus::ACTIVE,
        ]);

        return $user->fresh();
    }
}
