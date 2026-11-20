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
        if ($driver instanceof self) {
            $driver = $driver->getDriver();
        }

        $this->driver = $driver ?? $this->resolveDriver();
    }

    public function getDriver(): ?OtpDeliveryInterface
    {
        return $this->driver;
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
        if ($driver instanceof self) {
            $driver = $driver->getDriver() ?? $driver;
        }

        $this->driver = $driver;
    }

    public function send(string $phone, string $otp, string $purpose, User $user): bool
    {
        $activeDriver = $this->driver instanceof self ? $this->driver->getDriver() : $this->driver;

        return $activeDriver ? $activeDriver->send($phone, $otp, $purpose, $user) : true;
    }

    /**
     * Send temporary password via configured driver.
     */
    public function sendTemporaryPassword(
        string $phone,
        string $temporaryPassword,
        User $user,
        string $registrationNumber,
        bool $isResend = false
    ): bool {
        $activeDriver = $this->driver instanceof self ? $this->driver->getDriver() : $this->driver;

        if ($activeDriver instanceof FonnteOtpDelivery) {
            return $activeDriver->sendTemporaryPassword($phone, $temporaryPassword, $user, $registrationNumber, $isResend);
        }

        if ($activeDriver instanceof ArrayOtpDelivery) {
            return $activeDriver->sendTemporaryPassword($phone, $temporaryPassword, $user, $registrationNumber, $isResend);
        }

        if ($activeDriver instanceof LogOtpDelivery) {
            return $activeDriver->sendTemporaryPassword($phone, $temporaryPassword, $user, $registrationNumber, $isResend);
        }

        return true;
    }
}
