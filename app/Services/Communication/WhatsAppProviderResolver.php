<?php

declare(strict_types=1);

namespace App\Services\Communication;

use App\Contracts\WhatsAppProviderInterface;
use App\Models\CommunicationChannel;
use App\Models\CommunicationSetting;

class WhatsAppProviderResolver
{
    public function __construct(private readonly WhatsAppProviderRegistry $registry) {}

    public function activeChannel(): ?CommunicationChannel
    {
        $setting = CommunicationSetting::query()->with('activeChannel.credential')->first();

        if (! $setting?->database_provider_mode_enabled || ! $setting->activeChannel) {
            return null;
        }

        return $setting->activeChannel;
    }

    public function provider(CommunicationChannel $channel): WhatsAppProviderInterface
    {
        return $this->registry->get($channel->provider_code);
    }
}
