<?php

declare(strict_types=1);

namespace App\Services\Authentication;

use App\Contracts\OtpDeliveryInterface;
use App\Models\User;
use App\Services\Authentication\Delivery\ArrayOtpDelivery;
use App\Services\Authentication\Delivery\FonnteOtpDelivery;
use App\Services\Authentication\Delivery\LogOtpDelivery;
use App\Services\Authentication\Delivery\NullOtpDelivery;
use InvalidArgumentException;

class OtpDeliveryService implements OtpDeliveryInterface
{
    protected ?OtpDeliveryInterface $driver = null;

    public function __construct(?OtpDeliveryInterface $driver = null)
    {
        $this->driver = $driver ?? $this->resolveDriver();
    }

    /**
     * Resolve the configured OTP delivery driver.
     */
    protected function resolveDriver(): OtpDeliveryInterface
    {
        $driverName = config('ppdb.otp.delivery_driver', 'log');

        return match ($driverName) {
            'fonnte' => new FonnteOtpDelivery,
            'log' => new LogOtpDelivery,
            'array' => new ArrayOtpDelivery,
            'null' => new NullOtpDelivery,
            default => throw new InvalidArgumentException("Driver pengiriman OTP [{$driverName}] tidak dikenali."),
        };
    }

    /**
     * Swap the active driver (useful for tests).
     */
    public function setDriver(OtpDeliveryInterface $driver): void
    {
        $this->driver = $driver;
    }

    public function send(string $phone, string $otp, string $purpose, User $user): bool
    {
        return $this->driver->send($phone, $otp, $purpose, $user);
    }
}
