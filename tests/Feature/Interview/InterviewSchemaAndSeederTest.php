<?php

declare(strict_types=1);

namespace Tests\Feature\Interview;

use App\Models\AdmissionPeriod;
use App\Models\InterviewBatch;
use App\Models\InterviewScore;
use App\Models\InterviewSession;
use App\Models\InterviewStage;
use App\Models\Registration;
use App\Models\User;
use Database\Seeders\AdmissionPeriodSeeder;
use Database\Seeders\InterviewAccountSeeder;
use Database\Seeders\InterviewMasterSeeder;
use Database\Seeders\ProgramSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InterviewSchemaAndSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_interview_tables_exist_with_proper_columns(): void
    {
        $this->assertTrue(Schema::hasTable('interview_batches'));
        $this->assertTrue(Schema::hasTable('interview_stages'));
        $this->assertTrue(Schema::hasTable('interview_criteria'));
        $this->assertTrue(Schema::hasTable('interview_sessions'));
        $this->assertTrue(Schema::hasTable('interview_scores'));

        $this->assertTrue(Schema::hasColumns('registrations', [
            'queue_sequence',
            'verified_at',
            'verified_by',
            'interview_batch_id',
        ]));
    }

    public function test_interview_seeders_create_roles_accounts_stages_and_batches(): void
    {
        $this->seed(ProgramSeeder::class);
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(AdmissionPeriodSeeder::class);
        $this->seed(InterviewAccountSeeder::class);
        $this->seed(InterviewMasterSeeder::class);

        // 1. Roles
        $this->assertNotNull(Role::findByName('interviewer_stage_1', 'web'));
        $this->assertNotNull(Role::findByName('interviewer_stage_2', 'web'));
        $this->assertNotNull(Role::findByName('interviewer_stage_3', 'web'));

        // 2. Accounts
        $this->assertDatabaseHas('users', ['email' => 'admin1@minsatama.sch.id']);
        $this->assertDatabaseHas('users', ['email' => 'penguji1a@minsatama.sch.id']);
        $this->assertDatabaseHas('users', ['email' => 'penguji2a@minsatama.sch.id']);
        $this->assertDatabaseHas('users', ['email' => 'penguji3a@minsatama.sch.id']);

        $userStage1 = User::where('email', 'penguji1a@minsatama.sch.id')->first();
        $this->assertTrue($userStage1->hasRole('interviewer_stage_1'));
        $this->assertFalse($userStage1->hasRole('interviewer_stage_2'));

        // 3. Stages
        $period = AdmissionPeriod::where('is_active', true)->first();
        $this->assertSame(3, InterviewStage::where('admission_period_id', $period->id)->count());

        $stage1 = InterviewStage::where('code', 'STAGE_1')->first();
        $this->assertSame(2, $stage1->criteria()->count());

        $stage2 = InterviewStage::where('code', 'STAGE_2')->first();
        $this->assertSame(4, $stage2->criteria()->count());

        $stage3 = InterviewStage::where('code', 'STAGE_3')->first();
        $this->assertSame(3, $stage3->criteria()->count());
        $this->assertTrue($stage3->is_confidential);
        $this->assertSame('UNGGULAN', $stage3->program_scope);

        // 4. Batches
        $this->assertSame(5, InterviewBatch::where('admission_period_id', $period->id)->count());
        $batch1 = InterviewBatch::where('batch_number', 1)->first();
        $this->assertSame(1, $batch1->sequence_start);
        $this->assertSame(70, $batch1->sequence_end);
    }

    public function test_interview_session_and_scores_relationships(): void
    {
        $this->seed(ProgramSeeder::class);
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(AdmissionPeriodSeeder::class);
        $this->seed(InterviewMasterSeeder::class);

        $period = AdmissionPeriod::where('is_active', true)->first();
        $stage1 = InterviewStage::where('code', 'STAGE_1')->first();
        $criterion = $stage1->criteria()->first();

        $interviewer = User::factory()->create();
        $registration = Registration::factory()->create([
            'admission_period_id' => $period->id,
        ]);

        $session = InterviewSession::create([
            'registration_id' => $registration->id,
            'interview_stage_id' => $stage1->id,
            'interviewer_id' => $interviewer->id,
            'status' => 'IN_PROGRESS',
            'started_at' => now(),
        ]);

        $score = InterviewScore::create([
            'interview_session_id' => $session->id,
            'interview_criterion_id' => $criterion->id,
            'score' => 85,
        ]);

        $this->assertSame(1, $session->scores()->count());
        $this->assertSame(85, $session->scores->first()->score);
        $this->assertSame($registration->id, $session->registration->id);
    }
}
