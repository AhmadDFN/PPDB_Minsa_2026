<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\DocumentVerificationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\DocumentRequirement;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Storage::fake('ppdb_private');
    }

    public function test_verification_queue_only_lists_submitted_registrations(): void
    {
        $submitted = $this->documentFor(Registration::factory()->submitted()->create());
        $draft = $this->documentFor(Registration::factory()->draft()->create());

        $response = $this->actingAs($this->admin())->get(route('admin.document-verification.index'));

        $response->assertOk()
            ->assertSee($submitted->registration->applicant->full_name)
            ->assertDontSee($draft->registration->applicant->full_name);
    }

    public function test_admin_without_permission_is_forbidden_and_preview_is_private(): void
    {
        $admin = $this->admin();
        $admin->syncRoles([]);
        $document = $this->documentFor(Registration::factory()->submitted()->create());
        Storage::disk('ppdb_private')->put($document->file_path, '%PDF-test');

        $this->actingAs($admin)->get(route('admin.document-verification.index'))->assertForbidden();
        $this->actingAs($this->admin())->get(route('admin.document-verification.preview', [$document->registration, $document]))->assertOk();
        $other = $this->documentFor(Registration::factory()->submitted()->create());
        $this->actingAs($this->admin())->get(route('admin.document-verification.preview', [$document->registration, $other]))->assertNotFound();
    }

    public function test_approve_and_revision_requirements_are_recorded_in_history_and_audit(): void
    {
        $admin = $this->admin();
        $document = $this->documentFor(Registration::factory()->submitted()->create());

        $this->actingAs($admin)->post(route('admin.document-verification.revision', [$document->registration, $document]), [])->assertSessionHasErrors('reason');
        $this->actingAs($admin)->post(route('admin.document-verification.revision', [$document->registration, $document]), ['reason' => 'Foto kurang jelas'])->assertRedirect();
        $document->refresh();
        $this->assertSame(DocumentVerificationStatus::REVISION_REQUIRED, $document->verification_status);
        $this->assertSame('Foto kurang jelas', $document->verification_reason);
        $this->assertDatabaseHas('registration_document_verifications', ['registration_document_id' => $document->id, 'to_status' => 'REVISION_REQUIRED']);
        $this->actingAs($admin)->post(route('admin.document-verification.approve', [$document->registration, $document]))->assertRedirect();
        $this->assertDatabaseHas('admin_configuration_audit_logs', ['entity_type' => 'registration_document', 'entity_id' => $document->id, 'action' => 'verification_APPROVED', 'user_id' => $admin->id]);
    }

    public function test_draft_documents_cannot_be_verified(): void
    {
        $document = $this->documentFor(Registration::factory()->draft()->create());
        $this->actingAs($this->admin())->post(route('admin.document-verification.approve', [$document->registration, $document]))->assertNotFound();
    }

    private function admin(): User
    {
        $user = User::factory()->admin()->create();
        $user->assignRole('admin_ppdb');

        return $user;
    }

    private function documentFor(Registration $registration): RegistrationDocument
    {
        $period = AdmissionPeriod::findOrFail($registration->admission_period_id);
        $programId = AdmissionPeriodProgram::findOrFail($registration->admission_period_program_id)->program_id;
        $requirement = DocumentRequirement::factory()->create(['admission_period_id' => $period->id, 'program_id' => $programId]);

        return RegistrationDocument::factory()->create(['registration_id' => $registration->id, 'document_requirement_id' => $requirement->id]);
    }
}
