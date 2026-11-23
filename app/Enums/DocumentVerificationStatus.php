<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentVerificationStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case REVISION_REQUIRED = 'REVISION_REQUIRED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu pemeriksaan',
            self::APPROVED => 'Diterima',
            self::REJECTED => 'Ditolak',
            self::REVISION_REQUIRED => 'Perlu perbaikan',
        };
    }
}
