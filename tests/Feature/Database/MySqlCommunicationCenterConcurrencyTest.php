<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\CommunicationAuditLog;
use App\Models\CommunicationChannel;
use App\Models\CommunicationChannelCredential;
use App\Models\CommunicationSetting;
use App\Models\User;
use App\Services\Communication\CommunicationCredentialService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;
use Throwable;

class MySqlCommunicationCenterConcurrencyTest extends TestCase
{
    private const TEST_DATABASE = 'ppdb_minsatama_cc_concurrency_test';

    private string $runId;

    private array $channelIds = [];

    private array $userIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('COMMUNICATION_CENTER_MYSQL_CONCURRENCY') !== '1') {
            $this->markTestSkipped('Communication Center MySQL concurrency tests require COMMUNICATION_CENTER_MYSQL_CONCURRENCY=1.');
        }
        if (! extension_loaded('pdo_mysql')) {
            $this->fail('CI concurrency flag is enabled but pdo_mysql is unavailable.');
        }

        $database = (string) config('database.connections.mysql_test.database');
        $applicationDatabase = (string) config('database.connections.mysql.database');
        if ($database !== self::TEST_DATABASE || $database === $applicationDatabase) {
            $this->fail('Refusing to run Communication Center concurrency tests against an unexpected database.');
        }
        config(['database.default' => 'mysql_test']);
        DB::purge('mysql_test');
        try {
            $pdo = DB::connection('mysql_test')->getPdo();
            $engine = strtolower((string) DB::connection('mysql_test')->selectOne('select @@default_storage_engine as engine')->engine);
            if (! in_array($engine, ['innodb', 'mariadb'], true)) {
                $this->fail('Communication Center concurrency tests require InnoDB-compatible MySQL/MariaDB.');
            }
            unset($pdo);
            if (! DB::connection('mysql_test')->getSchemaBuilder()->hasTable('communication_settings')) {
                $this->fail('Communication Center tables are not migrated on the disposable database.');
            }
        } catch (Throwable $exception) {
            $this->fail('CI concurrency flag is enabled but the expected MySQL connection is unavailable: '.$exception->getMessage());
        }
        $this->runId = bin2hex(random_bytes(8));
    }

    protected function tearDown(): void
    {
        if ($this->channelIds !== []) {
            CommunicationAuditLog::query()->whereIn('communication_channel_id', $this->channelIds)->delete();
            CommunicationSetting::query()->whereIn('active_channel_id', $this->channelIds)->update(['active_channel_id' => null]);
            CommunicationChannelCredential::query()->whereIn('communication_channel_id', $this->channelIds)->delete();
            CommunicationChannel::query()->whereIn('id', $this->channelIds)->delete();
        }
        if ($this->userIds !== []) {
            User::query()->whereIn('id', $this->userIds)->delete();
        }
        parent::tearDown();
    }

    public function test_simultaneous_singleton_initialization(): void
    {
        CommunicationSetting::query()->whereKey(1)->delete();
        $dir = $this->barrierDirectory();
        try {
            $processes = $this->startWorkers('singleton', $dir, 2);
            $this->releaseWorkers($dir, 2);
            $this->finishWorkers($processes);
            $this->assertSame(1, CommunicationSetting::query()->whereKey(1)->count());
            $this->assertSame(1, DB::connection('mysql_test')->table('communication_settings')->count());
        } finally {
            $this->cleanupBarrier($dir);
        }
    }

    public function test_simultaneous_activation_keeps_one_valid_pointer(): void
    {
        $actor = $this->user();
        $first = $this->channel('first');
        $second = $this->channel('second');
        CommunicationSetting::query()->updateOrCreate(['id' => 1], ['active_channel_id' => null, 'database_provider_mode_enabled' => false]);
        $dir = $this->barrierDirectory();
        try {
            $processes = $this->startWorkers('activate', $dir, 2, [$first->id, $second->id], $actor->id);
            $this->releaseWorkers($dir, 2);
            $results = $this->finishWorkers($processes, true);
            $this->assertCount(2, $results);
            $this->assertSame(1, CommunicationSetting::query()->count());
            $pointer = CommunicationSetting::query()->value('active_channel_id');
            $this->assertContains((int) $pointer, [$first->id, $second->id]);
            $this->assertSame(2, CommunicationAuditLog::query()->whereIn('communication_channel_id', [$first->id, $second->id])->where('event', 'channel_activated')->count());
        } finally {
            $this->cleanupBarrier($dir);
        }
    }

    public function test_simultaneous_credential_update_allows_one_version_winner(): void
    {
        $actor = $this->user();
        $channel = $this->channel('credential');
        $dir = $this->barrierDirectory();
        try {
            $processes = $this->startWorkers('credential', $dir, 2, [$channel->id, $channel->id], $actor->id, ['ci-token-a-'.$this->runId, 'ci-token-b-'.$this->runId], [1, 1]);
            $this->releaseWorkers($dir, 2);
            $results = $this->finishWorkers($processes, true);
            $successes = count(array_filter($results, fn (array $result): bool => ($result['ok'] ?? false) === true));
            $this->assertSame(1, $successes);
            $credential = $channel->fresh('credential')->credential;
            $this->assertSame(2, $credential->credential_version);
            $this->assertContains(Crypt::decryptString($credential->credential_ciphertext), ['ci-token-a-'.$this->runId, 'ci-token-b-'.$this->runId]);
            $this->assertSame(1, CommunicationAuditLog::query()->where('event', 'credential_changed')->count());
        } finally {
            $this->cleanupBarrier($dir);
        }
    }

    public function test_lock_contention_and_failed_activation_rollback(): void
    {
        $actor = $this->user();
        $active = $this->channel('active');
        $candidate = $this->channel('candidate');
        CommunicationSetting::query()->updateOrCreate(['id' => 1], ['active_channel_id' => $active->id, 'database_provider_mode_enabled' => true]);
        $dir = $this->barrierDirectory();
        $activationDir = $this->barrierDirectory();
        try {
            $holder = $this->startWorkers('hold_lock', $dir, 1);
            $this->waitFor($dir, 'locked', 1);
            $activation = $this->startWorkers('activate', $activationDir, 1, [$candidate->id], $actor->id);
            $this->releaseWorkers($activationDir, 1);
            $this->waitFor($activationDir, 'invoked', 1);
            $this->assertSame([], glob($activationDir.DIRECTORY_SEPARATOR.'done-*') ?: []);
            touch($dir.DIRECTORY_SEPARATOR.'release_lock');
            $this->finishWorkers($holder);
            $this->finishWorkers($activation);
            $this->assertSame($candidate->id, CommunicationSetting::query()->value('active_channel_id'));

            CommunicationSetting::query()->update(['active_channel_id' => $active->id]);
            $auditBefore = CommunicationAuditLog::query()->where('communication_channel_id', $candidate->id)->where('event', 'channel_activated')->count();
            $failed = $this->startWorkers('activate_fail', $dir, 1, [$candidate->id], $actor->id);
            touch($dir.DIRECTORY_SEPARATOR.'release');
            $this->finishWorkers($failed, true);
            $this->assertSame($active->id, CommunicationSetting::query()->value('active_channel_id'));
            $this->assertSame($auditBefore, CommunicationAuditLog::query()->where('communication_channel_id', $candidate->id)->where('event', 'channel_activated')->count());
        } finally {
            $this->cleanupBarrier($dir);
            $this->cleanupBarrier($activationDir);
        }
    }

    public function test_worker_failure_is_bounded_and_reported(): void
    {
        $dir = $this->barrierDirectory();
        try {
            $processes = $this->startWorkers('crash', $dir, 1);
            $this->finishWorkers($processes, true);
            $this->assertFalse($processes[0]->isSuccessful());
        } finally {
            $this->cleanupBarrier($dir);
        }
    }

    private function user(): User
    {
        $user = User::factory()->admin()->create(['email' => 'ci-cc-'.$this->runId.'-'.count($this->userIds).'@example.test']);
        $user->assignRole('superadmin');
        $this->userIds[] = $user->id;

        return $user;
    }

    private function channel(string $label): CommunicationChannel
    {
        $channel = CommunicationChannel::query()->create(['provider_code' => 'fonnte', 'channel_name' => 'ci-cc-'.$this->runId.'-'.$label, 'connection_status' => 'connected']);
        app(CommunicationCredentialService::class)->store($channel, 'ci-initial-'.$this->runId.'-'.$label);
        $this->channelIds[] = $channel->id;

        return $channel->fresh('credential');
    }

    private function barrierDirectory(): string
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ppdb-cc-'.$this->runId.'-'.bin2hex(random_bytes(4));
        mkdir($dir);

        return $dir;
    }

    private function startWorkers(string $operation, string $dir, int $count, array $channels = [], int $actorId = 0, array $tokens = [], array $versions = []): array
    {
        $processes = [];
        for ($i = 0; $i < $count; $i++) {
            $channelId = (int) ($channels[$i] ?? 0);
            $token = (string) ($tokens[$i] ?? '');
            $version = (int) ($versions[$i] ?? 0);
            $process = new Process([PHP_BINARY, base_path('tests/Support/CommunicationCenterConcurrencyWorker.php'), $operation, $dir, (string) $i, (string) $channelId, (string) $actorId, $token, (string) $version], base_path());
            $process->setEnv(['APP_ENV' => 'testing', 'APP_KEY' => (string) env('APP_KEY'), 'DB_CONNECTION' => 'mysql_test', 'DB_TEST_URL' => '', 'DB_TEST_HOST' => (string) config('database.connections.mysql_test.host'), 'DB_TEST_PORT' => (string) config('database.connections.mysql_test.port'), 'DB_TEST_DATABASE' => self::TEST_DATABASE, 'DB_TEST_USERNAME' => (string) config('database.connections.mysql_test.username'), 'DB_TEST_PASSWORD' => (string) config('database.connections.mysql_test.password'), 'DB_TEST_SOCKET' => (string) config('database.connections.mysql_test.unix_socket'), 'NIK_HMAC_KEY' => (string) env('NIK_HMAC_KEY'), 'OTP_HMAC_KEY' => (string) env('OTP_HMAC_KEY')]);
            $process->setTimeout(35);
            $process->start();
            $processes[] = $process;
        }

        return $processes;
    }

    private function releaseWorkers(string $dir, int $count): void
    {
        $this->waitFor($dir, 'ready', $count);
        touch($dir.DIRECTORY_SEPARATOR.'release');
    }

    private function finishWorkers(array $processes, bool $allowFailure = false): array
    {
        $results = [];
        foreach ($processes as $process) {
            $process->wait();
            if (! $allowFailure && ! $process->isSuccessful()) {
                $this->fail('Concurrency worker failed: '.trim($process->getErrorOutput()));
            }
            $output = trim($process->getOutput());
            if ($output !== '') {
                $results[] = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
            }
        }

        return $results;
    }

    private function waitFor(string $dir, string $name, int $count): void
    {
        $deadline = microtime(true) + 30;
        do {
            if (count(glob($dir.DIRECTORY_SEPARATOR.$name.'-*') ?: []) >= $count) {
                return;
            }
            usleep(20_000);
        } while (microtime(true) < $deadline);
        $this->fail('Concurrency barrier timed out: '.$name);
    }

    private function cleanupBarrier(string $dir): void
    {
        foreach (glob($dir.DIRECTORY_SEPARATOR.'*') ?: [] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        if (is_dir($dir)) {
            rmdir($dir);
        }
    }
}
