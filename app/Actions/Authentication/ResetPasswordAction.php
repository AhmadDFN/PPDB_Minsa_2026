<?php

declare(strict_types=1);

namespace App\Actions\Authentication;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ResetPasswordAction
{
    /**
     * Reset applicant password after successful OTP verification.
     * Invalidates existing remember tokens to terminate stale sessions.
     *
     * @throws InvalidArgumentException
     */
    public function execute(User $user, string $newPassword): User
    {
        if ($user->phone_verified_at === null) {
            throw new InvalidArgumentException('Pemulihan kata sandi otomatis memerlukan nomor telepon yang sudah terverifikasi.');
        }

        $user->update([
            'password' => Hash::make($newPassword),
            'remember_token' => Str::random(60),
        ]);

        return $user->fresh();
    }
}
