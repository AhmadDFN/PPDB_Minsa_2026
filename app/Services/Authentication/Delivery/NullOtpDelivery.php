<?php

declare(strict_types=1);

namespace App\Services\Authentication\Delivery;

use App\Contracts\OtpDeliveryInterface;
use App\Models\User;

class NullOtpDelivery implements OtpDeliveryInterface
{
    public function send(string $phone, string $otp, string $purpose, User $user): bool
    {
        return true;
    }
}
