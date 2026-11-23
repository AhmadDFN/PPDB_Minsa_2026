<?php

declare(strict_types=1);

namespace App\Data\Communication;

final readonly class ConnectionResult
{
    public function __construct(
        public bool $valid,
        public bool $connected,
        public ?DeviceProfile $deviceProfile = null,
        public ?string $errorCode = null,
    ) {}
}
