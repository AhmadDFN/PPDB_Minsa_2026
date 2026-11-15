<?php

declare(strict_types=1);

namespace Tests\Feature\Applicant;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\AdmissionPeriodStatus;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Applicant;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Support\NikManager;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantProgramSelectionTest extends TestCase
{
    use RefreshDatabase;

    private function createApplicantUser(array $userAttributes = [], array $applicantAttributes = []): array
    {
        $nik = $userAttributes['nik'] ?? '3573010101190001';
        $user = User::factory()->create(array_merge([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'nik_lookup_hash' => NikManager::hash($nik),
            'nik_encrypted' => NikManager::encrypt($nik),
        ], $userAttributes));

        $applicant = Applicant::factory()->create(array_merge([
            'user_id' => $user->id,
            'full_name' => 'Calon Siswa Tes',
        ], $applicantAttributes));

        return [$user, $applicant];
    }

    private function createActivePeriodWithPrograms(): array
    {
        $period = AdmissionPeriod::factory()->create([
            'name' => 'PPDB Minsatama 2026/2027',
            'academic_year' => '2026/2027',
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
            'age_reference_date' => Carbon::parse('2026-07-01'),
        ]);

        $programUnggulan = Program::factory()->create([
            'name' => 'Kelas Unggulan Tahfidz & Sains',
            'code' => 'UNGGULAN',
            'is_active' => true,
        ]);

        $programReguler = Program::factory()->create([
            'name' => 'Kelas Reguler',
            'code' => 'REGULER',
            'is_active' => true,
        ]);

        $periodProgramUnggulan = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $programUnggulan->id,
            'is_active' => true,
        ]);

        $periodProgramReguler = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $programReguler->id,
            'is_active' => true,
        ]);

        return [$period, $periodProgramUnggulan, $periodProgramReguler];
    }

    public function test_guest_cannot_view_program_selection(): void
    {
        $response = $this->get(route('applicant.program.edit'));
        $response->assertRedirect(route('login'));
    }

    public function test_applicant_can_view_program_selection_page(): void
    {
        [$user] = $this->createApplicantUser();
        [$period, $ppUnggulan] = $this->createActivePeriodWithPrograms();

        $response = $this->actingAs($user, 'web')->get(route('applicant.program.edit'));

        $response->assertStatus(200);
        $response->assertSee('Pilihan Program Pendidikan');
        $response->assertSee('PPDB Minsatama 2026/2027');
        $response->assertSee('Kelas Unggulan Tahfidz & Sains');
        $response->assertSee('Kelas Reguler');
    }

    public function test_applicant_can_select_program_and_create_draft_registration(): void
    {
        [$user, $applicant] = $this->createApplicantUser();
        [$period, $ppUnggulan] = $this->createActivePeriodWithPrograms();

        $response = $this->actingAs($user, 'web')->post(route('applicant.program.update'), [
            'admission_period_program_id' => $ppUnggulan->id,
        ]);

        $response->assertRedirect(route('applicant.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('registrations', [
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period->id,
            'admission_period_program_id' => $ppUnggulan->id,
            'status' => RegistrationStatus::DRAFT->value,
            'registration_number' => null, // null until submitted in Phase 6
        ]);
    }

    public function test_applicant_can_change_selected_program_without_duplicate_registrations(): void
    {
        [$user, $applicant] = $this->createApplicantUser();
        [$period, $ppUnggulan, $ppReguler] = $this->createActivePeriodWithPrograms();

        // 1. Select Unggulan
        $this->actingAs($user, 'web')->post(route('applicant.program.update'), [
            'admission_period_program_id' => $ppUnggulan->id,
        ]);

        $this->assertSame(1, Registration::where('applicant_id', $applicant->id)->count());

        // 2. Change to Reguler
        $this->actingAs($user, 'web')->post(route('applicant.program.update'), [
            'admission_period_program_id' => $ppReguler->id,
        ]);

        $this->assertSame(1, Registration::where('applicant_id', $applicant->id)->count());
        $registration = Registration::where('applicant_id', $applicant->id)->first();
        $this->assertSame($ppReguler->id, $registration->admission_period_program_id);
    }

    public function test_program_selection_fails_if_no_active_period(): void
    {
        [$user] = $this->createApplicantUser();
        // No active period created

        $response = $this->actingAs($user, 'web')->post(route('applicant.program.update'), [
            'admission_period_program_id' => 999,
        ]);

        $response->assertSessionHasErrors(['admission_period_program_id']);
    }

    public function test_program_selection_fails_if_program_belongs_to_different_period(): void
    {
        [$user] = $this->createApplicantUser();
        [$activePeriod] = $this->createActivePeriodWithPrograms();

        // Create inactive other period
        $otherPeriod = AdmissionPeriod::factory()->create([
            'status' => AdmissionPeriodStatus::CLOSED,
            'is_active' => false,
        ]);
        $otherProgram = Program::factory()->create();
        $otherPeriodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $otherPeriod->id,
            'program_id' => $otherProgram->id,
        ]);

        $response = $this->actingAs($user, 'web')->post(route('applicant.program.update'), [
            'admission_period_program_id' => $otherPeriodProgram->id,
        ]);

        $response->assertSessionHasErrors(['admission_period_program_id']);
    }
}
