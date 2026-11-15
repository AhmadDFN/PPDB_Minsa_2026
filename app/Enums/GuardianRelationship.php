<?php

declare(strict_types=1);

namespace App\Enums;

enum GuardianRelationship: string
{
    case FATHER = 'FATHER';
    case MOTHER = 'MOTHER';
    case GUARDIAN = 'GUARDIAN';

    public function label(): string
    {
        return match ($this) {
            self::FATHER => 'Ayah Kandung',
            self::MOTHER => 'Ibu Kandung',
            self::GUARDIAN => 'Wali',
        };
    }
}
