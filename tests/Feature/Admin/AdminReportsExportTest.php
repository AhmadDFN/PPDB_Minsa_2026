<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\DocumentVerificationStatus;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Applicant;
use App\Models\Program;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use App\Models\User;
use App\Services\AdminConfigurationAuditService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AdminReportsExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        RateLimiter::clear('admin-report-export:user:1');
    }

    public function test_guest_applicant_and_admin_without_permission_are_denied(): void
    {
        $this->get(route('admin.reports.index'))->assertRedirect();
        $this->actingAs(User::factory()->applicant()->create())->get(route('admin.reports.index'))->assertForbidden();
        $admin = $this->admin('admin_ppdb');
        $admin->syncRoles([]);
        $this->actingAs($admin)->get(route('admin.reports.index'))->assertForbidden();
    }

    public function test_admin_and_superadmin_can_view_report(): void
    {
        $this->actingAs($this->admin('admin_ppdb'))->get(route('admin.reports.index'))->assertOk()->assertSee('Laporan PPDB');
        $this->actingAs($this->admin('superadmin'))->get(route('admin.reports.index'))->assertOk();
    }

    public function test_csv_contains_only_submitted_rows_and_redacted_contract(): void
    {
        $period = AdmissionPeriod::factory()->active()->create();
        $program = Program::factory()->create(['name' => 'Reguler Test']);
        $pivot = AdmissionPeriodProgram::factory()->create(['admission_period_id' => $period->id, 'program_id' => $program->id]);
        $submitted = $this->registration($period, $pivot, RegistrationStatus::SUBMITTED, '=Formula, "Name"');
        $this->document($submitted, DocumentVerificationStatus::APPROVED);
        $this->registration($period, $pivot, RegistrationStatus::DRAFT, 'Draft Pendaftar');

        $response = $this->actingAs($this->admin('admin_ppdb'))->get(route('admin.reports.export', ['period_id' => $period->id]));
        $response->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('"Nomor Registrasi","Nama Calon Peserta Didik"', $csv);
        $this->assertStringContainsString("\"'=Formula,", $csv);
        $this->assertStringNotContainsString('nik', strtolower($csv));
        $this->assertStringNotContainsString('Draft Pendaftar', $csv);
        $this->assertDatabaseHas('admin_configuration_audit_logs', ['action' => 'admin_report_export_requested', 'entity_id' => auth()->id()]);
        $this->assertSame(1, substr_count($csv, $submitted->registration_number));
    }

    public function test_filters_and_summary_use_same_submitted_scope(): void
    {
        $period = AdmissionPeriod::factory()->create(['name' => 'Periode Satu']);
        $otherPeriod = AdmissionPeriod::factory()->create(['name' => 'Periode Dua']);
        $program = Program::factory()->create(['name' => 'Program A']);
        $otherProgram = Program::factory()->create(['name' => 'Program B']);
        $pivot = AdmissionPeriodProgram::factory()->create(['admission_period_id' => $period->id, 'program_id' => $program->id]);
        $otherPivot = AdmissionPeriodProgram::factory()->create(['admission_period_id' => $otherPeriod->id, 'program_id' => $otherProgram->id]);
        $wanted = $this->registration($period, $pivot, RegistrationStatus::SUBMITTED, 'Wanted', now()->subDay());
        $this->document($wanted, DocumentVerificationStatus::PENDING);
        $excluded = $this->registration($otherPeriod, $otherPivot, RegistrationStatus::SUBMITTED, 'Excluded', now());
        $this->document($excluded, DocumentVerificationStatus::APPROVED);

        $admin = $this->admin('admin_ppdb');
        $page = $this->actingAs($admin)->get(route('admin.reports.index', ['period_id' => $period->id, 'program_id' => $program->id, 'verification_status' => 'PENDING', 'date_from' => now()->subDays(2)->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]));
        $page->assertOk()->assertSee('1')->assertDontSee('Excluded');
        $csv = $this->actingAs($admin)->get(route('admin.reports.export', ['period_id' => $period->id, 'program_id' => $program->id, 'verification_status' => 'PENDING']))->streamedContent();
        $this->assertStringContainsString($wanted->registration_number, $csv);
        $this->assertStringNotContainsString($excluded->registration_number, $csv);
    }

    public function test_audit_failure_stops_export(): void
    {
        $this->mock(AdminConfigurationAuditService::class, function ($mock): void {
            $mock->shouldReceive('record')->once()->andThrow(new \RuntimeException('audit unavailable'));
        });
        $response = $this->actingAs($this->admin('admin_ppdb'))->get(route('admin.reports.export'));
        $response->assertStatus(500)->assertSee('pencatatan audit gagal');
    }

    public function test_rate_limit_is_per_user(): void
    {
        $admin = $this->admin('admin_ppdb');
        for ($i = 0; $i < 10; $i++) {
            RateLimiter::hit('admin-report-export:user:'.$admin->id, 600);
        }
        $this->actingAs($admin)->get(route('admin.reports.export'))->assertStatus(429)->assertHeader('Retry-After');
    }

    private function admin(string $role): User
    {
        $user = User::factory()->admin()->create();
        $user->assignRole($role);

        return $user;
    }

    private function registration(AdmissionPeriod $period, AdmissionPeriodProgram $pivot, RegistrationStatus $status, string $name, $submittedAt = null): Registration
    {
        $applicant = Applicant::factory()->create(['full_name' => $name]);

        return Registration::factory()->create([
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period->id,
            'admission_period_program_id' => $pivot->id,
            'status' => $status,
            'registration_number' => $status === RegistrationStatus::SUBMITTED ? 'PPDB-'.fake()->unique()->numerify('#####') : null,
            'submitted_at' => $status === RegistrationStatus::SUBMITTED ? ($submittedAt ?? now()) : null,
        ]);
    }

    private function document(Registration $registration, DocumentVerificationStatus $status): RegistrationDocument
    {
        $document = RegistrationDocument::factory()->create(['registration_id' => $registration->id]);
        $document->update(['verification_status' => $status]);

        return $document;
    }
}
