<?php

declare(strict_types=1);

namespace App\Enums;

enum RegistrationStatus: string
{
    case DRAFT = 'DRAFT';
    case SUBMITTED = 'SUBMITTED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf (Belum Dikirim)',
            self::SUBMITTED => 'Terkirim (Resmi)',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::DRAFT => 'warning',
            self::SUBMITTED => 'success',
        };
    }
}
