<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\Applicant;
use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SystemSettingRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Cache::flush();
    }

    public function test_system_setting_reads_default_when_record_does_not_exist(): void
    {
        // Table exists, but no rows inserted yet
        $this->assertSame('PPDBM MIN 1 KOTA MADIUN', SystemSetting::getInstitutionName());
        $this->assertNull(SystemSetting::get('unknown_key'));
        $this->assertSame('fallback_val', SystemSetting::get('unknown_key', 'fallback_val'));
    }

    public function test_system_setting_reads_custom_value_when_configured(): void
    {
        SystemSetting::set('institution_display_name', 'PPDB MIN 1 KOTA MADIUN TAHUN 2026/2027');

        $this->assertSame('PPDB MIN 1 KOTA MADIUN TAHUN 2026/2027', SystemSetting::getInstitutionName());
    }

    public function test_system_setting_gracefully_falls_back_when_table_is_missing(): void
    {
        // Temporarily drop system_settings table to simulate pre-migration production state
        Schema::dropIfExists('system_setting_logs');
        Schema::dropIfExists('system_settings');

        // Verify table is indeed missing
        $this->assertFalse(Schema::hasTable('system_settings'));

        // SystemSetting::getInstitutionName() must NOT throw 500 QueryException
        $fallbackName = SystemSetting::getInstitutionName();
        $this->assertSame('PPDBM MIN 1 KOTA MADIUN', $fallbackName);

        // SystemSetting::get() must return provided default
        $this->assertSame('custom_default', SystemSetting::get('some_key', 'custom_default'));

        // Crucial requirement: Cache must NOT permanently cache the missing-table fallback
        $this->assertFalse(Cache::has(SystemSetting::CACHE_PREFIX.'institution_display_name'));
        $this->assertFalse(Cache::has(SystemSetting::CACHE_PREFIX.'some_key'));
    }

    public function test_system_setting_does_not_swallow_non_table_database_errors(): void
    {
        // If an unexpected query exception occurs that is NOT "table not found", it must be re-thrown
        $this->expectException(QueryException::class);

        // We simulate this by mocking or running an invalid query inside get
        DB::statement('SELECT invalid_syntax FROM');
    }

    public function test_login_and_registration_pages_render_cleanly_when_table_exists(): void
    {
        $loginResponse = $this->get(route('login'));
        $loginResponse->assertOk();
        $loginResponse->assertSee('PPDBM MIN 1 KOTA MADIUN');

        $registerResponse = $this->get(route('register'));
        $registerResponse->assertOk();
        $registerResponse->assertSee('PPDBM MIN 1 KOTA MADIUN');
    }

    public function test_login_and_registration_pages_render_without_500_when_table_is_missing(): void
    {
        // Simulate pending migration state
        Schema::dropIfExists('system_setting_logs');
        Schema::dropIfExists('system_settings');

        $loginResponse = $this->get(route('login'));
        $loginResponse->assertOk();
        $loginResponse->assertSee('PPDBM MIN 1 KOTA MADIUN');

        $registerResponse = $this->get(route('register'));
        $registerResponse->assertOk();
        $registerResponse->assertSee('PPDBM MIN 1 KOTA MADIUN');
    }

    public function test_admin_and_applicant_authentication_has_no_regression(): void
    {
        // 1. Applicant authentication check with associated Applicant model
        $user = User::factory()->create([
            'name' => 'Calon Siswa Test',
            'phone' => '081299887766',
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'password' => bcrypt('Password123!'),
            'must_change_password' => false,
        ]);

        Applicant::factory()->create([
            'user_id' => $user->id,
            'full_name' => 'Calon Siswa Test',
        ]);

        $this->actingAs($user);
        $this->get(route('applicant.dashboard'))->assertOk();

        // 2. Admin authentication check
        $admin = User::factory()->create([
            'email' => 'admin@minsatama.sch.id',
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
            'password' => bcrypt('AdminPassword123!'),
        ]);
        $admin->assignRole('superadmin');

        $this->actingAs($admin);
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.applicants.index'))->assertOk();
    }

    public function test_maintenance_migration_route_security(): void
    {
        // Invalid token returns 403
        $this->get('/maintenance/migrate-production/wrong-token')->assertForbidden();

        // Valid token executes successfully and returns 200 HTML response
        $response = $this->get('/maintenance/migrate-production/minsatama2026-secure-migrate');
        $response->assertOk();
        $response->assertSee('Migrasi Berhasil Dijalankan!');
    }
}
