<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RegistrationLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationLog extends Model
{
    /** @use HasFactory<RegistrationLogFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'registration_id',
        'user_id',
        'action',
        'from_revision',
        'to_revision',
        'changes',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'from_revision' => 'integer',
            'to_revision' => 'integer',
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
