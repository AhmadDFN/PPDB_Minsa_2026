<?php

declare(strict_types=1);

namespace App\Services\Communication;

use App\Models\CommunicationAuditLog;
use App\Models\CommunicationChannel;
use App\Models\User;

class CommunicationAuditService
{
    public function record(string $event, ?CommunicationChannel $channel, ?User $actor, array $metadata = []): void
    {
        CommunicationAuditLog::query()->create([
            'user_id' => $actor?->id,
            'communication_channel_id' => $channel?->id,
            'event' => $event,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
