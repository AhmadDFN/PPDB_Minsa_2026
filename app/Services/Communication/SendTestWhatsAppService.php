<?php

declare(strict_types=1);

namespace App\Services\Communication;

use App\Data\Communication\SendMessageResult;
use App\Services\Authentication\Delivery\FonnteOtpDelivery;

class SendTestWhatsAppService
{
    public function __construct(
        private readonly CommunicationChannelService $channels,
        private readonly WhatsAppProviderRegistry $providers,
    ) {}

    public function send(string $phone): SendMessageResult
    {
        $target = FonnteOtpDelivery::normalizePhone($phone);
        $message = 'Pesan uji sistem PPDB MIN 1 Kota Madiun. Tidak ada tindakan yang perlu dilakukan. Mohon konfirmasi penerimaan kepada petugas penguji.';

        if ($this->channels->databaseModeEnabled()) {
            return $this->channels->sendMessage($target, $message);
        }

        $token = config('services.fonnte.token');
        if (! is_string($token) || $token === '') {
            return new SendMessageResult(false, 'error', null, 'credential_unavailable');
        }

        return $this->providers->get('fonnte')->sendMessage($token, $target, $message);
    }
}
