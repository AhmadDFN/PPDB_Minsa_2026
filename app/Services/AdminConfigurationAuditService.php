<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AdminConfigurationAuditLog;
use App\Models\User;

class AdminConfigurationAuditService
{
    public function record(string $entityType, int $entityId, string $action, ?User $actor, ?array $before = null, ?array $after = null): void
    {
        AdminConfigurationAuditLog::query()->create([
            'user_id' => $actor?->id,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'before' => $before,
            'after' => $after,
            'created_at' => now(),
        ]);
    }
}
