<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PdfDocumentType;
use Database\Factories\RegistrationPdfVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationPdfVersion extends Model
{
    /** @use HasFactory<RegistrationPdfVersionFactory> */
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'document_type',
        'version_number',
        'data_revision',
        'file_path',
        'file_hash',
        'storage_disk',
        'mime_type',
        'file_size_bytes',
        'verification_token_hash',
        'generated_by',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => PdfDocumentType::class,
            'version_number' => 'integer',
            'data_revision' => 'integer',
            'file_size_bytes' => 'integer',
            'generated_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Check if this PDF version is outdated compared to the registration's current data revision.
     */
    public function isOutdated(): bool
    {
        return $this->data_revision !== $this->registration->data_revision;
    }
}
