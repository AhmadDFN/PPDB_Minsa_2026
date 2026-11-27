<?php

declare(strict_types=1);

namespace App\Services\Communication\Providers;

use App\Contracts\WhatsAppProviderInterface;
use App\Data\Communication\ConnectionResult;
use App\Data\Communication\DeviceProfile;
use App\Data\Communication\SendMessageResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

class FonnteProvider implements WhatsAppProviderInterface
{
    private const DEVICE_URL = 'https://api.fonnte.com/device';

    public function code(): string
    {
        return 'fonnte';
    }

    public function name(): string
    {
        return 'Fonnte';
    }

    public function validateConnection(string $deviceToken): ConnectionResult
    {
        try {
            $response = $this->client($deviceToken)->post(self::DEVICE_URL);
            $data = $response->json();

            if (! $response->successful() || ! is_array($data) || ! $this->isTruthy($data['status'] ?? false)) {
                return new ConnectionResult(false, false, null, 'provider_rejected');
            }

            $profile = $this->profileFrom($data);

            return new ConnectionResult(true, strtolower($profile->status) === 'connect', $profile,
                strtolower($profile->status) === 'connect' ? null : 'device_disconnected');
        } catch (ConnectionException) {
            return new ConnectionResult(false, false, null, 'connection_timeout');
        } catch (Throwable) {
            return new ConnectionResult(false, false, null, 'provider_response_invalid');
        }
    }

    public function getDeviceProfile(string $deviceToken): DeviceProfile
    {
        $result = $this->validateConnection($deviceToken);

        if (! $result->valid || ! $result->deviceProfile) {
            return new DeviceProfile(null, null, 'error', ['error_code' => $result->errorCode]);
        }

        return $result->deviceProfile;
    }

    public function sendMessage(string $deviceToken, string $target, string $message): SendMessageResult
    {
        if (trim($target) === '' || ! preg_match('/^\+?[1-9][0-9]{6,14}$/', trim($target))) {
            return new SendMessageResult(false, 'error', null, 'invalid_target');
        }

        if (trim($message) === '') {
            return new SendMessageResult(false, 'error', null, 'invalid_message');
        }

        try {
            $response = $this->client($deviceToken)->asForm()->post((string) config('services.fonnte.url', 'https://api.fonnte.com/send'), [
                'target' => $target,
                'message' => $message,
                'countryCode' => (string) config('services.fonnte.country_code', '62'),
            ]);
            $data = $response->json();

            if (! $response->successful() || ! is_array($data)) {
                return new SendMessageResult(false, 'error', null, 'provider_response_invalid');
            }

            $accepted = $this->isTruthy($data['status'] ?? false);
            $rawId = $data['id'] ?? $data['message_id'] ?? null;
            $firstId = is_array($rawId) ? ($rawId[0] ?? null) : $rawId;
            $messageId = is_string($firstId) || is_int($firstId) ? (string) $firstId : null;

            return new SendMessageResult($accepted, $accepted ? 'accepted' : 'rejected', $messageId, $accepted ? null : 'provider_rejected');
        } catch (ConnectionException) {
            return new SendMessageResult(false, 'ambiguous', null, 'connection_timeout');
        } catch (Throwable) {
            return new SendMessageResult(false, 'error', null, 'provider_error');
        }
    }

    private function client(string $deviceToken): PendingRequest
    {
        return Http::withHeaders(['Authorization' => $deviceToken])
            ->connectTimeout((int) config('services.fonnte.connect_timeout', 5))
            ->timeout((int) config('services.fonnte.timeout', 10));
    }

    private function profileFrom(array $data): DeviceProfile
    {
        $status = (string) ($data['device_status'] ?? $data['status_device'] ?? $data['device']['status'] ?? 'unknown');
        $device = is_array($data['device'] ?? null) ? $data['device'] : [];
        $sender = $data['device_number'] ?? $data['phone'] ?? $data['number'] ?? $device['phone'] ?? $device['number'] ?? null;
        $name = $data['device_name'] ?? $data['name'] ?? $device['name'] ?? null;

        return new DeviceProfile(
            is_scalar($sender) ? (string) $sender : null,
            is_scalar($name) ? (string) $name : null,
            $status,
            array_filter([
                'device_status' => $status,
                'device_id' => $data['device_id'] ?? $device['id'] ?? null,
            ], static fn ($value) => $value !== null && $value !== '')
        );
    }

    private function isTruthy(mixed $value): bool
    {
        return $value === true || (is_string($value) && strtolower($value) === 'true');
    }
}
