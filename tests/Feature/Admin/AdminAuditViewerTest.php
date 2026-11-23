<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AdminConfigurationAuditLog;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use App\Models\RegistrationDocumentVerification;
use App\Models\RegistrationLog;
use App\Models\SystemSettingLog;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminAuditViewerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_authorization_and_read_only_route_contract(): void
    {
        $this->get(route('admin.audit-logs.index'))->assertRedirect();
        $this->actingAs(User::factory()->applicant()->create())->get(route('admin.audit-logs.index'))->assertForbidden();
        $this->actingAs($this->admin('admin_ppdb'))->get(route('admin.audit-logs.index'))->assertForbidden();
        $this->actingAs($this->admin('superadmin'))->get(route('admin.audit-logs.index'))->assertOk()->assertSee('Audit Aktivitas');

        $this->assertRouteMissing('admin.audit-logs.destroy');
        $this->assertRouteMissing('admin.audit-logs.export');
    }

    public function test_events_are_integrated_sorted_and_document_verification_is_not_duplicated(): void
    {
        $admin = $this->admin('superadmin', 'Audit Admin');
        $admin->update(['email' => 'EMAIL_SECRET_MARKER@example.test', 'phone' => 'PHONE_SECRET_MARKER']);
        $registration = $this->registration('PPDB-AUDIT-001');
        $registration->applicant()->update(['full_name' => 'FULL_NAME_SECRET_MARKER']);
        $document = RegistrationDocument::factory()->create(['registration_id' => $registration->id]);

        AdminConfigurationAuditLog::create(['user_id' => $admin->id, 'entity_type' => 'admin_user', 'entity_id' => $admin->id, 'action' => 'created', 'created_at' => now()->subMinutes(5)]);
        AdminConfigurationAuditLog::create(['user_id' => $admin->id, 'entity_type' => 'registration_document', 'entity_id' => $document->id, 'action' => 'verification_APPROVED', 'before' => ['status' => 'PENDING'], 'after' => ['status' => 'APPROVED'], 'created_at' => now()->subMinutes(3)]);
        RegistrationDocumentVerification::create(['registration_document_id' => $document->id, 'actor_id' => $admin->id, 'from_status' => 'PENDING', 'to_status' => 'APPROVED', 'reason' => 'SECRET_REASON_MARKER', 'created_at' => now()->subMinutes(2)]);
        SystemSettingLog::create(['user_id' => $admin->id, 'setting_key' => 'institution_display_name', 'old_value' => 'OLD_SECRET_MARKER', 'new_value' => 'NEW_SECRET_MARKER', 'created_at' => now()->subMinute()]);
        RegistrationLog::create(['registration_id' => $registration->id, 'user_id' => $admin->id, 'action' => 'submitted', 'changes' => ['secret' => 'RAW_CHANGE_MARKER'], 'ip_address' => '198.51.100.9', 'user_agent' => 'SECRET_UA_MARKER', 'created_at' => now()->subMinutes(4)]);

        $response = $this->actingAs($admin)->get(route('admin.audit-logs.index'));
        $response->assertOk()->assertSee('Dokumen disetujui')->assertSee('Pendaftaran dikirim')->assertSee('Nama institusi diperbarui');
        $this->assertSame(1, substr_count($response->getContent(), 'Dokumen disetujui'));
        $response->assertSee('PPDB-AUDIT-001')->assertDontSee('EMAIL_SECRET_MARKER')->assertDontSee('PHONE_SECRET_MARKER')->assertDontSee('FULL_NAME_SECRET_MARKER')->assertDontSee('SECRET_REASON_MARKER')->assertDontSee('OLD_SECRET_MARKER')->assertDontSee('NEW_SECRET_MARKER')->assertDontSee('RAW_CHANGE_MARKER')->assertDontSee('SECRET_UA_MARKER')->assertDontSee('198.51.100.9');
    }

    public function test_filters_pagination_and_source_health_are_bounded(): void
    {
        $admin = $this->admin('superadmin');
        $registration = $this->registration('PPDB-FILTER-001');
        for ($index = 0; $index < 26; $index++) {
            RegistrationLog::create(['registration_id' => $registration->id, 'user_id' => $admin->id, 'action' => $index === 25 ? 'reopen_window_opened' : 'submitted', 'created_at' => now()->subDays($index)]);
        }

        DB::enableQueryLog();
        $response = $this->actingAs($admin)->get(route('admin.audit-logs.index', ['source' => 'registration', 'action' => 'reopen_window_opened', 'search' => 'PPDB-FILTER-001', 'date_from' => now()->subDays(30)->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]));
        $response->assertOk()->assertSee('Akses edit ulang dibuka')->assertSee('PPDB-FILTER-001');
        $this->assertLessThan(35, count(DB::getQueryLog()));
        DB::disableQueryLog();
        $this->actingAs($admin)->get(route('admin.audit-logs.index', ['source' => 'registration']))->assertOk()->assertSee('page=2');

        Schema::dropIfExists('system_setting_logs');
        $response = $this->actingAs($admin)->get(route('admin.audit-logs.index'));
        $response->assertOk()->assertSee('Sebagian sumber audit belum tersedia')->assertSee('2026_11_20_080004_create_system_settings_tables.php')->assertSee('PPDB-FILTER-001');
    }

    public function test_normalized_action_filters_match_settings_and_document_history(): void
    {
        $admin = $this->admin('superadmin', 'Filter Admin');
        $registration = $this->registration('PPDB-FILTER-ACTION');
        $document = RegistrationDocument::factory()->create(['registration_id' => $registration->id]);
        SystemSettingLog::create(['user_id' => $admin->id, 'setting_key' => 'institution_display_name', 'old_value' => 'old', 'new_value' => 'new']);
        RegistrationDocumentVerification::create(['registration_document_id' => $document->id, 'actor_id' => $admin->id, 'from_status' => 'PENDING', 'to_status' => 'APPROVED', 'reason' => 'approved']);
        RegistrationLog::create(['registration_id' => $registration->id, 'user_id' => $admin->id, 'action' => 'updated_after_submission']);
        RegistrationLog::create(['registration_id' => $registration->id, 'user_id' => $admin->id, 'action' => 'updated_draft']);

        $this->actingAs($admin)->get(route('admin.audit-logs.index', ['source' => 'system_settings', 'action' => 'setting_updated']))->assertOk()->assertSee('Pengaturan sistem diperbarui');
        $this->actingAs($admin)->get(route('admin.audit-logs.index', ['source' => 'document_verification', 'action' => 'verification_APPROVED']))->assertOk()->assertSee('Dokumen disetujui')->assertDontSee('Status dokumen: PENDING → REJECTED');
        $this->actingAs($admin)->get(route('admin.audit-logs.index', ['source' => 'registration', 'action' => 'updated_after_submission']))->assertOk()->assertSee('Aktivitas pendaftaran');
        $this->actingAs($admin)->get(route('admin.audit-logs.index', ['source' => 'registration', 'action' => 'updated_draft']))->assertOk()->assertSee('Aktivitas pendaftaran');
        $this->actingAs($admin)->get(route('admin.audit-logs.index'))->assertSeeHtml('value="setting_updated"')->assertSeeHtml('value="verification_APPROVED"');
        $this->actingAs($admin)->get(route('admin.audit-logs.index'))->assertSeeHtml('value="updated_after_submission"');
        $this->actingAs($admin)->get(route('admin.audit-logs.index'))->assertSeeHtml('value="updated_draft"');
    }

    public function test_actor_dropdown_contains_administrators_only(): void
    {
        $admin = $this->admin('superadmin', 'Dropdown Administrator');
        $applicant = User::factory()->applicant()->create(['name' => 'Dropdown Applicant Private Marker']);

        $response = $this->actingAs($admin)->get(route('admin.audit-logs.index'));
        $response->assertOk()->assertSee('Dropdown Administrator')->assertDontSee('Dropdown Applicant Private Marker');
        $this->assertStringContainsString('name="actor_id"', $response->getContent());
        $this->assertDatabaseHas('users', ['id' => $applicant->id, 'account_type' => 'applicant']);
    }

    public function test_registration_actor_identity_is_classified_and_anonymized(): void
    {
        $admin = $this->admin('superadmin', 'Named Administrator');
        $registration = $this->registration('PPDB-ACTOR-001');
        $applicant = $registration->applicant->user;
        $applicant->update(['name' => 'APPLICANT_PRIVATE_NAME_MARKER']);
        RegistrationLog::create(['registration_id' => $registration->id, 'user_id' => $applicant->id, 'action' => 'submitted', 'created_at' => now()->subMinute()]);
        RegistrationLog::create(['registration_id' => $registration->id, 'user_id' => $admin->id, 'action' => 'reopen_window_opened', 'created_at' => now()]);
        RegistrationLog::create(['registration_id' => $registration->id, 'user_id' => null, 'action' => 'pdf_generated', 'created_at' => now()->subMinutes(2)]);

        $response = $this->actingAs($admin)->get(route('admin.audit-logs.index', ['source' => 'registration']));
        $response->assertOk()->assertSee('Pendaftar')->assertSee('Named Administrator')->assertSee('Akses edit ulang dibuka')->assertSee('Aktor tidak tersedia')->assertDontSee('APPLICANT_PRIVATE_NAME_MARKER');
    }

    private function admin(string $role, string $name = 'Superadmin Audit'): User
    {
        $user = User::factory()->admin()->create(['name' => $name]);
        $user->assignRole($role);

        return $user;
    }

    private function registration(string $number): Registration
    {
        $period = AdmissionPeriod::factory()->create();
        $program = AdmissionPeriodProgram::factory()->create(['admission_period_id' => $period->id]);

        return Registration::factory()->create(['registration_number' => $number, 'admission_period_id' => $period->id, 'admission_period_program_id' => $program->id]);
    }

    private function assertRouteMissing(string $name): void
    {
        $this->assertFalse(collect(\Route::getRoutes())->contains(fn ($route): bool => $route->getName() === $name));
    }
}
