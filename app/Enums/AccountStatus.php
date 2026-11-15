<?php

declare(strict_types=1);

namespace App\Enums;

enum AccountStatus: string
{
    case PENDING_ACTIVATION = 'pending_activation';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_ACTIVATION => 'Menunggu Aktivasi OTP',
            self::ACTIVE => 'Aktif',
            self::SUSPENDED => 'Ditangguhkan',
        };
    }
}
