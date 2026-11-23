<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'communication_channel_id', 'event', 'metadata', 'created_at'];

    protected $casts = ['metadata' => 'array', 'created_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(CommunicationChannel::class, 'communication_channel_id');
    }
}
