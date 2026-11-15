<?php

declare(strict_types=1);

namespace App\Enums;

enum PreviousEducationLevel: string
{
    case RA = 'ra';
    case TK = 'tk';
    case PAUD = 'paud';
    case OTHER = 'other';
    case NONE = 'none';

    public function label(): string
    {
        return match ($this) {
            self::RA => 'Raudhatul Athfal (RA)',
            self::TK => 'Taman Kanak-kanak (TK)',
            self::PAUD => 'PAUD / Kelompok Bermain',
            self::OTHER => 'Lainnya',
            self::NONE => 'Tidak / Belum Sekolah Formal',
        };
    }
}
