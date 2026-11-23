<?php

declare(strict_types=1);

use App\Models\CommunicationChannel;
use App\Models\CommunicationSetting;
use App\Models\User;
use App\Services\Communication\CommunicationAuditService;
use App\Services\Communication\CommunicationConnectionService;
use App\Services\Communication\CommunicationCredentialService;
use App\Services\Communication\Providers\FonnteProvider;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['database.default' => 'mysql_test']);

$args = array_slice($argv, 1);
$operation = $args[0] ?? '';
$barrier = $args[1] ?? '';
$worker = $args[2] ?? '';
$channelId = (int) ($args[3] ?? 0);
$actorId = (int) ($args[4] ?? 0);
$token = (string) ($args[5] ?? '');
$expectedVersion = (int) ($args[6] ?? 0);

if ($operation === '' || $barrier === '' || $worker === '') {
    fwrite(STDERR, "invalid worker arguments\n");
    exit(2);
}

$signal = static function (string $name) use ($barrier, $worker): void {
    touch($barrier.DIRECTORY_SEPARATOR.$name.'-'.$worker);
};
$wait = static function (string $name, int $count = 2, int $timeout = 30) use ($barrier): void {
    $deadline = microtime(true) + $timeout;
    do {
        if (count(glob($barrier.DIRECTORY_SEPARATOR.$name.'-*') ?: []) >= $count) {
            return;
        }
        usleep(20_000);
    } while (microtime(true) < $deadline);
    throw new RuntimeException('barrier_timeout_'.$name);
};
$awaitRelease = static function (string $name = 'release') use ($barrier): void {
    $deadline = microtime(true) + 30;
    $path = $barrier.DIRECTORY_SEPARATOR.$name;
    while (! file_exists($path)) {
        if (microtime(true) >= $deadline) {
            throw new RuntimeException('release_timeout_'.$name);
        }
        usleep(20_000);
    }
};

try {
    if ($operation === 'crash') {
        throw new RuntimeException('controlled_worker_failure');
    }

    if ($operation === 'singleton') {
        $signal('ready');
        $awaitRelease();
        CommunicationSetting::singleton();
        echo json_encode(['ok' => true], JSON_THROW_ON_ERROR);
        exit(0);
    }

    if ($operation === 'activate' || $operation === 'activate_fail') {
        $signal('ready');
        $awaitRelease();
        $signal('invoked');
        if ($operation === 'activate_fail') {
            $app->instance(CommunicationAuditService::class, new class extends CommunicationAuditService
            {
                public function record(string $event, ?CommunicationChannel $channel, ?User $actor, array $metadata = []): void
                {
                    if ($event === 'channel_activated') {
                        throw new RuntimeException('controlled_audit_failure');
                    }
                    parent::record($event, $channel, $actor, $metadata);
                }
            });
        }
        $result = app(CommunicationConnectionService::class)->activate(
            CommunicationChannel::query()->findOrFail($channelId),
            User::query()->findOrFail($actorId),
        );
        $signal('done');
        echo json_encode(['ok' => true, 'activated' => $result], JSON_THROW_ON_ERROR);
        exit(0);
    }

    if ($operation === 'hold_lock') {
        DB::connection('mysql_test')->transaction(function () use ($signal, $awaitRelease): void {
            CommunicationSetting::query()->whereKey(1)->lockForUpdate()->firstOrFail();
            $signal('locked');
            $awaitRelease('release_lock');
        });
        echo json_encode(['ok' => true], JSON_THROW_ON_ERROR);
        exit(0);
    }

    if ($operation === 'credential') {
        Http::fake(function (Request $request) use ($signal, $wait): Response {
            if ($request->url() !== 'https://api.fonnte.com/device') {
                throw new RuntimeException('unexpected_provider_url');
            }
            $signal('validated');
            $wait('validated');

            return Http::response([
                'status' => true,
                'device_status' => 'connect',
                'device_number' => '628000000000',
                'device_name' => 'CI Device',
            ]);
        });
        $signal('ready');
        $awaitRelease();
        $profile = app(FonnteProvider::class)->validateConnection($token);
        if (! $profile->connected || ! $profile->deviceProfile) {
            throw new RuntimeException('fake_provider_validation_failed');
        }
        app(CommunicationCredentialService::class)->updateValidated(
            CommunicationChannel::query()->findOrFail($channelId),
            $token,
            $expectedVersion,
            ['sender_identifier' => $profile->deviceProfile->senderIdentifier, 'sender_name' => $profile->deviceProfile->senderName, 'safe_metadata' => $profile->deviceProfile->safeMetadata, 'connection_status' => 'connected', 'last_validated_at' => now()],
            User::query()->findOrFail($actorId),
        );
        echo json_encode(['ok' => true, 'updated' => true], JSON_THROW_ON_ERROR);
        exit(0);
    }

    throw new RuntimeException('unknown_operation');
} catch (Throwable $exception) {
    echo json_encode(['ok' => false, 'error' => $exception->getMessage()], JSON_THROW_ON_ERROR);
    exit(1);
}
