<?php

declare(strict_types=1);

namespace App\Services\Communication;

use App\Contracts\WhatsAppProviderInterface;
use App\Services\Communication\Providers\FonnteProvider;
use InvalidArgumentException;

class WhatsAppProviderRegistry
{
    /** @return array<string, WhatsAppProviderInterface> */
    public function all(): array
    {
        return ['fonnte' => app(FonnteProvider::class)];
    }

    public function get(string $code): WhatsAppProviderInterface
    {
        $provider = $this->all()[$code] ?? null;

        if (! $provider) {
            throw new InvalidArgumentException('Provider WhatsApp belum tersedia.');
        }

        return $provider;
    }
}
