<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\Communication\ConnectionResult;
use App\Data\Communication\DeviceProfile;
use App\Data\Communication\SendMessageResult;

interface WhatsAppProviderInterface
{
    public function code(): string;

    public function name(): string;

    public function validateConnection(string $deviceToken): ConnectionResult;

    public function getDeviceProfile(string $deviceToken): DeviceProfile;

    public function sendMessage(string $deviceToken, string $target, string $message): SendMessageResult;
}
