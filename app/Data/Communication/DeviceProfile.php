<?php

declare(strict_types=1);

namespace App\Data\Communication;

final readonly class DeviceProfile
{
    public function __construct(
        public ?string $senderIdentifier,
        public ?string $senderName,
        public string $status,
        public array $safeMetadata = [],
    ) {}
}
