<?php

declare(strict_types=1);

namespace App\Enums;

enum AdmissionPeriodStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case CLOSED = 'closed';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf Konfigurasi',
            self::ACTIVE => 'Pendaftaran Dibuka',
            self::CLOSED => 'Pendaftaran Ditutup',
            self::ARCHIVED => 'Diarsipkan',
        };
    }

    public function isOpen(): bool
    {
        return $this === self::ACTIVE;
    }
}
