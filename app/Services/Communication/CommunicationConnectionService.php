<?php

declare(strict_types=1);

namespace App\Services\Communication;

use App\Models\CommunicationChannel;
use App\Models\CommunicationSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommunicationConnectionService
{
    public function __construct(
        private readonly WhatsAppProviderResolver $resolver,
        private readonly CommunicationCredentialService $credentials,
        private readonly CommunicationAuditService $audit,
    ) {}

    public function validate(CommunicationChannel $channel, ?User $actor = null): bool
    {
        $token = $this->credentials->reveal($channel);
        $result = $token ? $this->resolver->provider($channel)->validateConnection($token) : null;

        $channel->forceFill([
            'connection_status' => $result?->connected ? 'connected' : ($result?->valid ? 'disconnected' : 'error'),
            'sender_identifier' => $result?->deviceProfile?->senderIdentifier ?? $channel->sender_identifier,
            'sender_name' => $result?->deviceProfile?->senderName ?? $channel->sender_name,
            'safe_metadata' => $result?->deviceProfile?->safeMetadata,
            'last_validated_at' => now(),
            'last_connection_error_code' => $result?->errorCode,
            'last_connection_error_at' => $result?->errorCode ? now() : null,
            'updated_by' => $actor?->id,
        ])->save();

        $this->audit->record('connection_validated', $channel, $actor, ['status' => $channel->connection_status, 'error_code' => $result?->errorCode]);
        if (! $result?->connected) {
            $this->audit->record('provider_connection_failure', $channel, $actor, ['error_code' => $result?->errorCode, 'status' => $channel->connection_status]);
        }

        return (bool) $result?->connected;
    }

    public function activate(CommunicationChannel $channel, User $actor): bool
    {
        if ($channel->connection_status !== 'connected') {
            return false;
        }

        return DB::transaction(function () use ($channel, $actor): bool {
            CommunicationSetting::singleton();
            $setting = CommunicationSetting::query()->whereKey(1)->lockForUpdate()->firstOrFail();
            $channel = CommunicationChannel::query()->lockForUpdate()->findOrFail($channel->id);

            if ($channel->connection_status !== 'connected') {
                return false;
            }

            $channel->forceFill(['updated_by' => $actor->id])->save();
            $setting->forceFill(['active_channel_id' => $channel->id, 'database_provider_mode_enabled' => true])->save();
            $this->audit->record('channel_activated', $channel, $actor, ['provider_code' => $channel->provider_code]);

            return true;
        });
    }

    public function deactivate(CommunicationChannel $channel, User $actor): void
    {
        DB::transaction(function () use ($channel, $actor): void {
            CommunicationSetting::singleton();
            $setting = CommunicationSetting::query()->whereKey(1)->lockForUpdate()->firstOrFail();
            $channel = CommunicationChannel::query()->lockForUpdate()->findOrFail($channel->id);
            $channel->forceFill(['updated_by' => $actor->id])->save();
            if ($setting->active_channel_id === $channel->id) {
                $setting->forceFill(['active_channel_id' => null])->save();
            }
            $this->audit->record('channel_deactivated', $channel, $actor);
        });
    }
}
