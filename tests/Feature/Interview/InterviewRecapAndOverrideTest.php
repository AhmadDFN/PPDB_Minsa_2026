<?php

declare(strict_types=1);

namespace Tests\Feature\Interview;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\AdmissionPeriodStatus;
use App\Enums\RegistrationStatus;
use App\Models\AdminConfigurationAuditLog;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\InterviewBatch;
use App\Models\InterviewScore;
use App\Models\InterviewSession;
use App\Models\InterviewStage;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use Database\Seeders\InterviewMasterSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterviewRecapAndOverrideTest extends TestCase
{
    use RefreshDatabase;

    private AdmissionPeriod $period;

    private Program $programReguler;

    private Program $programUnggulan;

    private AdmissionPeriodProgram $periodProgramReguler;

    private AdmissionPeriodProgram $periodProgramUnggulan;

    private InterviewBatch $batch1;

    private User $superadmin;

    private User $adminPpdb;

    private User $interviewer1;

    private User $interviewer2;

    private User $interviewer3;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->period = AdmissionPeriod::factory()->create([
            'is_active' => true,
            'status' => AdmissionPeriodStatus::ACTIVE,
            'academic_year' => '2026/2027',
        ]);

        $this->programReguler = Program::firstOrCreate(
            ['code' => 'REGULER'],
            ['name' => 'Program Reguler', 'description' => 'Reguler', 'is_active' => true]
        );

        $this->programUnggulan = Program::firstOrCreate(
            ['code' => 'UNGGULAN'],
            ['name' => 'Program Unggulan', 'description' => 'Unggulan', 'is_active' => true]
        );

        $this->periodProgramReguler = AdmissionPeriodProgram::firstOrCreate(
            ['admission_period_id' => $this->period->id, 'program_id' => $this->programReguler->id],
            ['is_active' => true]
        );

        $this->periodProgramUnggulan = AdmissionPeriodProgram::firstOrCreate(
            ['admission_period_id' => $this->period->id, 'program_id' => $this->programUnggulan->id],
            ['is_active' => true]
        );

        $this->batch1 = InterviewBatch::create([
            'admission_period_id' => $this->period->id,
            'batch_number' => 1,
            'title' => 'Hari 1 - Gelombang Pagi',
            'interview_date' => now()->addDays(5)->toDateString(),
            'start_time' => '07:30',
            'end_time' => '14:00',
            'quota' => 70,
            'sequence_start' => 1,
            'sequence_end' => 70,
            'is_active' => true,
        ]);

        $this->seed(InterviewMasterSeeder::class);

        $this->superadmin = $this->createStaff('superadmin', 'Superadmin', 'superadmin@test.sch.id');
        $this->adminPpdb = $this->createStaff('admin_ppdb', 'Admin PPDB', 'adminppdb@test.sch.id');
        $this->interviewer1 = $this->createStaff('interviewer_stage_1', 'Penguji 1', 'p1@test.sch.id');
        $this->interviewer2 = $this->createStaff('interviewer_stage_2', 'Penguji 2', 'p2@test.sch.id');
        $this->interviewer3 = $this->createStaff('interviewer_stage_3', 'Penguji 3', 'p3@test.sch.id');
    }

    private function createStaff(string $role, string $name, string $email): User
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password123'),
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function createCompletedStage1Session(Registration $registration, int $scorePerCriterion = 80): InterviewSession
    {
        $stage1 = InterviewStage::where('admission_period_id', $this->period->id)
            ->where('stage_order', 1)
            ->with('criteria')
            ->firstOrFail();

        $total = 0;
        $criteria = $stage1->criteria()->where('is_active', true)->get();

        $session = InterviewSession::create([
            'registration_id' => $registration->id,
            'interview_stage_id' => $stage1->id,
            'interviewer_id' => $this->interviewer1->id,
            'status' => 'COMPLETED',
            'started_at' => now()->subMinutes(15),
            'completed_at' => now(),
            'total_score' => $scorePerCriterion * $criteria->count(),
            'average_score' => (float) $scorePerCriterion,
        ]);

        foreach ($criteria as $criterion) {
            InterviewScore::create([
                'interview_session_id' => $session->id,
                'interview_criterion_id' => $criterion->id,
                'score' => $scorePerCriterion,
            ]);
            $total += $scorePerCriterion;
        }

        return $session;
    }

    public function test_all_interviewer_roles_and_applicants_get_403_forbidden_on_recap_override_and_export(): void
    {
        $registration = Registration::factory()->create([
            'admission_period_id' => $this->period->id,
            'admission_period_program_id' => $this->periodProgramReguler->id,
            'status' => RegistrationStatus::SUBMITTED,
            'registration_number' => '270001',
            'verified_at' => now(),
            'verified_by' => $this->adminPpdb->id,
        ]);
        $session = $this->createCompletedStage1Session($registration);
        $applicantUser = User::factory()->applicant()->create();

        foreach ([$this->interviewer1, $this->interviewer2, $this->interviewer3, $applicantUser] as $unauthorizedUser) {
            $this->actingAs($unauthorizedUser)
                ->get(route('admin.interview-recaps.index'))
                ->assertForbidden();

            $this->actingAs($unauthorizedUser)
                ->get(route('admin.interview-recaps.export'))
                ->assertForbidden();

            $this->actingAs($unauthorizedUser)
                ->put(route('admin.interview-recaps.override', $session), [
                    'scores' => [1 => 90],
                    'reason' => 'Percobaan ilegal mengubah nilai',
                ])
                ->assertForbidden();
        }
    }

    public function test_admin_ppdb_and_superadmin_can_view_recap_and_filter_results(): void
    {
        $reg1 = Registration::factory()->create([
            'admission_period_id' => $this->period->id,
            'admission_period_program_id' => $this->periodProgramReguler->id,
            'interview_batch_id' => $this->batch1->id,
            'status' => RegistrationStatus::SUBMITTED,
            'registration_number' => '270001',
            'queue_sequence' => 1,
            'verified_at' => now(),
            'verified_by' => $this->adminPpdb->id,
        ]);
        $reg1->applicant->update(['full_name' => 'Ahmad Fauzi']);

        $reg2 = Registration::factory()->create([
            'admission_period_id' => $this->period->id,
            'admission_period_program_id' => $this->periodProgramUnggulan->id,
            'interview_batch_id' => $this->batch1->id,
            'status' => RegistrationStatus::SUBMITTED,
            'registration_number' => '270002',
            'queue_sequence' => 2,
            'verified_at' => now(),
            'verified_by' => $this->adminPpdb->id,
        ]);
        $reg2->applicant->update(['full_name' => 'Zahra Aulia']);

        $this->createCompletedStage1Session($reg1, 85);

        // Admin PPDB views unfiltered recap
        $this->actingAs($this->adminPpdb)
            ->get(route('admin.interview-recaps.index'))
            ->assertOk()
            ->assertSee('Rekap Nilai Wawancara')
            ->assertSee('Ahmad Fauzi')
            ->assertSee('Zahra Aulia')
            ->assertSee('85.00');

        // Filter by Program Unggulan
        $this->actingAs($this->superadmin)
            ->get(route('admin.interview-recaps.index', ['program_id' => $this->programUnggulan->id]))
            ->assertOk()
            ->assertSee('Zahra Aulia')
            ->assertDontSee('Ahmad Fauzi');

        // Filter by Search
        $this->actingAs($this->adminPpdb)
            ->get(route('admin.interview-recaps.index', ['search' => '270001']))
            ->assertOk()
            ->assertSee('Ahmad Fauzi')
            ->assertDontSee('Zahra Aulia');
    }

    public function test_admin_override_requires_minimum_10_char_reason_recalculates_scores_and_records_audit_log(): void
    {
        $registration = Registration::factory()->create([
            'admission_period_id' => $this->period->id,
            'admission_period_program_id' => $this->periodProgramReguler->id,
            'status' => RegistrationStatus::SUBMITTED,
            'registration_number' => '270010',
            'verified_at' => now(),
            'verified_by' => $this->adminPpdb->id,
        ]);

        $session = $this->createCompletedStage1Session($registration, 70);
        $criteria = $session->stage->criteria()->where('is_active', true)->get();
        $newScores = [];
        foreach ($criteria as $index => $criterion) {
            $newScores[$criterion->id] = $index === 0 ? 90 : 80;
        }
        $expectedTotal = array_sum($newScores);
        $expectedAvg = round($expectedTotal / $criteria->count(), 2);

        // 1. Short reason (< 10 chars) must fail validation
        $this->actingAs($this->adminPpdb)
            ->put(route('admin.interview-recaps.override', $session), [
                'scores' => $newScores,
                'reason' => 'Salah',
            ])
            ->assertSessionHasErrors('reason');

        // 2. Valid override must succeed, recalculate totals, and record audit log with before/after diff
        $reason = 'Koreksi salah ketik penguji sesuai berita acara nomor 012.';
        $this->actingAs($this->adminPpdb)
            ->put(route('admin.interview-recaps.override', $session), [
                'scores' => $newScores,
                'reason' => $reason,
                'notes' => 'Diperbarui oleh panitia',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $session->refresh();
        $this->assertSame($expectedTotal, $session->total_score);
        $this->assertEquals($expectedAvg, (float) $session->average_score);
        $this->assertSame($this->adminPpdb->id, $session->last_edited_by);
        $this->assertSame($reason, $session->last_edit_reason);

        $auditLog = AdminConfigurationAuditLog::query()
            ->where('entity_type', 'interview_session')
            ->where('entity_id', $session->id)
            ->where('action', 'score_overridden')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertSame($this->adminPpdb->id, $auditLog->user_id);
        $this->assertSame(70 * $criteria->count(), $auditLog->before['total_score']);
        $this->assertSame($expectedTotal, $auditLog->after['total_score']);
        $this->assertSame($reason, $auditLog->after['last_edit_reason']);
    }

    public function test_csv_export_includes_dynamic_columns_and_sanitizes_formula_injection(): void
    {
        $registration = Registration::factory()->create([
            'admission_period_id' => $this->period->id,
            'admission_period_program_id' => $this->periodProgramReguler->id,
            'interview_batch_id' => $this->batch1->id,
            'status' => RegistrationStatus::SUBMITTED,
            'registration_number' => '270020',
            'verified_at' => now(),
            'verified_by' => $this->adminPpdb->id,
        ]);
        // Formula injection payload in student name
        $registration->applicant->update(['full_name' => '=CMD("calc")']);

        $this->createCompletedStage1Session($registration, 88);

        $response = $this->actingAs($this->adminPpdb)
            ->get(route('admin.interview-recaps.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Nomor Urut', $content);
        $this->assertStringContainsString('T1 - Jumlah', $content);
        $this->assertStringContainsString('T1 - Rata-rata', $content);
        // Formula injection sanitized with leading single quote (fputcsv escapes quotes as "")
        $this->assertStringContainsString("'=CMD(\"\"calc\"\")", $content);
        $this->assertStringContainsString('88.00', $content);
    }
}
