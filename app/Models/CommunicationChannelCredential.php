<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationChannelCredential extends Model
{
    protected $fillable = ['communication_channel_id', 'credential_ciphertext', 'credential_version'];

    protected $hidden = ['credential_ciphertext'];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(CommunicationChannel::class, 'communication_channel_id');
    }
}
