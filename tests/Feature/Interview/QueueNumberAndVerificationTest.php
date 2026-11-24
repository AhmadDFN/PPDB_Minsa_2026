<?php

declare(strict_types=1);

namespace Tests\Feature\Interview;

use App\Actions\Registration\ApproveAndAssignQueueNumberAction;
use App\Actions\Registration\GenerateRegistrationPdfAction;
use App\Actions\Registration\SubmitRegistrationAction;
use App\Enums\AdmissionPeriodStatus;
use App\Enums\DocumentVerificationStatus;
use App\Enums\GuardianRelationship;
use App\Enums\PdfDocumentType;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Applicant;
use App\Models\ApplicantAddress;
use App\Models\ApplicantEducation;
use App\Models\ApplicantGuardian;
use App\Models\DocumentRequirement;
use App\Models\InterviewBatch;
use App\Models\Program;
use App\Models\Registration;
use App\Models\RegistrationConsent;
use App\Models\RegistrationDocument;
use App\Models\User;
use Database\Seeders\InterviewMasterSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class QueueNumberAndVerificationTest extends TestCase
{
    use RefreshDatabase;

    private AdmissionPeriod $period;

    private Program $program;

    private AdmissionPeriodProgram $periodProgram;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->period = AdmissionPeriod::factory()->create([
            'is_active' => true,
            'status' => AdmissionPeriodStatus::ACTIVE,
            'academic_year' => '2026/2027',
        ]);

        $this->program = Program::factory()->create([
            'name' => 'Program Reguler',
            'code' => 'REGULER',
        ]);

        $this->periodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $this->period->id,
            'program_id' => $this->program->id,
            'is_active' => true,
        ]);

        $this->seed(InterviewMasterSeeder::class);
        Storage::fake('ppdb_private');
    }

    public function test_submit_registration_leaves_registration_number_and_queue_null(): void
    {
        $registration = $this->createCompleteDraftRegistration();
        $applicantUser = $registration->applicant->user;

        $action = app(SubmitRegistrationAction::class);
        $submitted = $action->execute(
            $applicantUser,
            'Ayah Calon Siswa',
            '127.0.0.1',
            'PHPUnit'
        );

        $this->assertSame(RegistrationStatus::SUBMITTED, $submitted->status);
        $this->assertNull($submitted->registration_number);
        $this->assertNull($submitted->queue_sequence);
        $this->assertNull($submitted->verified_at);
        $this->assertNull($submitted->verified_by);
        $this->assertNull($submitted->interview_batch_id);
    }

    public function test_approve_action_fails_if_required_documents_are_not_approved(): void
    {
        $admin = $this->createAdmin();
        $registration = $this->createCompleteSubmittedRegistration();

        // Create a required document that is still PENDING
        $requirement = DocumentRequirement::factory()->create([
            'admission_period_id' => $this->period->id,
            'program_id' => $this->program->id,
            'is_required' => true,
            'name' => 'Kartu Keluarga',
        ]);

        RegistrationDocument::factory()->create([
            'registration_id' => $registration->id,
            'document_requirement_id' => $requirement->id,
            'verification_status' => DocumentVerificationStatus::PENDING,
        ]);

        $action = app(ApproveAndAssignQueueNumberAction::class);

        $this->expectException(ValidationException::class);
        $action->execute($registration, $admin);
    }

    public function test_approve_action_assigns_sequential_numbers_and_maps_to_batch(): void
    {
        $admin = $this->createAdmin();
        $registration1 = $this->createCompleteSubmittedRegistration();
        $registration2 = $this->createCompleteSubmittedRegistration();

        $action = app(ApproveAndAssignQueueNumberAction::class);

        $approved1 = $action->execute($registration1, $admin);
        $approved2 = $action->execute($registration2, $admin);

        // Sequence should be 1 and 2 with 4-digit format (270001, 270002)
        $this->assertSame(1, $approved1->queue_sequence);
        $this->assertSame('270001', $approved1->registration_number);
        $this->assertNotNull($approved1->verified_at);
        $this->assertSame($admin->id, $approved1->verified_by);
        $this->assertNotNull($approved1->interview_batch_id);

        $batch1 = InterviewBatch::find($approved1->interview_batch_id);
        $this->assertNotNull($batch1);
        $this->assertSame(1, $batch1->sequence_start);
        $this->assertSame(70, $batch1->sequence_end);

        $this->assertSame(2, $approved2->queue_sequence);
        $this->assertSame('270002', $approved2->registration_number);
        $this->assertSame($batch1->id, $approved2->interview_batch_id);

        // Audit log must exist
        $this->assertDatabaseHas('registration_logs', [
            'registration_id' => $approved1->id,
            'action' => 'verified_and_queued',
        ]);
    }

    public function test_admin_endpoint_sahkan_succeeds_and_updates_status(): void
    {
        $admin = $this->createAdmin();
        $registration = $this->createCompleteSubmittedRegistration();

        $response = $this->actingAs($admin)->post(
            route('admin.document-verification.approve-registration', $registration)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $registration->refresh();
        $this->assertTrue($registration->isVerified());
        $this->assertSame('270001', $registration->registration_number);
        $this->assertSame(1, $registration->queue_sequence);
    }

    public function test_non_admin_cannot_access_sahkan_endpoint(): void
    {
        $applicantUser = User::factory()->applicant()->create();
        $registration = $this->createCompleteSubmittedRegistration();

        $response = $this->actingAs($applicantUser)->post(
            route('admin.document-verification.approve-registration', $registration)
        );

        $response->assertForbidden();
    }

    public function test_final_pdf_cannot_be_generated_before_verification_but_succeeds_after(): void
    {
        $admin = $this->createAdmin();
        $registration = $this->createCompleteSubmittedRegistration();
        $applicantUser = $registration->applicant->user;

        $pdfAction = app(GenerateRegistrationPdfAction::class);

        // 1. Before verification (registration_number is null): must throw ValidationException
        $caught = false;
        try {
            $pdfAction->execute($registration, $applicantUser, PdfDocumentType::FINAL);
        } catch (ValidationException $e) {
            $caught = true;
            $this->assertStringContainsString('Bukti PDF final hanya dapat dibuat setelah pendaftaran disahkan', $e->getMessage());
        }
        $this->assertTrue($caught, 'Final PDF should not be generated before registration is approved and numbered.');

        // 2. Now approve and assign queue number
        $approveAction = app(ApproveAndAssignQueueNumberAction::class);
        $approveAction->execute($registration, $admin);

        $registration->refresh();

        // 3. Generate final PDF should now succeed
        $pdfVersion = $pdfAction->execute($registration, $applicantUser, PdfDocumentType::FINAL);
        $this->assertNotNull($pdfVersion);
        $this->assertSame(1, $pdfVersion->version_number);
        $this->assertStringStartsWith('%PDF-', Storage::disk('ppdb_private')->get($pdfVersion->file_path));
    }

    private function createAdmin(): User
    {
        $user = User::factory()->admin()->create();
        $user->assignRole('admin_ppdb');

        return $user;
    }

    private function createCompleteDraftRegistration(): Registration
    {
        $user = User::factory()->applicant()->create();

        $applicant = Applicant::factory()->create([
            'user_id' => $user->id,
            'birth_date' => '2020-05-15',
            'full_name' => 'Calon Siswa Penguji',
        ]);

        ApplicantAddress::factory()->create([
            'applicant_id' => $applicant->id,
        ]);

        ApplicantGuardian::factory()->create([
            'applicant_id' => $applicant->id,
            'relationship_type' => GuardianRelationship::FATHER,
            'name' => 'Ayah Calon Siswa',
            'is_alive' => true,
            'nik' => '3573010101750001',
            'phone' => '081234567890',
            'education' => 'S1',
            'occupation' => 'PNS',
            'monthly_income' => '3.000.000 - 5.000.000',
        ]);

        ApplicantGuardian::factory()->create([
            'applicant_id' => $applicant->id,
            'relationship_type' => GuardianRelationship::MOTHER,
            'name' => 'Ibu Calon Siswa',
            'is_alive' => true,
            'nik' => '3573010101850002',
            'phone' => '081234567891',
            'education' => 'S1',
            'occupation' => 'Ibu Rumah Tangga',
            'monthly_income' => 'Tidak Berpenghasilan',
        ]);

        ApplicantEducation::factory()->create([
            'applicant_id' => $applicant->id,
            'education_level' => 'tk',
            'school_name' => 'TK Madiun',
        ]);

        DocumentRequirement::firstOrCreate([
            'admission_period_id' => $this->period->id,
            'program_id' => $this->program->id,
            'code' => 'KK',
        ], [
            'name' => 'Kartu Keluarga',
            'is_required' => false,
            'max_size_kb' => 2048,
            'allowed_mimes' => 'application/pdf,image/jpeg,image/png',
        ]);

        return Registration::query()->create([
            'applicant_id' => $applicant->id,
            'admission_period_id' => $this->period->id,
            'admission_period_program_id' => $this->periodProgram->id,
            'status' => RegistrationStatus::DRAFT,
            'registration_number' => null,
            'queue_sequence' => null,
            'data_revision' => 1,
        ]);
    }

    private function createCompleteSubmittedRegistration(): Registration
    {
        $registration = $this->createCompleteDraftRegistration();

        RegistrationConsent::query()->create([
            'registration_id' => $registration->id,
            'accepted_by_name' => 'Ayah Calon Siswa',
            'agreed_at' => now(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'consent_version' => 'v1.0',
            'statement_text' => 'Saya menyatakan seluruh data benar.',
        ]);

        $registration->update([
            'status' => RegistrationStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);

        return $registration->fresh(['applicant.user', 'consent']);
    }
}
