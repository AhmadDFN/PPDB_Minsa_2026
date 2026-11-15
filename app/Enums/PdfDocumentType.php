<?php

declare(strict_types=1);

namespace App\Enums;

enum PdfDocumentType: string
{
    case DRAFT = 'draft';
    case FINAL = 'final';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Lembar Draf Pendaftaran (Watermark DRAFT)',
            self::FINAL => 'Bukti Pendaftaran Resmi (Final PTSP)',
        };
    }
}
