<?php

declare(strict_types=1);

namespace App\Enums;

enum OtpPurpose: string
{
    case ACTIVATION = 'activation';
    case PASSWORD_RESET = 'password_reset';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVATION => 'Aktivasi Akun Baru',
            self::PASSWORD_RESET => 'Pemulihan Kata Sandi',
        };
    }
}
