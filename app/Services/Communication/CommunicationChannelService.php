<?php

declare(strict_types=1);

namespace App\Services\Communication;

use App\Data\Communication\SendMessageResult;
use App\Models\CommunicationChannel;
use App\Models\CommunicationSetting;
use Illuminate\Database\QueryException;

class CommunicationChannelService
{
    public function __construct(
        private readonly WhatsAppProviderResolver $resolver,
        private readonly CommunicationCredentialService $credentials,
    ) {}

    public function databaseModeEnabled(): bool
    {
        try {
            return (bool) CommunicationSetting::query()->value('database_provider_mode_enabled');
        } catch (QueryException $e) {
            if ($e->getCode() === '42S02' || str_contains($e->getMessage(), '1146') || str_contains($e->getMessage(), 'no such table')) {
                return false;
            }

            throw $e;
        }
    }

    public function sendMessage(string $target, string $message): SendMessageResult
    {
        $channel = $this->resolver->activeChannel();

        if (! $channel) {
            return new SendMessageResult(false, 'error', null, 'active_channel_unavailable');
        }

        $token = $this->credentials->reveal($channel);
        if (! $token) {
            return new SendMessageResult(false, 'error', null, 'credential_unavailable');
        }

        return $this->resolver->provider($channel)->sendMessage($token, $target, $message);
    }

    public function channels()
    {
        return CommunicationChannel::query()->with('credential')->latest()->get();
    }
}
