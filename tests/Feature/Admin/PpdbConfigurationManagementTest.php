<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\DocumentRequirement;
use App\Models\Program;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use App\Models\User;
use App\Services\AdminConfigurationAuditService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpdbConfigurationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_only_authorized_admin_roles_access_configuration_modules(): void
    {
        $superadmin = $this->admin('superadmin');
        $admin = $this->admin('admin_ppdb');

        foreach (['admin.admission-periods.index', 'admin.programs.index', 'admin.document-requirements.index'] as $route) {
            $this->actingAs($superadmin)->get(route($route))->assertOk();
            $this->actingAs($admin)->get(route($route))->assertForbidden();
        }
    }

    public function test_period_crud_activation_date_validation_and_audit(): void
    {
        $actor = $this->admin('superadmin');
        $this->actingAs($actor)->post(route('admin.admission-periods.store'), [
            'name' => 'Periode QA', 'academic_year' => '2026/2027', 'start_date' => '2026-12-01 08:00', 'end_date' => '2026-12-31 16:00', 'age_reference_date' => '2026-07-01', 'status' => 'draft',
        ])->assertRedirect();
        $period = AdmissionPeriod::query()->where('name', 'Periode QA')->firstOrFail();

        $this->actingAs($actor)->post(route('admin.admission-periods.toggle', $period))->assertRedirect();
        $this->assertTrue($period->fresh()->is_active);
        $this->assertDatabaseHas('admin_configuration_audit_logs', ['entity_type' => 'admission_period', 'entity_id' => $period->id, 'action' => 'status_changed']);

        $this->actingAs($actor)->put(route('admin.admission-periods.update', $period), [
            'name' => 'Invalid', 'academic_year' => '2026/2027', 'start_date' => '2026-12-31 16:00', 'end_date' => '2026-12-01 08:00', 'age_reference_date' => '2026-07-01', 'status' => 'active',
        ])->assertSessionHasErrors('end_date');
    }

    public function test_used_period_cannot_change_historical_dates(): void
    {
        $actor = $this->admin('superadmin');
        $period = AdmissionPeriod::factory()->create();
        Registration::factory()->create(['admission_period_id' => $period->id]);

        $this->actingAs($actor)->put(route('admin.admission-periods.update', $period), [
            'name' => $period->name, 'academic_year' => $period->academic_year, 'start_date' => $period->start_date->copy()->addDay()->format('Y-m-d H:i:s'), 'end_date' => $period->end_date->format('Y-m-d H:i:s'), 'age_reference_date' => $period->age_reference_date->format('Y-m-d'), 'status' => $period->status->value,
        ])->assertSessionHas('error');
    }

    public function test_metadata_update_with_same_calendar_dates_is_allowed_for_used_period(): void
    {
        $actor = $this->admin('superadmin');
        $period = AdmissionPeriod::factory()->create();
        Registration::factory()->create(['admission_period_id' => $period->id]);

        $this->actingAs($actor)->put(route('admin.admission-periods.update', $period), [
            'name' => 'Nama Baru', 'academic_year' => $period->academic_year,
            'start_date' => $period->start_date->format('Y-m-d\TH:i'), 'end_date' => $period->end_date->format('Y-m-d\TH:i'),
            'age_reference_date' => $period->age_reference_date->format('Y-m-d'), 'status' => $period->status->value,
        ])->assertRedirect();

        $this->assertSame('Nama Baru', $period->fresh()->name);
    }

    public function test_database_rejects_a_second_active_period(): void
    {
        AdmissionPeriod::factory()->active()->create();

        $this->expectException(QueryException::class);
        AdmissionPeriod::factory()->active()->create();
    }

    public function test_audit_failure_rolls_back_period_creation(): void
    {
        $actor = $this->admin('superadmin');
        $this->mock(AdminConfigurationAuditService::class, function ($mock): void {
            $mock->shouldReceive('record')->once()->andThrow(new \RuntimeException('audit unavailable'));
        });

        $this->actingAs($actor)->post(route('admin.admission-periods.store'), [
            'name' => 'Rollback QA', 'academic_year' => '2026/2027', 'start_date' => '2026-12-01 08:00', 'end_date' => '2026-12-31 16:00', 'age_reference_date' => '2026-07-01', 'status' => 'draft',
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('admission_periods', ['name' => 'Rollback QA']);
    }

    public function test_program_crud_toggle_and_historical_relation(): void
    {
        $actor = $this->admin('superadmin');
        $period = AdmissionPeriod::factory()->create();
        $this->actingAs($actor)->post(route('admin.programs.store'), ['code' => 'QA', 'name' => 'Program QA', 'description' => 'QA', 'period_ids' => [$period->id]])->assertRedirect();
        $program = Program::query()->where('code', 'QA')->firstOrFail();
        $this->assertDatabaseHas('admission_period_programs', ['admission_period_id' => $period->id, 'program_id' => $program->id, 'is_active' => 1]);
        $this->actingAs($actor)->post(route('admin.programs.toggle', $program))->assertRedirect();
        $this->assertFalse($program->fresh()->is_active);
        $this->assertSame($program->id, AdmissionPeriodProgram::query()->where('program_id', $program->id)->value('program_id'));
    }

    public function test_document_requirement_crud_toggle_and_used_identity_protection(): void
    {
        $actor = $this->admin('superadmin');
        $period = AdmissionPeriod::factory()->create();
        $requirement = DocumentRequirement::factory()->create(['admission_period_id' => $period->id, 'code' => 'QA_DOC']);
        $this->actingAs($actor)->post(route('admin.document-requirements.toggle', $requirement))->assertRedirect();
        $this->assertFalse($requirement->fresh()->is_required);
        $registration = Registration::factory()->create(['admission_period_id' => $period->id]);
        RegistrationDocument::factory()->create(['registration_id' => $registration->id, 'document_requirement_id' => $requirement->id]);

        $this->actingAs($actor)->put(route('admin.document-requirements.update', $requirement), [
            'admission_period_id' => $period->id, 'program_id' => '', 'code' => 'RENAMED', 'name' => $requirement->name, 'description' => '', 'is_required' => '1', 'allowed_mimes' => 'application/pdf,image/jpeg,image/png', 'max_size_kb' => 5120, 'sort_order' => 1,
        ])->assertSessionHas('error');
    }

    public function test_document_requirement_rejects_program_not_linked_to_period(): void
    {
        $actor = $this->admin('superadmin');
        $period = AdmissionPeriod::factory()->create();
        $otherPeriod = AdmissionPeriod::factory()->create(['status' => 'draft', 'is_active' => false]);
        $program = Program::factory()->create();
        AdmissionPeriodProgram::factory()->create(['admission_period_id' => $otherPeriod->id, 'program_id' => $program->id]);

        $this->actingAs($actor)->post(route('admin.document-requirements.store'), [
            'admission_period_id' => $period->id, 'program_id' => $program->id, 'code' => 'BAD_SCOPE', 'name' => 'Bad scope',
            'description' => '', 'is_required' => '1', 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 5120, 'sort_order' => 1,
        ])->assertSessionHasErrors('program_id');
    }

    private function admin(string $role): User
    {
        $user = User::factory()->admin()->create(['email' => $role.'-config@example.test']);
        $user->assignRole($role);

        return $user;
    }
}
