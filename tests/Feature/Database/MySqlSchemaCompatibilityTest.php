<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use Tests\TestCase;
use Throwable;

class MySqlSchemaCompatibilityTest extends TestCase
{
    private const TEST_DATABASE = 'ppdb_phase6_test';

    protected static bool $mysqlAvailable = false;

    protected static ?string $mysqlVersion = null;

    protected function setUp(): void
    {
        parent::setUp();

        $database = (string) config('database.connections.mysql_test.database');
        $applicationDatabase = (string) config('database.connections.mysql.database');
        $testUrl = (string) config('database.connections.mysql_test.url');

        $this->assertSame(self::TEST_DATABASE, $database, 'MySQL integration tests must use the dedicated ppdb_phase6_test database.');
        $this->assertNotSame($applicationDatabase, $database, 'MySQL integration tests must not use the application database.');
        $this->assertSame('', $testUrl, 'MySQL integration tests must not use a URL that can override the isolated database.');

        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('Ekstensi pdo_mysql belum aktif di lingkungan PHP.');
        }

        $host = (string) config('database.connections.mysql_test.host', '127.0.0.1');
        $port = (string) config('database.connections.mysql_test.port', '3306');
        $username = (string) config('database.connections.mysql_test.username', 'root');
        $password = (string) config('database.connections.mysql_test.password', '');

        try {
            $pdo = new PDO("mysql:host={$host};port={$port}", $username, $password, [
                PDO::ATTR_TIMEOUT => 2,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            self::$mysqlAvailable = true;
            self::$mysqlVersion = $pdo->query('SELECT VERSION()')->fetchColumn() ?: null;
        } catch (Throwable $e) {
            self::$mysqlAvailable = false;
            $this->markTestSkipped("MySQL server tidak aktif pada {$host}:{$port}. Pengujian MySQL ditandai PENDING.");
        }
    }

    /**
     * Run migrations only on the dedicated isolated Phase 6 test database.
     */
    public function test_mysql_all_migrations_run_successfully_on_disposable_database(): void
    {
        $this->runMigrations();

        $this->assertTrue(Schema::connection('mysql_test')->hasTable('users'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('applicants'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('applicant_addresses'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('applicant_guardians'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('applicant_educations'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('admission_periods'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('programs'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('admission_period_programs'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('registrations'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('document_requirements'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('registration_documents'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('registration_document_revisions'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('registration_number_sequences'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('registration_consents'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('registration_logs'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('registration_pdf_versions'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('registration_reopen_windows'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('user_otp_tokens'));
    }

    /**
     * Verify that all tables use the InnoDB storage engine in MySQL.
     */
    public function test_mysql_tables_use_innodb_storage_engine(): void
    {
        $this->runMigrations();

        $tables = DB::connection('mysql_test')->select(
            'SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?',
            [self::TEST_DATABASE]
        );

        $this->assertNotEmpty($tables);

        foreach ($tables as $table) {
            $this->assertSame(
                'InnoDB',
                $table->ENGINE,
                "Table {$table->TABLE_NAME} must use the InnoDB storage engine."
            );
        }
    }

    public function test_mysql_reopen_window_schema_has_required_foreign_keys_and_expiry_index(): void
    {
        $this->runMigrations();

        $columns = Schema::connection('mysql_test')->getColumnListing('registration_reopen_windows');
        $this->assertContains('registration_id', $columns);
        $this->assertContains('opened_by', $columns);
        $this->assertContains('closed_by', $columns);
        $this->assertContains('expires_at', $columns);
        $this->assertContains('closed_at', $columns);

        $foreignKeys = DB::connection('mysql_test')->select(
            'SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [self::TEST_DATABASE, 'registration_reopen_windows']
        );
        $foreignKeyTargets = [];
        foreach ($foreignKeys as $foreignKey) {
            $foreignKeyTargets[$foreignKey->COLUMN_NAME] = [
                $foreignKey->REFERENCED_TABLE_NAME,
                $foreignKey->REFERENCED_COLUMN_NAME,
            ];
        }
        ksort($foreignKeyTargets);

        $this->assertSame([
            'closed_by' => ['users', 'id'],
            'opened_by' => ['users', 'id'],
            'registration_id' => ['registrations', 'id'],
        ], $foreignKeyTargets);

        $indexes = DB::connection('mysql_test')->select(
            'SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [self::TEST_DATABASE, 'registration_reopen_windows']
        );

        $this->assertContains(
            'registration_reopen_active_index',
            array_column($indexes, 'INDEX_NAME')
        );
    }

    public function test_mysql_document_requirement_default_upload_limit_is_5120_kb(): void
    {
        $this->runMigrations();

        $column = DB::connection('mysql_test')->selectOne(
            'SELECT COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [self::TEST_DATABASE, 'document_requirements', 'max_size_kb']
        );

        $this->assertNotNull($column);
        $this->assertSame('5120', (string) $column->COLUMN_DEFAULT);
    }

    public function test_mysql_registration_number_sequence_is_unique_by_academic_year_and_program(): void
    {
        $this->runMigrations();

        $indexes = DB::connection('mysql_test')->select(
            'SELECT INDEX_NAME, COLUMN_NAME, NON_UNIQUE FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? ORDER BY SEQ_IN_INDEX',
            [self::TEST_DATABASE, 'registration_number_sequences', 'reg_number_seq_scope_unique']
        );

        $this->assertSame(
            ['academic_year', 'program_id'],
            array_column($indexes, 'COLUMN_NAME'),
        );
        $this->assertSame([0, 0], array_map(
            static fn (object $index): int => (int) $index->NON_UNIQUE,
            $indexes,
        ));
    }

    private function runMigrations(): void
    {
        if (! self::$mysqlAvailable) {
            $this->markTestSkipped('MySQL tidak tersedia.');
        }

        Artisan::call('migrate', [
            '--database' => 'mysql_test',
            '--force' => true,
        ]);
    }
}
