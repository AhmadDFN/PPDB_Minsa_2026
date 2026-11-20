<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Actions\Registration\SubmitRegistrationAction;
use App\Enums\AdmissionPeriodStatus;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Applicant;
use App\Models\ApplicantAddress;
use App\Models\ApplicantEducation;
use App\Models\ApplicantGuardian;
use App\Models\DocumentRequirement;
use App\Models\Program;
use App\Models\Registration;
use App\Models\RegistrationLog;
use App\Models\User;
use App\Services\Registration\RegistrationNumberGeneratorService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Process\Process;
use Tests\TestCase;
use Throwable;

class MySqlRegistrationConcurrencyTest extends TestCase
{
    private const TEST_DATABASE = 'ppdb_phase6_test';

    /**
     * @var list<int>
     */
    private array $applicantIds = [];

    /**
     * @var list<int>
     */
    private array $userIds = [];

    /**
     * @var list<int>
     */
    private array $periodIds = [];

    /**
     * @var list<array{academic_year: string, program_id: int}>
     */
    private array $registrationSequenceScopes = [];

    protected function setUp(): void
    {
        parent::setUp();

        $database = (string) config('database.connections.mysql_test.database');
        $applicationDatabase = (string) config('database.connections.mysql.database');
        $testUrl = (string) config('database.connections.mysql_test.url');

        $this->assertSame(
            self::TEST_DATABASE,
            $database,
            'Concurrency tests must use the dedicated ppdb_phase6_test database.',
        );
        $this->assertNotSame(
            $applicationDatabase,
            $database,
            'Concurrency tests must not use the application database.',
        );
        $this->assertSame(
            '',
            $testUrl,
            'Concurrency tests must not use a URL that can override the isolated database.',
        );

        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('Ekstensi pdo_mysql belum aktif di lingkungan PHP.');
        }

        try {
            DB::connection('mysql_test')->getPdo();
        } catch (Throwable) {
            $this->markTestSkipped('Server MySQL untuk pengujian integrasi tidak tersedia.');
        }

        Artisan::call('migrate', [
            '--database' => 'mysql_test',
            '--force' => true,
        ]);

        config(['database.default' => 'mysql_test']);
        DB::purge();
        DB::table('yearly_registration_sequences')->delete();
    }

    protected function tearDown(): void
    {
        if (
            $this->applicantIds !== []
            && config('database.default') === 'mysql_test'
            && config('database.connections.mysql_test.database') === self::TEST_DATABASE
        ) {
            DB::table('yearly_registration_sequences')->delete();
            Registration::query()->whereIn('applicant_id', $this->applicantIds)->delete();
            foreach ($this->registrationSequenceScopes as $scope) {
                DB::table('registration_number_sequences')
                    ->where('academic_year', $scope['academic_year'])
                    ->where('program_id', $scope['program_id'])
                    ->delete();
            }
            Applicant::query()->whereIn('id', $this->applicantIds)->delete();
            User::query()->whereIn('id', $this->userIds)->delete();
            AdmissionPeriod::query()->whereIn('id', $this->periodIds)->delete();
        }

        parent::tearDown();
    }

    public function test_two_processes_submitting_the_same_registration_issue_one_number_and_consent(): void
    {
        $registration = $this->createReadyRegistration();

        $results = $this->runWorkers([$registration->id, $registration->id]);

        $intakeYear = app(RegistrationNumberGeneratorService::class)->extractIntakeYear($registration->admissionPeriod);
        $this->assertSame($results[0]['registration_number'], $results[1]['registration_number']);
        $this->assertSame(RegistrationStatus::SUBMITTED->value, $results[0]['status']);
        $this->assertDatabaseCount('registration_consents', 1);
        $this->assertDatabaseCount('registration_logs', 1);
        $this->assertSame(1, (int) DB::table('yearly_registration_sequences')->where('intake_year', $intakeYear)->value('last_sequence'));
    }

    public function test_parallel_applicants_for_one_period_and_program_receive_distinct_sequential_numbers(): void
    {
        $firstRegistration = $this->createReadyRegistration();
        $secondRegistration = $this->createReadyRegistration(
            $firstRegistration->admissionPeriod,
            $firstRegistration->admissionPeriodProgram,
            $firstRegistration->admissionPeriod->documentRequirements()->first(),
        );

        $results = $this->runWorkers([$firstRegistration->id, $secondRegistration->id]);
        $numbers = array_column($results, 'registration_number');
        sort($numbers);

        $intakeYear = app(RegistrationNumberGeneratorService::class)->extractIntakeYear($firstRegistration->admissionPeriod);
        $prefix = substr($intakeYear, -2);

        $this->assertSame(
            [
                $prefix.'001',
                $prefix.'002',
            ],
            $numbers,
        );
        $this->assertDatabaseCount('registration_consents', 2);
        $this->assertDatabaseCount('registration_logs', 2);
        $this->assertSame(2, (int) DB::table('yearly_registration_sequences')->where('intake_year', $intakeYear)->value('last_sequence'));
    }

    public function test_parallel_submissions_in_different_periods_same_year_and_program_keep_numbers_unique(): void
    {
        $firstRegistration = $this->createReadyRegistration();
        $program = $firstRegistration->admissionPeriodProgram->program;
        $secondPeriod = AdmissionPeriod::factory()->create([
            'name' => 'PPDB MIN 1 Kota Madiun Periode Kedua',
            'academic_year' => $firstRegistration->admissionPeriod->academic_year,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);
        $this->periodIds[] = $secondPeriod->id;

        $secondPeriodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $secondPeriod->id,
            'program_id' => $program->id,
            'is_active' => true,
        ]);
        $secondRequirement = DocumentRequirement::factory()->create([
            'admission_period_id' => $secondPeriod->id,
            'program_id' => $program->id,
            'is_required' => false,
        ]);
        $secondRegistration = $this->createReadyRegistration(
            $secondPeriod,
            $secondPeriodProgram,
            $secondRequirement,
        );

        $results = $this->runWorkers([$firstRegistration->id, $secondRegistration->id]);
        $numbers = array_column($results, 'registration_number');
        sort($numbers);

        $intakeYear = app(RegistrationNumberGeneratorService::class)->extractIntakeYear($firstRegistration->admissionPeriod);
        $prefix = substr($intakeYear, -2);

        $this->assertSame(
            [
                $prefix.'001',
                $prefix.'002',
            ],
            $numbers,
        );
        $this->assertSame(2, (int) DB::table('yearly_registration_sequences')->where('intake_year', $intakeYear)->value('last_sequence'));
    }

    public function test_failure_during_audit_rolls_back_submission_and_retry_uses_the_first_number(): void
    {
        $registration = $this->createReadyRegistration();
        $user = $registration->applicant->user;
        $intakeYear = app(RegistrationNumberGeneratorService::class)->extractIntakeYear($registration->admissionPeriod);

        RegistrationLog::creating(function (): void {
            throw new RuntimeException('Forced audit write failure.');
        });

        try {
            app(SubmitRegistrationAction::class)->execute(
                $user,
                'Orang Tua Penguji',
                '127.0.0.1',
                'mysql-concurrency-test',
            );
            $this->fail('The forced audit write failure should abort the transaction.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Forced audit write failure.', $exception->getMessage());
        } finally {
            RegistrationLog::flushEventListeners();
        }

        $this->assertSame(RegistrationStatus::DRAFT, $registration->fresh()->status);
        $this->assertDatabaseCount('registration_consents', 0);
        $this->assertDatabaseCount('registration_logs', 0);
        $this->assertNull(DB::table('yearly_registration_sequences')->where('intake_year', $intakeYear)->first());

        $submitted = app(SubmitRegistrationAction::class)->execute(
            $user,
            'Orang Tua Penguji',
            '127.0.0.1',
            'mysql-concurrency-test',
        );

        $prefix = substr($intakeYear, -2);

        $this->assertSame(
            $prefix.'001',
            $submitted->registration_number,
        );
        $this->assertSame(RegistrationStatus::SUBMITTED, $submitted->status);
        $this->assertDatabaseCount('registration_consents', 1);
        $this->assertDatabaseCount('registration_logs', 1);
        $this->assertSame(1, (int) DB::table('yearly_registration_sequences')->where('intake_year', $intakeYear)->value('last_sequence'));
    }

    private function createReadyRegistration(
        ?AdmissionPeriod $period = null,
        ?AdmissionPeriodProgram $periodProgram = null,
        ?DocumentRequirement $requirement = null
    ): Registration {
        $user = User::factory()->applicant()->create();
        $this->userIds[] = $user->id;

        $applicant = Applicant::factory()->for($user)->create([
            'full_name' => 'Calon Siswa Pengujian MySQL',
            'birth_date' => '2020-01-15',
            'child_order' => 1,
            'siblings_count' => 0,
        ]);
        $this->applicantIds[] = $applicant->id;

        $period ??= AdmissionPeriod::factory()->create([
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);

        if (! in_array($period->id, $this->periodIds, true)) {
            $this->periodIds[] = $period->id;
        }

        if ($periodProgram === null) {
            $academicYearStart = 2000 + $period->id;
            $period->update([
                'academic_year' => sprintf('%04d/%04d', $academicYearStart, $academicYearStart + 1),
            ]);

            $program = Program::query()->firstOrCreate(
                ['code' => 'REGULER'],
                [
                    'name' => 'Program Reguler',
                    'description' => 'Program Reguler',
                    'is_active' => true,
                ],
            );
            $periodProgram = AdmissionPeriodProgram::factory()->create([
                'admission_period_id' => $period->id,
                'program_id' => $program->id,
                'is_active' => true,
            ]);
        }

        $requirement ??= DocumentRequirement::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $periodProgram->program_id,
            'is_required' => false,
            'code' => 'OPTIONAL_'.$period->id,
        ]);

        $sequenceScope = [
            'academic_year' => str_replace('/', '-', (string) $period->academic_year),
            'program_id' => (int) $periodProgram->program_id,
        ];
        if (! in_array($sequenceScope, $this->registrationSequenceScopes, true)) {
            $this->registrationSequenceScopes[] = $sequenceScope;
        }

        ApplicantAddress::factory()->create([
            'applicant_id' => $applicant->id,
        ]);
        ApplicantGuardian::factory()->father()->create([
            'applicant_id' => $applicant->id,
            'nik' => '3573010101750001',
        ]);
        ApplicantGuardian::factory()->mother()->create([
            'applicant_id' => $applicant->id,
            'nik' => '3573010101850002',
        ]);
        ApplicantEducation::factory()->create([
            'applicant_id' => $applicant->id,
            'education_level' => 'none',
            'school_name' => null,
        ]);

        return Registration::query()->create([
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period->id,
            'admission_period_program_id' => $periodProgram->id,
            'status' => RegistrationStatus::DRAFT,
            'data_revision' => 1,
        ]);
    }

    /**
     * @param  list<int>  $registrationIds
     * @return list<array{registration_id: int, registration_number: string, status: string}>
     */
    private function runWorkers(array $registrationIds): array
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ppdb-phase6-'.bin2hex(random_bytes(8));
        if (! mkdir($directory)) {
            throw new RuntimeException('Could not create the temporary concurrency barrier.');
        }

        $releasePath = $directory.DIRECTORY_SEPARATOR.'release';
        $processes = [];

        try {
            foreach ($registrationIds as $index => $registrationId) {
                $readyPath = $directory.DIRECTORY_SEPARATOR.'ready-'.$index;
                $process = new Process([
                    PHP_BINARY,
                    '-r',
                    $this->workerScript($registrationId, $readyPath, $releasePath),
                ]);
                $process->setWorkingDirectory(base_path());
                $process->setEnv([
                    'APP_ENV' => 'testing',
                    'DB_CONNECTION' => 'mysql_test',
                    'DB_TEST_URL' => '',
                    'DB_TEST_HOST' => (string) config('database.connections.mysql_test.host'),
                    'DB_TEST_PORT' => (string) config('database.connections.mysql_test.port'),
                    'DB_TEST_DATABASE' => self::TEST_DATABASE,
                    'DB_TEST_USERNAME' => (string) config('database.connections.mysql_test.username'),
                    'DB_TEST_PASSWORD' => (string) config('database.connections.mysql_test.password'),
                    'DB_TEST_SOCKET' => (string) config('database.connections.mysql_test.unix_socket'),
                ]);
                $process->setTimeout(45);
                $process->start();
                $processes[] = $process;
            }

            $deadline = microtime(true) + 30;
            foreach (array_keys($processes) as $index) {
                $readyPath = $directory.DIRECTORY_SEPARATOR.'ready-'.$index;

                while (! file_exists($readyPath)) {
                    if (! $processes[$index]->isRunning() || microtime(true) >= $deadline) {
                        throw new RuntimeException('A MySQL submission worker did not reach the concurrency barrier.');
                    }

                    usleep(20_000);
                }
            }

            if (! touch($releasePath)) {
                throw new RuntimeException('Could not release the MySQL concurrency barrier.');
            }

            $results = [];
            foreach ($processes as $process) {
                $process->wait();
                if (! $process->isSuccessful()) {
                    throw new RuntimeException('A MySQL submission worker exited unsuccessfully.');
                }

                $results[] = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            }

            return $results;
        } finally {
            if (! file_exists($releasePath)) {
                touch($releasePath);
            }

            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop(1);
                }
            }

            foreach (glob($directory.DIRECTORY_SEPARATOR.'*') ?: [] as $path) {
                unlink($path);
            }

            rmdir($directory);
        }
    }

    private function workerScript(int $registrationId, string $readyPath, string $releasePath): string
    {
        $template = <<<'PHP'
require __AUTOLOAD__;
$app = require __BOOTSTRAP__;
$app->make(__KERNEL__::class)->bootstrap();
config(['database.default' => 'mysql_test']);
$registrationId = __REGISTRATION_ID__;
$readyPath = __READY_PATH__;
$releasePath = __RELEASE_PATH__;
file_put_contents($readyPath, 'ready');
$deadline = microtime(true) + 30;
while (! file_exists($releasePath)) {
    if (microtime(true) >= $deadline) {
        throw new RuntimeException('Concurrency barrier timed out.');
    }
    usleep(20_000);
}
try {
    $registration = \App\Models\Registration::query()
        ->with('applicant.user')
        ->findOrFail($registrationId);
    $result = app(\App\Actions\Registration\SubmitRegistrationAction::class)->execute(
        $registration->applicant->user,
        'Orang Tua Penguji',
        '127.0.0.1',
        'mysql-concurrency-worker',
    );
    echo json_encode([
        'registration_id' => $result->id,
        'registration_number' => $result->registration_number,
        'status' => $result->status->value,
    ], JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Submission failed: '.get_class($exception));
    exit(1);
}
PHP;

        return str_replace(
            ['__AUTOLOAD__', '__BOOTSTRAP__', '__KERNEL__', '__REGISTRATION_ID__', '__READY_PATH__', '__RELEASE_PATH__'],
            [
                var_export(base_path('vendor/autoload.php'), true),
                var_export(base_path('bootstrap/app.php'), true),
                var_export(Kernel::class, true),
                (string) $registrationId,
                var_export($readyPath, true),
                var_export($releasePath, true),
            ],
            $template,
        );
    }
}
