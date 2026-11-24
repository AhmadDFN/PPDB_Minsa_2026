<?php

declare(strict_types=1);

namespace Tests\Feature\Applicant;

use App\Actions\Registration\ApproveAndAssignQueueNumberAction;
use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\AdmissionPeriodStatus;
use App\Enums\DocumentVerificationStatus;
use App\Enums\Gender;
use App\Enums\PreviousEducationLevel;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Applicant;
use App\Models\ApplicantAddress;
use App\Models\ApplicantEducation;
use App\Models\ApplicantGuardian;
use App\Models\DocumentRequirement;
use App\Models\Program;
use App\Models\Registration;
use App\Models\RegistrationConsent;
use App\Models\RegistrationDocument;
use App\Models\RegistrationLog;
use App\Models\User;
use App\Support\NikManager;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RegistrationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{
     *     user: User,
     *     applicant: Applicant,
     *     period: AdmissionPeriod,
     *     periodProgram: AdmissionPeriodProgram,
     *     registration: Registration,
     *     requirement: DocumentRequirement
     * }
     */
    private function createRegistration(
        bool $complete = true,
        ?AdmissionPeriod $period = null,
        ?AdmissionPeriodProgram $periodProgram = null,
        ?DocumentRequirement $requirement = null
    ): array {
        $this->travelTo(Carbon::parse('2026-11-18 10:00:00'));

        $index = User::query()->count() + 1;
        $nik = sprintf('3573%012d', $index);
        $user = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'nik_lookup_hash' => NikManager::hash($nik),
            'nik_encrypted' => NikManager::encrypt($nik),
        ]);
        $applicant = Applicant::factory()->create([
            'user_id' => $user->id,
            'full_name' => 'Calon Siswa Lengkap',
            'gender' => Gender::MALE,
            'birth_place' => 'Kota Madiun',
            'birth_date' => '2020-01-15',
            'child_order' => $complete ? 1 : null,
            'siblings_count' => $complete ? 0 : null,
        ]);

        $period ??= AdmissionPeriod::factory()->create([
            'name' => 'PPDB MIN 1 Kota Madiun 2026/2027',
            'academic_year' => '2026/2027',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);

        if ($periodProgram === null) {
            $program = Program::factory()->create([
                'code' => $index % 2 === 1 ? 'REGULER' : 'UNGGULAN',
                'name' => $index % 2 === 1 ? 'Program Reguler' : 'Program Unggulan',
                'is_active' => true,
            ]);
            $periodProgram = AdmissionPeriodProgram::factory()->create([
                'admission_period_id' => $period->id,
                'program_id' => $program->id,
                'is_active' => true,
            ]);
        }

        $registration = Registration::query()->create([
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period->id,
            'admission_period_program_id' => $periodProgram->id,
            'status' => RegistrationStatus::DRAFT,
            'data_revision' => 1,
        ]);

        $requirement ??= DocumentRequirement::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $periodProgram->program_id,
            'name' => 'Akta Kelahiran',
            'is_required' => true,
        ]);

        if ($complete) {
            ApplicantAddress::factory()->create([
                'applicant_id' => $applicant->id,
                'address_line' => 'Jalan Merpati Nomor 10',
                'kelurahan' => 'Kejuron',
                'kecamatan' => 'Taman',
                'city' => 'Kota Madiun',
                'province' => 'Jawa Timur',
            ]);
            ApplicantGuardian::factory()->father()->create([
                'applicant_id' => $applicant->id,
                'name' => 'Budi Santoso',
                'nik' => '3573010101750001',
                'education' => 'SMA/SMK',
                'occupation' => 'Wiraswasta',
                'monthly_income' => '3.000.000 - 5.000.000',
                'phone' => '081234567890',
                'is_alive' => true,
            ]);
            ApplicantGuardian::factory()->mother()->create([
                'applicant_id' => $applicant->id,
                'name' => 'Siti Aminah',
                'nik' => '3573010101850002',
                'education' => 'SMA/SMK',
                'occupation' => 'Guru',
                'monthly_income' => '3.000.000 - 5.000.000',
                'phone' => '081234567891',
                'is_alive' => true,
            ]);
            ApplicantEducation::factory()->create([
                'applicant_id' => $applicant->id,
                'education_level' => PreviousEducationLevel::NONE,
                'school_name' => null,
            ]);

            $path = 'ppdb/documents/registration-'.$registration->id.'.pdf';
            Storage::disk('ppdb_private')->put($path, "%PDF-1.4\ncomplete document");
            RegistrationDocument::factory()->create([
                'registration_id' => $registration->id,
                'document_requirement_id' => $requirement->id,
                'file_path' => $path,
                'original_name' => 'akta.pdf',
            ]);
        }

        return [
            'user' => $user,
            'applicant' => $applicant,
            'period' => $period,
            'periodProgram' => $periodProgram,
            'registration' => $registration,
            'requirement' => $requirement,
        ];
    }

    public function test_guest_is_redirected_from_registration_review(): void
    {
        $this->get(route('applicant.registration.review'))
            ->assertRedirect(route('login'));
    }

    public function test_review_masks_student_and_parent_nik_and_omits_private_paths(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();
        $father = $data['applicant']->father;
        $fatherNik = '3573010101750001';
        $father->update(['nik' => $fatherNik]);

        $this->actingAs($data['user'])
            ->get(route('applicant.registration.review'))
            ->assertOk()
            ->assertSee($data['user']->getMaskedNik())
            ->assertSee(NikManager::mask($fatherNik))
            ->assertDontSee($data['user']->getDecryptedNik())
            ->assertDontSee($fatherNik)
            ->assertDontSee('storage/app/private')
            ->assertSee('Akta Kelahiran');
    }

    public function test_incomplete_registration_shows_missing_sections_and_cannot_be_submitted(): void
    {
        $data = $this->createRegistration(complete: false);

        $this->actingAs($data['user'])
            ->get(route('applicant.registration.review'))
            ->assertOk()
            ->assertSee('Data ayah')
            ->assertSee('Data ibu')
            ->assertSee('Alamat domisili')
            ->assertSee('Pendidikan sebelumnya')
            ->assertSee('Dokumen wajib')
            ->assertDontSee('name="consent"');

        $this->actingAs($data['user'])
            ->post(route('applicant.registration.submit'), [
                'consent' => '1',
                'consent_name' => 'Orang Tua Siswa',
            ])
            ->assertSessionHasErrors('registration');

        $this->assertSame(RegistrationStatus::DRAFT, $data['registration']->fresh()->status);
        $this->assertNull($data['registration']->fresh()->registration_number);
        $this->assertDatabaseCount('registration_consents', 0);
    }

    public function test_missing_required_parent_fields_block_submission(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();
        $data['applicant']->father->update(['phone' => null]);

        $this->actingAs($data['user'])
            ->post(route('applicant.registration.submit'), [
                'consent' => '1',
                'consent_name' => 'Orang Tua Siswa',
            ])
            ->assertSessionHasErrors('registration');

        $this->assertSame(RegistrationStatus::DRAFT, $data['registration']->fresh()->status);
        $this->assertNull($data['registration']->fresh()->registration_number);
    }

    public function test_submission_fails_closed_when_consent_configuration_is_missing(): void
    {
        $data = $this->createRegistration();
        config([
            'ppdb.registration.consent_statement' => '',
            'ppdb.registration.consent_version' => '',
        ]);

        $this->actingAs($data['user'])
            ->get(route('applicant.registration.review'))
            ->assertOk()
            ->assertDontSee('name="consent"')
            ->assertSee('Pernyataan persetujuan belum dikonfigurasi oleh madrasah');

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        $this->actingAs($data['user'])->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Orang Tua Siswa',
        ]);
    }

    public function test_successful_submission_records_consent_number_and_audit_log(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();

        $this->actingAs($data['user'])
            ->post(route('applicant.registration.submit'), [
                'consent' => '1',
                'consent_name' => 'Budi Santoso',
                'registration_number' => 'ATTACKER-CONTROLLED',
                'status' => RegistrationStatus::DRAFT->value,
            ])
            ->assertRedirect(route('applicant.registration.review'))
            ->assertSessionHas('success');

        $registration = $data['registration']->fresh();
        $this->assertSame(RegistrationStatus::SUBMITTED, $registration->status);
        $this->assertNull($registration->registration_number);
        $this->assertNull($registration->queue_sequence);
        $this->assertNotNull($registration->submitted_at);
        $this->assertSame(1, $registration->data_revision);

        $consent = RegistrationConsent::query()->where('registration_id', $registration->id)->firstOrFail();
        $this->assertSame('Budi Santoso', $consent->accepted_by_name);
        $this->assertSame('v1.0', $consent->consent_version);
        $this->assertSame((string) config('ppdb.registration.consent_statement'), $consent->statement_text);
        $this->assertTrue($consent->is_agreed);

        $this->assertDatabaseHas('registration_logs', [
            'registration_id' => $registration->id,
            'action' => 'submitted',
            'from_revision' => 1,
            'to_revision' => 1,
        ]);
        $this->assertDatabaseCount('registration_pdf_versions', 0);

        $this->actingAs($data['user'])
            ->get(route('applicant.dashboard'))
            ->assertOk()
            ->assertSee('Menunggu Pengesahan Berkas')
            ->assertSee('Terkirim (Resmi)');
    }

    public function test_repeated_submission_is_idempotent_and_does_not_issue_another_number(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();
        $payload = ['consent' => '1', 'consent_name' => 'Budi Santoso'];

        $this->actingAs($data['user'])->post(route('applicant.registration.submit'), $payload);
        $firstRegistration = $data['registration']->fresh();
        $submittedAt = $firstRegistration->submitted_at?->toISOString();

        $this->actingAs($data['user'])
            ->post(route('applicant.registration.submit'), $payload)
            ->assertRedirect(route('applicant.registration.review'));

        $registration = $data['registration']->fresh();
        $this->assertSame($firstRegistration->registration_number, $registration->registration_number);
        $this->assertSame($submittedAt, $registration->submitted_at?->toISOString());
        $this->assertDatabaseCount('registration_consents', 1);
        $this->assertDatabaseCount('registration_logs', 1);
        $this->assertDatabaseCount('yearly_registration_sequences', 0);
    }

    public function test_sequence_is_unique_and_increments_within_the_same_period_program(): void
    {
        Storage::fake('ppdb_private');
        $first = $this->createRegistration();
        $second = $this->createRegistration(
            period: $first['period'],
            periodProgram: $first['periodProgram'],
            requirement: $first['requirement'],
        );

        foreach ([$first, $second] as $data) {
            $this->actingAs($data['user'])->post(route('applicant.registration.submit'), [
                'consent' => '1',
                'consent_name' => 'Orang Tua Siswa',
            ])->assertRedirect(route('applicant.registration.review'));
        }

        RegistrationDocument::query()->whereIn('registration_id', [$first['registration']->id, $second['registration']->id])->update([
            'verification_status' => DocumentVerificationStatus::APPROVED,
        ]);
        $admin = User::factory()->admin()->create();
        $action = app(ApproveAndAssignQueueNumberAction::class);
        $action->execute($first['registration']->fresh(), $admin);
        $action->execute($second['registration']->fresh(), $admin);

        $this->assertSame('270001', $first['registration']->fresh()->registration_number);
        $this->assertSame('270002', $second['registration']->fresh()->registration_number);
        $this->assertDatabaseCount('yearly_registration_sequences', 1);
    }

    public function test_registration_number_sequence_is_scoped_to_each_program(): void
    {
        Storage::fake('ppdb_private');
        $first = $this->createRegistration();
        $second = $this->createRegistration(period: $first['period']);

        foreach ([$first, $second] as $data) {
            $this->actingAs($data['user'])->post(route('applicant.registration.submit'), [
                'consent' => '1',
                'consent_name' => 'Orang Tua Siswa',
            ])->assertRedirect(route('applicant.registration.review'));
        }

        RegistrationDocument::query()->whereIn('registration_id', [$first['registration']->id, $second['registration']->id])->update([
            'verification_status' => DocumentVerificationStatus::APPROVED,
        ]);
        $admin = User::factory()->admin()->create();
        $action = app(ApproveAndAssignQueueNumberAction::class);
        $action->execute($first['registration']->fresh(), $admin);
        $action->execute($second['registration']->fresh(), $admin);

        $this->assertSame('270001', $first['registration']->fresh()->registration_number);
        $this->assertSame('270002', $second['registration']->fresh()->registration_number);
        $this->assertDatabaseCount('yearly_registration_sequences', 1);
    }

    public function test_registration_number_sequence_does_not_reset_across_periods_in_the_same_academic_year(): void
    {
        Storage::fake('ppdb_private');
        $first = $this->createRegistration();
        $this->actingAs($first['user'])->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Orang Tua Siswa',
        ])->assertRedirect(route('applicant.registration.review'));
        $first['period']->update(['status' => AdmissionPeriodStatus::CLOSED, 'is_active' => false]);
        $secondPeriod = AdmissionPeriod::factory()->create([
            'name' => 'PPDB MIN 1 Kota Madiun 2026/2027 - Periode Kedua',
            'academic_year' => '2026/2027',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);
        $secondPeriodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $secondPeriod->id,
            'program_id' => $first['periodProgram']->program_id,
            'is_active' => true,
        ]);
        $secondRequirement = DocumentRequirement::factory()->create([
            'admission_period_id' => $secondPeriod->id,
            'program_id' => $first['periodProgram']->program_id,
            'is_required' => true,
        ]);
        $second = $this->createRegistration(
            period: $secondPeriod,
            periodProgram: $secondPeriodProgram,
            requirement: $secondRequirement,
        );

        $this->actingAs($second['user'])->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Orang Tua Siswa',
        ])->assertRedirect(route('applicant.registration.review'));

        RegistrationDocument::query()->whereIn('registration_id', [$first['registration']->id, $second['registration']->id])->update([
            'verification_status' => DocumentVerificationStatus::APPROVED,
        ]);
        $admin = User::factory()->admin()->create();
        $action = app(ApproveAndAssignQueueNumberAction::class);
        $action->execute($first['registration']->fresh(), $admin);
        $action->execute($second['registration']->fresh(), $admin);

        $this->assertSame('270001', $first['registration']->fresh()->registration_number);
        $this->assertSame('270002', $second['registration']->fresh()->registration_number);
        $this->assertDatabaseCount('yearly_registration_sequences', 1);
    }

    public function test_migration_backfills_the_highest_legacy_sequence_per_academic_year_and_program(): void
    {
        $first = $this->createRegistration();
        $secondPeriod = AdmissionPeriod::factory()->create([
            'name' => 'PPDB MIN 1 Kota Madiun 2026/2027 - Periode Kedua',
            'academic_year' => str_replace('/', '-', $first['period']->academic_year),
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'status' => AdmissionPeriodStatus::DRAFT,
            'is_active' => false,
        ]);
        $secondPeriodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $secondPeriod->id,
            'program_id' => $first['periodProgram']->program_id,
            'is_active' => true,
        ]);

        DB::table('registration_sequences')->insert([
            [
                'admission_period_program_id' => $first['periodProgram']->id,
                'last_sequence' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'admission_period_program_id' => $secondPeriodProgram->id,
                'last_sequence' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        Schema::drop('registration_number_sequences');

        $migration = require database_path('migrations/2026_11_18_151640_create_registration_number_sequences_table.php');
        $migration->up();

        $this->assertDatabaseCount('registration_number_sequences', 1);
        $this->assertDatabaseHas('registration_number_sequences', [
            'academic_year' => str_replace('/', '-', $first['period']->academic_year),
            'program_id' => $first['periodProgram']->program_id,
            'last_sequence' => 7,
        ]);
    }

    public function test_program_code_outside_the_approved_set_blocks_submission(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();
        $data['periodProgram']->program->update(['code' => 'REGULER2']);

        $this->actingAs($data['user'])
            ->from(route('applicant.registration.review'))
            ->post(route('applicant.registration.submit'), [
                'consent' => '1',
                'consent_name' => 'Orang Tua Siswa',
            ])
            ->assertSessionHasErrors('registration');

        $this->assertSame(RegistrationStatus::DRAFT, $data['registration']->fresh()->status);
        $this->assertNull($data['registration']->fresh()->registration_number);
        $this->assertDatabaseCount('registration_number_sequences', 0);
    }

    public function test_unconfigured_required_documents_block_submission_but_optional_documents_do_not(): void
    {
        Storage::fake('ppdb_private');
        $required = $this->createRegistration();
        $required['registration']->documents()->delete();

        $this->from(route('applicant.registration.review'))
            ->actingAs($required['user'])
            ->post(route('applicant.registration.submit'), [
                'consent' => '1',
                'consent_name' => 'Orang Tua Siswa',
            ])
            ->assertSessionHasErrors('registration');

        $this->assertSame(RegistrationStatus::DRAFT, $required['registration']->fresh()->status);
        $required['period']->update([
            'status' => AdmissionPeriodStatus::CLOSED,
            'is_active' => false,
            'end_date' => now()->subDay(),
        ]);

        $optionalPeriod = AdmissionPeriod::factory()->create([
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);
        $optional = $this->createRegistration(period: $optionalPeriod);
        $optional['requirement']->update(['is_required' => false]);

        $this->actingAs($optional['user'])
            ->post(route('applicant.registration.submit'), [
                'consent' => '1',
                'consent_name' => 'Orang Tua Siswa',
            ])
            ->assertRedirect(route('applicant.registration.review'));

        $this->assertSame(RegistrationStatus::SUBMITTED, $optional['registration']->fresh()->status);
    }

    public function test_closed_period_blocks_submission(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();
        $data['period']->update([
            'status' => AdmissionPeriodStatus::CLOSED,
            'is_active' => false,
            'end_date' => now()->subDay(),
        ]);

        $this->actingAs($data['user'])
            ->post(route('applicant.registration.submit'), [
                'consent' => '1',
                'consent_name' => 'Orang Tua Siswa',
            ])
            ->assertSessionHasErrors('registration');

        $this->assertSame(RegistrationStatus::DRAFT, $data['registration']->fresh()->status);
        $this->assertNull($data['registration']->fresh()->registration_number);
        $this->assertDatabaseCount('registration_number_sequences', 0);
    }

    public function test_inactive_program_blocks_submission(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();
        $data['periodProgram']->update(['is_active' => false]);

        $this->actingAs($data['user'])
            ->post(route('applicant.registration.submit'), [
                'consent' => '1',
                'consent_name' => 'Orang Tua Siswa',
            ])
            ->assertSessionHasErrors('registration');

        $this->assertSame(RegistrationStatus::DRAFT, $data['registration']->fresh()->status);
        $this->assertNull($data['registration']->fresh()->registration_number);
    }

    public function test_inactive_program_record_blocks_submission(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();
        $data['periodProgram']->program->update(['is_active' => false]);

        $this->actingAs($data['user'])
            ->post(route('applicant.registration.submit'), [
                'consent' => '1',
                'consent_name' => 'Orang Tua Siswa',
            ])
            ->assertSessionHasErrors('registration');

        $this->assertSame(RegistrationStatus::DRAFT, $data['registration']->fresh()->status);
        $this->assertNull($data['registration']->fresh()->registration_number);
    }

    public function test_applicant_cannot_submit_another_applicants_registration(): void
    {
        Storage::fake('ppdb_private');
        $owner = $this->createRegistration();
        $other = $this->createRegistration(
            complete: false,
            period: $owner['period'],
            periodProgram: $owner['periodProgram'],
            requirement: $owner['requirement'],
        );

        $this->actingAs($other['user'])
            ->post(route('applicant.registration.submit'), [
                'registration_id' => $owner['registration']->id,
                'registration_number' => 'ATTACKER-CONTROLLED',
                'status' => RegistrationStatus::SUBMITTED->value,
                'consent' => '1',
                'consent_name' => 'Orang Tua Siswa',
            ])
            ->assertSessionHasErrors('registration');

        $this->assertSame(RegistrationStatus::DRAFT, $owner['registration']->fresh()->status);
        $this->assertNull($owner['registration']->fresh()->registration_number);
        $this->assertSame(RegistrationStatus::DRAFT, $other['registration']->fresh()->status);
    }

    public function test_post_submission_profile_edit_increments_revision_and_records_redacted_fields(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();
        $this->actingAs($data['user'])->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Budi Santoso',
        ]);
        $registration = $data['registration']->fresh();
        $registrationNumber = $registration->registration_number;
        $submittedAt = $registration->submitted_at?->toISOString();
        $nik = $data['user']->getDecryptedNik();

        $this->actingAs($data['user'])
            ->post(route('applicant.biodata.update'), [
                'full_name' => 'Nama Diperbarui',
                'gender' => 'L',
                'birth_place' => 'Kota Madiun',
                'birth_date' => '2020-01-15',
                'nisn' => $data['applicant']->nisn,
                'child_order' => 1,
                'siblings_count' => 0,
            ])
            ->assertSessionHas('success');

        $registration->refresh();
        $this->assertSame(RegistrationStatus::SUBMITTED, $registration->status);
        $this->assertSame($registrationNumber, $registration->registration_number);
        $this->assertSame($submittedAt, $registration->submitted_at?->toISOString());
        $this->assertSame(2, $registration->data_revision);
        $this->assertSame('Nama Diperbarui', $data['applicant']->fresh()->full_name);

        $log = RegistrationLog::query()
            ->where('registration_id', $registration->id)
            ->where('action', 'updated_after_submission')
            ->firstOrFail();
        $this->assertSame(['fields' => ['biodata.full_name']], $log->changes);
        $this->assertStringNotContainsString($nik, json_encode($log->changes, JSON_THROW_ON_ERROR));
    }

    public function test_no_op_profile_edit_does_not_increment_revision(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();
        $this->actingAs($data['user'])->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Budi Santoso',
        ]);

        $this->actingAs($data['user'])
            ->post(route('applicant.biodata.update'), [
                'full_name' => $data['applicant']->full_name,
                'gender' => 'L',
                'birth_place' => 'Kota Madiun',
                'birth_date' => '2020-01-15',
                'nisn' => $data['applicant']->nisn,
                'child_order' => 1,
                'siblings_count' => 0,
            ]);

        $this->assertSame(1, $data['registration']->fresh()->data_revision);
        $this->assertDatabaseCount('registration_logs', 1);
    }

    public function test_applicant_cannot_edit_submitted_registration_after_period_closes(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();
        $this->actingAs($data['user'])->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Budi Santoso',
        ]);
        $data['period']->update([
            'status' => AdmissionPeriodStatus::CLOSED,
            'is_active' => false,
            'end_date' => now()->subDay(),
        ]);

        $this->actingAs($data['user'])
            ->post(route('applicant.biodata.update'), [
                'full_name' => 'Nama Tidak Boleh Berubah',
                'gender' => 'L',
                'birth_place' => 'Kota Madiun',
                'birth_date' => '2020-01-15',
                'nisn' => $data['applicant']->nisn,
                'child_order' => 1,
                'siblings_count' => 0,
            ])
            ->assertSessionHasErrors('registration');

        $this->assertSame('Calon Siswa Lengkap', $data['applicant']->fresh()->full_name);
        $this->assertSame(RegistrationStatus::SUBMITTED, $data['registration']->fresh()->status);
        $this->assertSame(1, $data['registration']->fresh()->data_revision);
        $this->assertDatabaseCount('registration_logs', 1);
    }

    public function test_submitted_document_replacement_is_audited_and_keeps_the_historical_file(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();
        $this->actingAs($data['user'])->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Budi Santoso',
        ]);

        $document = RegistrationDocument::query()
            ->where('registration_id', $data['registration']->id)
            ->firstOrFail();
        $oldPath = $document->file_path;

        $this->actingAs($data['user'])
            ->post(route('applicant.documents.upload', $data['requirement']->id), [
                'file' => UploadedFile::fake()->createWithContent('akta-baru.pdf', "%PDF-1.4\nreplacement"),
                'reason' => 'Memperbarui hasil pindai',
            ])
            ->assertRedirect(route('applicant.documents.index'));

        $document->refresh();
        $this->assertNotSame($oldPath, $document->file_path);
        Storage::disk('ppdb_private')->assertExists($oldPath);
        Storage::disk('ppdb_private')->assertExists($document->file_path);
        $this->assertDatabaseHas('registration_document_revisions', [
            'registration_document_id' => $document->id,
            'file_path' => $oldPath,
        ]);
        $this->assertSame(2, $data['registration']->fresh()->data_revision);
        $this->assertSame(
            ['fields' => ['documents']],
            RegistrationLog::query()->where('action', 'updated_after_submission')->firstOrFail()->changes,
        );
    }
}
