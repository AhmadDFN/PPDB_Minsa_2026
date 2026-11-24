<?php

declare(strict_types=1);

namespace Tests\Feature\Interview;

use App\Enums\AdmissionPeriodStatus;
use App\Models\AdmissionPeriod;
use App\Models\InterviewBatch;
use App\Models\InterviewCriterion;
use App\Models\InterviewScore;
use App\Models\InterviewSession;
use App\Models\InterviewStage;
use App\Models\Registration;
use App\Models\User;
use Database\Seeders\InterviewMasterSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterviewMasterManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    private User $adminPpdb;

    private User $interviewer;

    private User $applicant;

    private AdmissionPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->superadmin = User::factory()->admin()->create();
        $this->superadmin->assignRole('superadmin');

        $this->adminPpdb = User::factory()->admin()->create();
        $this->adminPpdb->assignRole('admin_ppdb');

        $this->interviewer = User::factory()->admin()->create();
        $this->interviewer->assignRole('interviewer_stage_1');

        $this->applicant = User::factory()->applicant()->create();

        $this->period = AdmissionPeriod::factory()->create([
            'is_active' => true,
            'status' => AdmissionPeriodStatus::ACTIVE,
            'academic_year' => '2026/2027',
        ]);

        $this->seed(InterviewMasterSeeder::class);
    }

    public function test_superadmin_can_view_and_create_batch_and_rejects_overlapping_range(): void
    {
        // 1. Superadmin can view index
        $this->actingAs($this->superadmin)
            ->get(route('admin.interview-batches.index'))
            ->assertOk()
            ->assertSee('Jadwal & Kuota Wawancara', false);

        // Existing batches in seeder: 1..70, 71..140, 141..210, etc.
        // 2. Reject overlapping sequence range (e.g. 50..100 overlaps with 1..70 and 71..140)
        $this->actingAs($this->superadmin)
            ->post(route('admin.interview-batches.store'), [
                'admission_period_id' => $this->period->id,
                'batch_number' => 6,
                'title' => 'Gelombang 6 Overlap',
                'interview_date' => now()->addDays(14)->format('Y-m-d'),
                'start_time' => '07:30',
                'end_time' => '14:00',
                'quota' => 50,
                'sequence_start' => 50,
                'sequence_end' => 100,
            ])
            ->assertSessionHasErrors('sequence_start');

        // 3. Create non-overlapping batch succeeds (e.g. 351..420)
        $this->actingAs($this->superadmin)
            ->post(route('admin.interview-batches.store'), [
                'admission_period_id' => $this->period->id,
                'batch_number' => 6,
                'title' => 'Gelombang 6 Valid',
                'interview_date' => now()->addDays(14)->format('Y-m-d'),
                'start_time' => '07:30',
                'end_time' => '14:00',
                'quota' => 70,
                'sequence_start' => 351,
                'sequence_end' => 420,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.interview-batches.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('interview_batches', [
            'admission_period_id' => $this->period->id,
            'title' => 'Gelombang 6 Valid',
            'sequence_start' => 351,
            'sequence_end' => 420,
        ]);
    }

    public function test_batch_assigned_to_registration_cannot_be_deleted(): void
    {
        /** @var InterviewBatch $batch */
        $batch = InterviewBatch::query()->where('admission_period_id', $this->period->id)->firstOrFail();

        // Assign a registration to this batch
        Registration::factory()->create([
            'admission_period_id' => $this->period->id,
            'interview_batch_id' => $batch->id,
        ]);

        $this->actingAs($this->superadmin)
            ->delete(route('admin.interview-batches.destroy', $batch))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('interview_batches', ['id' => $batch->id]);

        // A batch with no registrations can be deleted
        $emptyBatch = InterviewBatch::query()
            ->create([
                'admission_period_id' => $this->period->id,
                'batch_number' => 99,
                'title' => 'Batch Kosong',
                'interview_date' => now()->addDays(30)->format('Y-m-d'),
                'start_time' => '07:30',
                'end_time' => '14:00',
                'quota' => 50,
                'sequence_start' => 901,
                'sequence_end' => 950,
                'is_active' => true,
            ]);

        $this->actingAs($this->superadmin)
            ->delete(route('admin.interview-batches.destroy', $emptyBatch))
            ->assertRedirect(route('admin.interview-batches.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('interview_batches', ['id' => $emptyBatch->id]);
    }

    public function test_superadmin_can_update_stage_and_manage_criteria(): void
    {
        $stage = InterviewStage::query()
            ->where('admission_period_id', $this->period->id)
            ->where('code', 'STAGE_1')
            ->firstOrFail();

        // 1. Update Stage info
        $this->actingAs($this->superadmin)
            ->put(route('admin.interview-stages.update', $stage), [
                'title' => 'Tahap 1: BTQ & Tahfidz Terbarukan',
                'description' => 'Panduan penguji BTQ terbarukan.',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.interview-stages.index'))
            ->assertSessionHas('success');

        $this->assertSame('Tahap 1: BTQ & Tahfidz Terbarukan', $stage->fresh()->title);

        // 2. Add Criterion
        $this->actingAs($this->superadmin)
            ->post(route('admin.interview-criteria.store', $stage), [
                'title' => 'Tajwid & Ghunnah',
                'description' => 'Penerapan hukum tajwid dasar.',
                'sort_order' => 3,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.interview-stages.index'))
            ->assertSessionHas('success');

        $newCriterion = InterviewCriterion::query()
            ->where('interview_stage_id', $stage->id)
            ->where('title', 'Tajwid & Ghunnah')
            ->firstOrFail();
        $this->assertSame(3, $newCriterion->sort_order);

        // 3. Update Criterion
        $this->actingAs($this->superadmin)
            ->put(route('admin.interview-criteria.update', $newCriterion), [
                'title' => 'Tajwid, Ghunnah & Waqaf',
                'description' => 'Penerapan hukum tajwid dan tanda waqaf.',
                'sort_order' => 3,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.interview-stages.index'))
            ->assertSessionHas('success');

        $this->assertSame('Tajwid, Ghunnah & Waqaf', $newCriterion->fresh()->title);

        // 4. Delete criterion without scores succeeds
        $this->actingAs($this->superadmin)
            ->delete(route('admin.interview-criteria.destroy', $newCriterion))
            ->assertRedirect(route('admin.interview-stages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('interview_criteria', ['id' => $newCriterion->id]);
    }

    public function test_criterion_with_scores_cannot_be_deleted_but_can_be_deactivated(): void
    {
        $stage = InterviewStage::query()
            ->where('admission_period_id', $this->period->id)
            ->where('code', 'STAGE_1')
            ->firstOrFail();

        $criterion = $stage->criteria()->firstOrFail();

        // Create an interview session and score on this criterion
        $registration = Registration::factory()->create(['admission_period_id' => $this->period->id]);
        $session = InterviewSession::query()->create([
            'registration_id' => $registration->id,
            'interview_stage_id' => $stage->id,
            'interviewer_id' => $this->interviewer->id,
            'status' => 'COMPLETED',
            'started_at' => now()->subMinutes(15),
            'completed_at' => now(),
            'total_score' => 85,
            'average_score' => 85.00,
        ]);

        InterviewScore::query()->create([
            'interview_session_id' => $session->id,
            'interview_criterion_id' => $criterion->id,
            'score' => 85,
        ]);

        // Attempt deletion: must be rejected
        $this->actingAs($this->superadmin)
            ->delete(route('admin.interview-criteria.destroy', $criterion))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('interview_criteria', ['id' => $criterion->id]);

        // But can be deactivated (is_active = false)
        $this->actingAs($this->superadmin)
            ->put(route('admin.interview-criteria.update', $criterion), [
                'title' => $criterion->title,
                'description' => $criterion->description,
                'sort_order' => $criterion->sort_order,
                'is_active' => 0,
            ])
            ->assertRedirect(route('admin.interview-stages.index'))
            ->assertSessionHas('success');

        $this->assertFalse($criterion->fresh()->is_active);
    }

    public function test_minimum_one_active_criterion_is_enforced_on_active_stage(): void
    {
        $stage = InterviewStage::query()
            ->where('admission_period_id', $this->period->id)
            ->where('code', 'STAGE_1')
            ->firstOrFail();

        // Stage 1 has 2 criteria. Deactivate one first.
        $criteria = $stage->criteria()->get();
        $first = $criteria[0];
        $second = $criteria[1];

        $first->update(['is_active' => false]);

        // Attempt to deactivate the only remaining active criterion
        $this->actingAs($this->superadmin)
            ->put(route('admin.interview-criteria.update', $second), [
                'title' => $second->title,
                'is_active' => 0,
            ])
            ->assertSessionHas('error');

        $this->assertTrue($second->fresh()->is_active);

        // Attempt to delete the only remaining active criterion
        $this->actingAs($this->superadmin)
            ->delete(route('admin.interview-criteria.destroy', $second))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('interview_criteria', ['id' => $second->id]);
    }

    public function test_role_isolation_non_superadmin_forbidden(): void
    {
        // 1. admin_ppdb is forbidden
        $this->actingAs($this->adminPpdb)
            ->get(route('admin.interview-batches.index'))
            ->assertForbidden();

        $this->actingAs($this->adminPpdb)
            ->get(route('admin.interview-stages.index'))
            ->assertForbidden();

        // 2. interviewer is forbidden
        $this->actingAs($this->interviewer)
            ->get(route('admin.interview-batches.index'))
            ->assertForbidden();

        $this->actingAs($this->interviewer)
            ->get(route('admin.interview-stages.index'))
            ->assertForbidden();

        // 3. applicant is forbidden
        $this->actingAs($this->applicant)
            ->get(route('admin.interview-batches.index'))
            ->assertForbidden();

        // 4. guest is redirected to login
        auth()->logout();
        $this->get(route('admin.interview-batches.index'))
            ->assertRedirect();
    }
}
