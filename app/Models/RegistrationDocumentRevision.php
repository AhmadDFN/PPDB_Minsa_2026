<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RegistrationDocumentRevisionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationDocumentRevision extends Model
{
    /** @use HasFactory<RegistrationDocumentRevisionFactory> */
    use HasFactory;

    protected $fillable = [
        'registration_document_id',
        'revision_number',
        'file_path',
        'original_name',
        'mime_type',
        'file_size_bytes',
        'file_hash',
        'uploaded_by',
        'replaced_at',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'revision_number' => 'integer',
            'file_size_bytes' => 'integer',
            'replaced_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(RegistrationDocument::class, 'registration_document_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
