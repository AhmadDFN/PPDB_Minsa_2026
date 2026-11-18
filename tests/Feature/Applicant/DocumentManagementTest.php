<?php

declare(strict_types=1);

namespace Tests\Feature\Applicant;

use App\Actions\Document\UploadRegistrationDocumentAction;
use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\AdmissionPeriodStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Applicant;
use App\Models\DocumentRequirement;
use App\Models\Program;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use App\Models\RegistrationDocumentRevision;
use App\Models\User;
use App\Support\NikManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class DocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createRegistration(?int $limitKb = null): array
    {
        $nik = sprintf('3573%012d', User::query()->count() + 1);
        $user = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'nik_lookup_hash' => NikManager::hash($nik),
            'nik_encrypted' => NikManager::encrypt($nik),
        ]);
        $applicant = Applicant::factory()->create(['user_id' => $user->id]);
        $period = AdmissionPeriod::factory()->create([
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);
        $program = Program::factory()->create();
        $periodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
            'is_active' => true,
        ]);
        $registration = Registration::factory()->create([
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period->id,
            'admission_period_program_id' => $periodProgram->id,
        ]);
        $requirement = DocumentRequirement::factory()->create(array_filter([
            'admission_period_id' => $period->id,
            'program_id' => null,
            'max_size_kb' => $limitKb,
        ], static fn ($value) => $value !== null));

        return [$user, $applicant, $registration, $requirement];
    }

    private function pdfFile(string $name, int $sizeBytes): UploadedFile
    {
        $header = "%PDF-1.4\n";
        $content = $header.str_repeat('A', max(0, $sizeBytes - strlen($header)));

        return UploadedFile::fake()->createWithContent($name, $content);
    }

    public function test_guest_cannot_view_document_management_or_private_files(): void
    {
        $this->get(route('applicant.documents.index'))->assertRedirect(route('login'));
    }

    public function test_applicant_can_view_document_requirements_and_upload_form(): void
    {
        [$user, , , $requirement] = $this->createRegistration();
        Storage::fake('ppdb_private');

        $this->actingAs($user)
            ->get(route('applicant.documents.index'))
            ->assertOk()
            ->assertSee($requirement->name)
            ->assertSee('5120 KB')
            ->assertSee(route('applicant.documents.upload', $requirement->id), false);
    }

    public function test_applicant_can_upload_file_below_global_limit(): void
    {
        [$user, , , $requirement] = $this->createRegistration();
        Storage::fake('ppdb_private');

        $response = $this->actingAs($user)->post(
            route('applicant.documents.upload', $requirement->id),
            ['file' => $this->pdfFile('bukti.pdf', 100 * 1024)],
        );

        $response->assertRedirect(route('applicant.documents.index'));
        $this->assertDatabaseCount('registration_documents', 1);
        $document = RegistrationDocument::firstOrFail();
        $this->assertStringStartsWith('ppdb/documents/', $document->file_path);
        $this->assertSame(
            hash_file('sha256', Storage::disk('ppdb_private')->path($document->file_path)),
            $document->file_hash,
        );
    }

    public function test_file_exactly_5120_kb_is_accepted(): void
    {
        [$user, , , $requirement] = $this->createRegistration();
        Storage::fake('ppdb_private');

        $response = $this->actingAs($user)->post(
            route('applicant.documents.upload', $requirement->id),
            ['file' => $this->pdfFile('batas.pdf', 5120 * 1024)],
        );

        $response->assertRedirect(route('applicant.documents.index'));
        $document = RegistrationDocument::firstOrFail();
        $this->assertSame(5120 * 1024, $document->file_size_bytes);
        $this->assertSame(64, strlen($document->file_hash));
    }

    public function test_file_above_global_limit_is_rejected(): void
    {
        [$user, , , $requirement] = $this->createRegistration();
        Storage::fake('ppdb_private');

        $response = $this->actingAs($user)->post(
            route('applicant.documents.upload', $requirement->id),
            ['file' => $this->pdfFile('terlalu-besar.pdf', 5120 * 1024 + 1)],
        );

        $response->assertSessionHasErrors([
            'file' => 'Ukuran berkas tidak boleh melebihi 5120 KB (5 MB).',
        ]);
        $this->assertDatabaseCount('registration_documents', 0);
    }

    public function test_smaller_per_document_limit_is_enforced(): void
    {
        [$user, , , $requirement] = $this->createRegistration(64);
        Storage::fake('ppdb_private');

        $response = $this->actingAs($user)->post(
            route('applicant.documents.upload', $requirement->id),
            ['file' => $this->pdfFile('diatas-limit.pdf', 65 * 1024)],
        );

        $response->assertSessionHasErrors([
            'file' => 'Ukuran berkas tidak boleh lebih dari 64 KB.',
        ]);
        $this->assertDatabaseCount('registration_documents', 0);

        $this->actingAs($user)->post(
            route('applicant.documents.upload', $requirement->id),
            ['file' => $this->pdfFile('dibawah-limit.pdf', 64 * 1024)],
        )->assertRedirect(route('applicant.documents.index'));
        $this->assertDatabaseCount('registration_documents', 1);
    }

    public function test_document_requirement_cannot_exceed_5120_kb(): void
    {
        [, , $registration] = $this->createRegistration();

        try {
            DocumentRequirement::factory()->create([
                'admission_period_id' => $registration->admission_period_id,
                'max_size_kb' => 5121,
            ]);
            $this->fail('A document requirement above the global limit should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('max_size_kb', $exception->errors());
        }
    }

    public function test_invalid_extension_and_mime_spoofing_are_rejected(): void
    {
        [$user, , , $requirement] = $this->createRegistration();
        Storage::fake('ppdb_private');

        $this->actingAs($user)->post(
            route('applicant.documents.upload', $requirement->id),
            ['file' => $this->pdfFile('dokumen.txt', 1024)],
        )->assertSessionHasErrors('file');

        $this->actingAs($user)->post(
            route('applicant.documents.upload', $requirement->id),
            ['file' => $this->pdfFile('dokumen.jpg', 1024)],
        )->assertSessionHasErrors('file');

        $this->assertDatabaseCount('registration_documents', 0);
    }

    public function test_applicant_cannot_access_another_applicants_document(): void
    {
        [$owner, , , $requirement] = $this->createRegistration();
        Storage::fake('ppdb_private');
        $this->actingAs($owner)->post(
            route('applicant.documents.upload', $requirement->id),
            ['file' => $this->pdfFile('milik-pemilik.pdf', 1024)],
        );
        $document = RegistrationDocument::firstOrFail();
        [$otherApplicant] = $this->createRegistration();

        $this->actingAs($otherApplicant)
            ->get(route('applicant.documents.preview', $document->id))
            ->assertNotFound();

        $this->actingAs($otherApplicant)
            ->get(route('applicant.documents.download', $document->id))
            ->assertNotFound();
    }

    public function test_replacement_preserves_old_file_and_exposes_private_revision_history(): void
    {
        [$user, , , $requirement] = $this->createRegistration();
        Storage::fake('ppdb_private');
        $this->actingAs($user)->post(
            route('applicant.documents.upload', $requirement->id),
            ['file' => $this->pdfFile('pertama.pdf', 1024)],
        )->assertRedirect(route('applicant.documents.index'));
        $document = RegistrationDocument::firstOrFail();
        $oldPath = $document->file_path;

        $this->actingAs($user)->post(
            route('applicant.documents.upload', $requirement->id),
            [
                'file' => $this->pdfFile('pengganti.pdf', 2048),
                'reason' => 'Hasil pindai <script>alert(1)</script>',
            ],
        )->assertRedirect(route('applicant.documents.index'));

        $document->refresh();
        $revision = RegistrationDocumentRevision::firstOrFail();
        $this->assertNotSame($oldPath, $document->file_path);
        Storage::disk('ppdb_private')->assertExists($oldPath);
        Storage::disk('ppdb_private')->assertExists($document->file_path);
        $this->assertSame($oldPath, $revision->file_path);
        $this->assertSame('Hasil pindai <script>alert(1)</script>', $revision->reason);

        $this->actingAs($user)
            ->get(route('applicant.documents.index'))
            ->assertOk()
            ->assertSee('Hasil pindai &lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);

        $this->actingAs($user)
            ->get(route('applicant.documents.revisions.preview', [$document->id, $revision->id]))
            ->assertOk();
        $this->actingAs($user)
            ->get(route('applicant.documents.revisions.download', [$document->id, $revision->id]))
            ->assertOk();
    }

    public function test_database_failure_cleans_newly_stored_file(): void
    {
        [, , $registration, $requirement] = $this->createRegistration();
        Storage::fake('ppdb_private');
        $event = 'eloquent.creating: '.RegistrationDocument::class;
        Event::listen($event, static function (): void {
            throw new RuntimeException('Simulated database persistence failure.');
        });

        try {
            app(UploadRegistrationDocumentAction::class)->execute(
                $registration,
                $requirement,
                $this->pdfFile('gagal-simpan.pdf', 1024),
            );
            $this->fail('The simulated database failure should be surfaced.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated database persistence failure.', $exception->getMessage());
        } finally {
            Event::forget($event);
        }

        $this->assertSame([], Storage::disk('ppdb_private')->allFiles());
        $this->assertDatabaseCount('registration_documents', 0);
    }

    public function test_dashboard_includes_document_completeness(): void
    {
        [$user, , $registration, $requirement] = $this->createRegistration();
        Storage::fake('ppdb_private');

        $this->actingAs($user)
            ->get(route('applicant.dashboard'))
            ->assertOk()
            ->assertSee('0 dari 1 dokumen wajib');

        $this->actingAs($user)->post(
            route('applicant.documents.upload', $requirement->id),
            ['file' => $this->pdfFile('lengkap.pdf', 1024)],
        );

        $this->actingAs($user)
            ->get(route('applicant.dashboard'))
            ->assertOk()
            ->assertSee('1 dari 1 dokumen wajib');

        $this->assertSame($registration->id, RegistrationDocument::firstOrFail()->registration_id);
    }
}
