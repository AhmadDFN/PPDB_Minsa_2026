<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommunicationChannel extends Model
{
    protected $fillable = [
        'provider_code', 'channel_name', 'sender_identifier', 'sender_name',
        'connection_status', 'last_validated_at', 'last_connection_error_code',
        'last_connection_error_at', 'safe_metadata', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'safe_metadata' => 'array',
        'last_validated_at' => 'datetime',
        'last_connection_error_at' => 'datetime',
    ];

    protected $hidden = ['created_by', 'updated_by'];

    public function credential(): HasOne
    {
        return $this->hasOne(CommunicationChannelCredential::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
