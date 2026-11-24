<?php

declare(strict_types=1);

namespace Tests\Feature\Interview;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\AdmissionPeriodStatus;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\InterviewSession;
use App\Models\InterviewStage;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use Database\Seeders\InterviewMasterSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterviewerPortalTest extends TestCase
{
    use RefreshDatabase;

    private AdmissionPeriod $period;

    private Program $programReguler;

    private Program $programUnggulan;

    private AdmissionPeriodProgram $periodProgramReguler;

    private AdmissionPeriodProgram $periodProgramUnggulan;

    private User $interviewer1;

    private User $interviewer1b;

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

        $this->seed(InterviewMasterSeeder::class);

        $this->interviewer1 = $this->createInterviewer('interviewer_stage_1', 'Penguji BTQ 1', 'penguji1@minsatama.sch.id');
        $this->interviewer1b = $this->createInterviewer('interviewer_stage_1', 'Penguji BTQ 2', 'penguji1b@minsatama.sch.id');
        $this->interviewer2 = $this->createInterviewer('interviewer_stage_2', 'Penguji Calistung', 'penguji2@minsatama.sch.id');
        $this->interviewer3 = $this->createInterviewer('interviewer_stage_3', 'Penguji Unggulan', 'penguji3@minsatama.sch.id');
    }

    private function createInterviewer(string $role, string $name, string $email): User
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

    private function createVerifiedRegistration(bool $isUnggulan = false, string $number = '270001'): Registration
    {
        $pivot = $isUnggulan ? $this->periodProgramUnggulan : $this->periodProgramReguler;

        return Registration::factory()->create([
            'admission_period_id' => $this->period->id,
            'admission_period_program_id' => $pivot->id,
            'status' => RegistrationStatus::SUBMITTED,
            'registration_number' => $number,
            'verified_at' => now(),
            'verified_by' => $this->interviewer1->id,
        ]);
    }

    public function test_interviewer_is_redirected_to_interview_portal_after_login_and_forbidden_from_admin_area(): void
    {
        // 1. Staff login redirect
        $response = $this->post('/admin/login', [
            'email' => $this->interviewer1->email,
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('interview.index'));

        // 2. Generic /dashboard redirect
        $this->actingAs($this->interviewer1)
            ->get('/dashboard')
            ->assertRedirect(route('interview.index'));

        // 3. Blocked with 403 Forbidden from /admin/*
        $this->actingAs($this->interviewer1)
            ->get('/admin/dashboard')
            ->assertForbidden();

        $this->actingAs($this->interviewer1)
            ->get('/admin/pendaftar')
            ->assertForbidden();
    }

    public function test_interviewer_lookup_rejects_unverified_candidate(): void
    {
        $unverifiedReg = Registration::factory()->create([
            'admission_period_id' => $this->period->id,
            'admission_period_program_id' => $this->periodProgramReguler->id,
            'status' => RegistrationStatus::SUBMITTED,
            'registration_number' => '270099',
            'verified_at' => null, // Unverified
        ]);

        $response = $this->actingAs($this->interviewer1)
            ->post(route('interview.lookup'), [
                'identifier' => '270099',
            ]);

        $response->assertSessionHasErrors([
            'identifier' => 'Berkas calon siswa belum disahkan oleh panitia loket verifikasi.',
        ]);
    }

    public function test_sequential_gate_stage_2_requires_stage_1_completed(): void
    {
        $registration = $this->createVerifiedRegistration(false, '270002');
        $stage1 = InterviewStage::where('admission_period_id', $this->period->id)->where('stage_order', 1)->first();

        // Attempt stage 2 before stage 1 is completed -> Rejected
        $response = $this->actingAs($this->interviewer2)
            ->post(route('interview.lookup'), [
                'identifier' => '270002',
            ]);

        $response->assertSessionHasErrors([
            'identifier' => "Calon siswa belum menyelesaikan Wawancara Tahap 1 (BTQ/Iqro'). Selesaikan Tahap 1 terlebih dahulu.",
        ]);

        // Complete Stage 1
        InterviewSession::create([
            'registration_id' => $registration->id,
            'interview_stage_id' => $stage1->id,
            'interviewer_id' => $this->interviewer1->id,
            'status' => 'COMPLETED',
            'started_at' => now(),
            'completed_at' => now(),
            'total_score' => 85,
            'average_score' => 85.00,
        ]);

        // Stage 2 attempt -> Allowed
        $response2 = $this->actingAs($this->interviewer2)
            ->post(route('interview.lookup'), [
                'identifier' => '270002',
            ]);

        $response2->assertSessionHasNoErrors();
        $response2->assertRedirect();
        $this->assertDatabaseHas('interview_sessions', [
            'registration_id' => $registration->id,
            'interview_stage_id' => InterviewStage::where('admission_period_id', $this->period->id)->where('stage_order', 2)->first()->id,
            'interviewer_id' => $this->interviewer2->id,
            'status' => 'IN_PROGRESS',
        ]);
    }

    public function test_sequential_gate_stage_3_requires_unggulan_program_and_stages_1_and_2_completed(): void
    {
        $reguler = $this->createVerifiedRegistration(false, '270003');
        $unggulan = $this->createVerifiedRegistration(true, '270004');

        $stage1 = InterviewStage::where('admission_period_id', $this->period->id)->where('stage_order', 1)->first();
        $stage2 = InterviewStage::where('admission_period_id', $this->period->id)->where('stage_order', 2)->first();
        $stage3 = InterviewStage::where('admission_period_id', $this->period->id)->where('stage_order', 3)->first();

        // 1. Stage 3 rejects Reguler candidate
        $response = $this->actingAs($this->interviewer3)
            ->post(route('interview.lookup'), [
                'identifier' => '270003',
            ]);
        $response->assertSessionHasErrors([
            'identifier' => 'Calon siswa terdaftar pada Program Reguler dan tidak dijadwalkan untuk Wawancara Tahap 3.',
        ]);

        // 2. Stage 3 rejects Unggulan candidate if stage 1 or 2 not completed
        $response2 = $this->actingAs($this->interviewer3)
            ->post(route('interview.lookup'), [
                'identifier' => '270004',
            ]);
        $response2->assertSessionHasErrors([
            'identifier' => 'Calon siswa belum menyelesaikan Wawancara Tahap 1 dan/atau Tahap 2. Wawancara Tahap 3 hanya dapat dilakukan setelah kedua tahap sebelumnya selesai.',
        ]);

        // Complete Stage 1 & Stage 2 for Unggulan candidate
        InterviewSession::create([
            'registration_id' => $unggulan->id,
            'interview_stage_id' => $stage1->id,
            'interviewer_id' => $this->interviewer1->id,
            'status' => 'COMPLETED',
            'started_at' => now(),
            'completed_at' => now(),
            'total_score' => 90,
            'average_score' => 90.00,
        ]);
        InterviewSession::create([
            'registration_id' => $unggulan->id,
            'interview_stage_id' => $stage2->id,
            'interviewer_id' => $this->interviewer2->id,
            'status' => 'COMPLETED',
            'started_at' => now(),
            'completed_at' => now(),
            'total_score' => 88,
            'average_score' => 88.00,
        ]);

        // 3. Stage 3 now allows Unggulan candidate
        $response3 = $this->actingAs($this->interviewer3)
            ->post(route('interview.lookup'), [
                'identifier' => '270004',
            ]);
        $response3->assertSessionHasNoErrors();
        $response3->assertRedirect();
        $this->assertDatabaseHas('interview_sessions', [
            'registration_id' => $unggulan->id,
            'interview_stage_id' => $stage3->id,
            'interviewer_id' => $this->interviewer3->id,
            'status' => 'IN_PROGRESS',
        ]);
    }

    public function test_soft_lock_prevents_concurrent_grading_under_30_minutes_and_allows_takeover_after_30_minutes(): void
    {
        $registration = $this->createVerifiedRegistration(false, '270005');
        $stage1 = InterviewStage::where('admission_period_id', $this->period->id)->where('stage_order', 1)->first();

        // Interviewer 1 starts session
        $session = InterviewSession::create([
            'registration_id' => $registration->id,
            'interview_stage_id' => $stage1->id,
            'interviewer_id' => $this->interviewer1->id,
            'status' => 'IN_PROGRESS',
            'started_at' => now()->subMinutes(10), // 10 minutes ago (< 30 min)
        ]);

        // Interviewer 1b attempts lookup on same candidate -> Soft lock rejection
        $response = $this->actingAs($this->interviewer1b)
            ->post(route('interview.lookup'), [
                'identifier' => '270005',
            ]);

        $response->assertSessionHasErrors([
            'identifier' => "Calon siswa sedang aktif diwawancarai oleh {$this->interviewer1->name}. Silakan tunggu atau hubungi panitia.",
        ]);

        // Simulate session abandoned for 35 minutes (> 30 min)
        $session->update(['started_at' => now()->subMinutes(35)]);

        // Interviewer 1b attempts lookup again -> Takeover allowed
        $response2 = $this->actingAs($this->interviewer1b)
            ->post(route('interview.lookup'), [
                'identifier' => '270005',
            ]);

        $response2->assertSessionHasNoErrors();
        $response2->assertRedirect(route('interview.session', $session));

        $session->refresh();
        $this->assertSame($this->interviewer1b->id, $session->interviewer_id);
    }

    public function test_submit_and_lock_session_calculates_scores_locks_permanently_and_prevents_interviewer_reentry(): void
    {
        $registration = $this->createVerifiedRegistration(false, '270006');
        $stage1 = InterviewStage::where('admission_period_id', $this->period->id)->where('stage_order', 1)->first();
        $criteria = $stage1->criteria()->where('is_active', true)->get();
        $this->assertGreaterThan(0, $criteria->count());

        $session = InterviewSession::create([
            'registration_id' => $registration->id,
            'interview_stage_id' => $stage1->id,
            'interviewer_id' => $this->interviewer1->id,
            'status' => 'IN_PROGRESS',
            'started_at' => now(),
        ]);

        // 1. Interviewer can view session while IN_PROGRESS
        $this->actingAs($this->interviewer1)
            ->get(route('interview.session', $session))
            ->assertOk()
            ->assertSee('Formulir Penilaian')
            ->assertSee($registration->applicant->full_name);

        // Photo route check during IN_PROGRESS
        $this->actingAs($this->interviewer1)
            ->get(route('interview.session.photo', $session))
            ->assertOk();

        // 2. Submit scores
        $scores = [];
        $totalExpected = 0;
        foreach ($criteria as $criterion) {
            $scores[$criterion->id] = 85;
            $totalExpected += 85;
        }
        $avgExpected = round($totalExpected / $criteria->count(), 2);

        $submitResponse = $this->actingAs($this->interviewer1)
            ->post(route('interview.session.submit', $session), [
                'scores' => $scores,
                'notes' => 'Siswa sangat lancar membaca jilid 4.',
            ]);

        $submitResponse->assertRedirect(route('interview.index'));
        $submitResponse->assertSessionHas('success');

        // Check DB state
        $session->refresh();
        $this->assertSame('COMPLETED', $session->status);
        $this->assertNotNull($session->completed_at);
        $this->assertSame($totalExpected, $session->total_score);
        $this->assertEquals($avgExpected, (float) $session->average_score);
        $this->assertSame('Siswa sangat lancar membaca jilid 4.', $session->notes);

        foreach ($criteria as $criterion) {
            $this->assertDatabaseHas('interview_scores', [
                'interview_session_id' => $session->id,
                'interview_criterion_id' => $criterion->id,
                'score' => 85,
            ]);
        }

        // Check audit log
        $this->assertDatabaseHas('admin_configuration_audit_logs', [
            'entity_type' => 'interview_session',
            'entity_id' => $session->id,
            'action' => 'locked',
            'user_id' => $this->interviewer1->id,
        ]);

        // 3. Absolute Score Isolation: Interviewer CANNOT re-enter session
        $reentryResponse = $this->actingAs($this->interviewer1)
            ->get(route('interview.session', $session));

        $reentryResponse->assertRedirect(route('interview.index'));
        $reentryResponse->assertSessionHas('warning', 'Sesi wawancara untuk siswa ini sudah selesai dan dikunci.');

        // 4. Photo route blocked after session COMPLETED
        $this->actingAs($this->interviewer1)
            ->get(route('interview.session.photo', $session))
            ->assertForbidden();

        // 5. Subsequent lookup for the same stage is blocked
        $lookupAgain = $this->actingAs($this->interviewer1)
            ->post(route('interview.lookup'), [
                'identifier' => '270006',
            ]);
        $lookupAgain->assertSessionHasErrors('identifier');
    }

    public function test_interviewer_can_cancel_in_progress_session(): void
    {
        $registration = $this->createVerifiedRegistration(false, '270007');
        $stage1 = InterviewStage::where('admission_period_id', $this->period->id)->where('stage_order', 1)->first();

        $session = InterviewSession::create([
            'registration_id' => $registration->id,
            'interview_stage_id' => $stage1->id,
            'interviewer_id' => $this->interviewer1->id,
            'status' => 'IN_PROGRESS',
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->interviewer1)
            ->post(route('interview.session.cancel', $session));

        $response->assertRedirect(route('interview.index'));
        $response->assertSessionHas('info');

        $this->assertDatabaseMissing('interview_sessions', [
            'id' => $session->id,
        ]);
    }
}
