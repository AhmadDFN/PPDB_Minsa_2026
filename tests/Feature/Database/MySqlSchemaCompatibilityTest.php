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
    protected static bool $mysqlAvailable = false;

    protected static ?string $mysqlVersion = null;

    protected function setUp(): void
    {
        parent::setUp();

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
            $this->markTestSkipped("MySQL server tidak aktif pada {$host}:{$port} ({$e->getMessage()}). Pengujian MySQL ditandai PENDING.");
        }
    }

    /**
     * Run all migrations against the isolated disposable MySQL database ppdb_minsatama_test.
     */
    public function test_mysql_all_migrations_run_successfully_on_disposable_database(): void
    {
        if (! self::$mysqlAvailable) {
            $this->markTestSkipped('MySQL tidak tersedia.');
        }

        $dbName = config('database.connections.mysql_test.database', 'ppdb_minsatama_test');

        // Create database if not exists
        DB::connection('mysql')->statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Run migrations on mysql_test
        Artisan::call('migrate', [
            '--database' => 'mysql_test',
            '--force' => true,
        ]);

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
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('registration_consents'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('registration_logs'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('registration_pdf_versions'));
        $this->assertTrue(Schema::connection('mysql_test')->hasTable('user_otp_tokens'));
    }

    /**
     * Verify that all tables use the InnoDB storage engine in MySQL.
     */
    public function test_mysql_tables_use_innodb_storage_engine(): void
    {
        if (! self::$mysqlAvailable) {
            $this->markTestSkipped('MySQL tidak tersedia.');
        }

        $dbName = config('database.connections.mysql_test.database', 'ppdb_minsatama_test');

        $tables = DB::connection('mysql_test')->select(
            'SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?',
            [$dbName]
        );

        $this->assertNotEmpty($tables);

        foreach ($tables as $table) {
            $this->assertEquals(
                'InnoDB',
                $table->ENGINE,
                "Table {$table->TABLE_NAME} must use the InnoDB storage engine."
            );
        }
    }
}
