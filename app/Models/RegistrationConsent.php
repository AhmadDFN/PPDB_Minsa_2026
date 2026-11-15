<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RegistrationConsentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationConsent extends Model
{
    /** @use HasFactory<RegistrationConsentFactory> */
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'statement_text',
        'is_agreed',
        'agreed_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'is_agreed' => 'boolean',
            'agreed_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
