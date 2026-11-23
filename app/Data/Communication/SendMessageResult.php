<?php

declare(strict_types=1);

namespace App\Data\Communication;

final readonly class SendMessageResult
{
    public function __construct(
        public bool $accepted,
        public ?string $status = null,
        public ?string $providerMessageId = null,
        public ?string $errorCode = null,
    ) {}
}
