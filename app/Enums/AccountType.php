<?php

declare(strict_types=1);

namespace App\Enums;

enum AccountType: string
{
    case APPLICANT = 'applicant';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::APPLICANT => 'Calon Peserta Didik',
            self::ADMIN => 'Administrator',
        };
    }
}
