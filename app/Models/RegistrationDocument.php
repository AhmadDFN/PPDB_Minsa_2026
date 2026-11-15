<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RegistrationDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationDocument extends Model
{
    /** @use HasFactory<RegistrationDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'document_requirement_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size_bytes',
        'file_hash',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size_bytes' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(DocumentRequirement::class, 'document_requirement_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(RegistrationDocumentRevision::class)->orderBy('revision_number', 'desc');
    }
}
