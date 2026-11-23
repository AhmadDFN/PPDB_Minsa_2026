<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentVerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationDocumentVerification extends Model
{
    public $timestamps = false;

    protected $fillable = ['registration_document_id', 'actor_id', 'from_status', 'to_status', 'reason', 'created_at'];

    protected function casts(): array
    {
        return ['from_status' => DocumentVerificationStatus::class, 'to_status' => DocumentVerificationStatus::class, 'created_at' => 'datetime'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(RegistrationDocument::class, 'registration_document_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
