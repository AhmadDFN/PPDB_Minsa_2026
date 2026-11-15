<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Applicant;
use App\Models\ApplicantGuardian;
use App\Models\DocumentRequirement;
use App\Models\Program;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use App\Models\RegistrationDocumentRevision;
use App\Models\RegistrationPdfVersion;
use App\Models\User;
use App\Support\NikManager;
use Database\Seeders\ProgramSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaAndRulesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Rule 1: One NIK cannot be duplicated across the application.
     */
    public function test_rule_1_one_nik_cannot_be_duplicated(): void
    {
        $nik = '3573010101180001';
        $lookupHash = NikManager::hash($nik);

        // First user with this NIK
        User::factory()->create([
            'nik_lookup_hash' => $lookupHash,
            'nik_encrypted' => NikManager::encrypt($nik),
        ]);

        // Attempting to create second user with identical NIK lookup hash must throw QueryException
        $this->expectException(QueryException::class);

        User::factory()->create([
            'nik_lookup_hash' => $lookupHash,
            'nik_encrypted' => NikManager::encrypt($nik),
        ]);
    }

    /**
     * Rule 2: Different accounts may reuse the same phone number (siblings).
     */
    public function test_rule_2_different_accounts_may_reuse_the_same_phone_number(): void
    {
        $sharedPhone = '081234567899';

        $user1 = User::factory()->create(['phone' => $sharedPhone]);
        $user2 = User::factory()->create(['phone' => $sharedPhone]);

        $this->assertEquals($sharedPhone, $user1->phone);
        $this->assertEquals($sharedPhone, $user2->phone);
        $this->assertNotEquals($user1->id, $user2->id);
    }

    /**
     * Rule 3: Each applicant has only one profile (user_id is unique).
     */
    public function test_rule_3_each_applicant_has_only_one_profile(): void
    {
        $user = User::factory()->create();

        Applicant::factory()->create(['user_id' => $user->id]);

        // Attempting to create a second applicant profile for the same user must fail
        $this->expectException(QueryException::class);

        Applicant::factory()->create(['user_id' => $user->id]);
    }

    /**
     * Rule 4: Admin accounts cannot have applicant profiles.
     */
    public function test_rule_4_admin_accounts_are_distinguished_and_cannot_act_as_applicants(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isApplicant());
        $this->assertNull($admin->applicant);
    }

    /**
     * Rule 5: One applicant cannot register twice in the same admission period.
     */
    public function test_rule_5_one_applicant_cannot_register_twice_in_the_same_period(): void
    {
        $period = AdmissionPeriod::factory()->create();
        $program = Program::factory()->reguler()->create();
        $periodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
        ]);

        $applicant = Applicant::factory()->create();

        // First registration in this period
        Registration::factory()->create([
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period->id,
            'admission_period_program_id' => $periodProgram->id,
        ]);

        // Second registration attempt in the same period must throw QueryException
        $this->expectException(QueryException::class);

        Registration::factory()->create([
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period->id,
            'admission_period_program_id' => $periodProgram->id,
        ]);
    }

    /**
     * Rule 6: One applicant may register in different admission periods.
     */
    public function test_rule_6_one_applicant_may_register_in_different_periods(): void
    {
        $period1 = AdmissionPeriod::factory()->create(['academic_year' => '2025/2026']);
        $period2 = AdmissionPeriod::factory()->create(['academic_year' => '2026/2027']);

        $program = Program::factory()->reguler()->create();

        $periodProg1 = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period1->id,
            'program_id' => $program->id,
        ]);
        $periodProg2 = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period2->id,
            'program_id' => $program->id,
        ]);

        $applicant = Applicant::factory()->create();

        $reg1 = Registration::factory()->create([
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period1->id,
            'admission_period_program_id' => $periodProg1->id,
        ]);

        $reg2 = Registration::factory()->create([
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period2->id,
            'admission_period_program_id' => $periodProg2->id,
        ]);

        $this->assertEquals(2, $applicant->registrations()->count());
        $this->assertNotEquals($reg1->id, $reg2->id);
    }

    /**
     * Rule 7: Program in an admission period cannot be duplicated.
     */
    public function test_rule_7_program_cannot_be_duplicated_in_the_same_period(): void
    {
        $period = AdmissionPeriod::factory()->create();
        $program = Program::factory()->create();

        AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
        ]);

        // Duplicate program entry in same period must fail
        $this->expectException(QueryException::class);

        AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
        ]);
    }

    /**
     * Rule 8: Father and mother relationship types cannot be duplicated for the same applicant.
     */
    public function test_rule_8_father_and_mother_relationship_types_cannot_be_duplicated(): void
    {
        $applicant = Applicant::factory()->create();

        // Create father
        ApplicantGuardian::factory()->father()->create([
            'applicant_id' => $applicant->id,
        ]);

        // Attempting to create a second father for the same applicant must fail
        $this->expectException(QueryException::class);

        ApplicantGuardian::factory()->father()->create([
            'applicant_id' => $applicant->id,
        ]);
    }

    /**
     * Rule 9: Multiple document revisions are supported while active document is unique per requirement.
     */
    public function test_rule_9_multiple_document_revisions_are_supported(): void
    {
        $reg = Registration::factory()->create();
        $req = DocumentRequirement::factory()->create([
            'admission_period_id' => $reg->admission_period_id,
        ]);

        $doc = RegistrationDocument::factory()->create([
            'registration_id' => $reg->id,
            'document_requirement_id' => $req->id,
        ]);

        // Create revision 1
        RegistrationDocumentRevision::factory()->create([
            'registration_document_id' => $doc->id,
            'revision_number' => 1,
            'file_path' => 'docs/v1.pdf',
        ]);

        // Create revision 2 (replacement)
        RegistrationDocumentRevision::factory()->create([
            'registration_document_id' => $doc->id,
            'revision_number' => 2,
            'file_path' => 'docs/v2.pdf',
        ]);

        $this->assertEquals(2, $doc->revisions()->count());
    }

    /**
     * Rule 10: PDF version numbers cannot be duplicated for the same registration and type.
     */
    public function test_rule_10_pdf_version_numbers_cannot_be_duplicated_for_same_type(): void
    {
        $reg = Registration::factory()->submitted()->create();

        RegistrationPdfVersion::factory()->final()->create([
            'registration_id' => $reg->id,
            'version_number' => 1,
            'data_revision' => 1,
        ]);

        // Duplicate final version 1 must throw QueryException
        $this->expectException(QueryException::class);

        RegistrationPdfVersion::factory()->final()->create([
            'registration_id' => $reg->id,
            'version_number' => 1,
            'data_revision' => 1,
        ]);
    }

    /**
     * Rule 11: Only DRAFT and SUBMITTED registration statuses are accepted.
     */
    public function test_rule_11_only_draft_and_submitted_statuses_exist(): void
    {
        $cases = RegistrationStatus::cases();

        $this->assertCount(2, $cases);
        $this->assertEquals(['DRAFT', 'SUBMITTED'], array_map(fn ($c) => $c->value, $cases));
    }

    /**
     * Rule 12: Nullable registration numbers are supported for draft records.
     */
    public function test_rule_12_nullable_registration_numbers_supported_for_draft(): void
    {
        $reg = Registration::factory()->draft()->create();

        $this->assertNull($reg->registration_number);
        $this->assertTrue($reg->isDraft());
        $this->assertFalse($reg->isSubmitted());

        // Now finalize submission
        $reg->update([
            'registration_number' => 'PPDB-2026-0001',
            'status' => RegistrationStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);

        $this->assertEquals('PPDB-2026-0001', $reg->fresh()->registration_number);
        $this->assertTrue($reg->fresh()->isSubmitted());
    }

    /**
     * Additional test: Verify age calculation dynamically without database persistence.
     */
    public function test_age_calculation_is_accurate_and_not_persisted(): void
    {
        $applicant = Applicant::factory()->create([
            'birth_date' => '2019-07-01',
        ]);

        $referenceDate = '2026-07-01'; // Exactly 7 years
        $age = $applicant->calculateAgeAt($referenceDate);

        $this->assertEquals(7, $age['years']);
        $this->assertEquals(0, $age['months']);
        $this->assertEquals(0, $age['days']);
        $this->assertEquals('7 tahun 0 bulan 0 hari', $age['formatted']);
    }

    /**
     * Additional test: Verify seeders execute cleanly.
     */
    public function test_seeders_run_successfully(): void
    {
        $this->seed(ProgramSeeder::class);
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(SuperAdminSeeder::class);

        $this->assertDatabaseHas('programs', ['code' => 'UNGGULAN']);
        $this->assertDatabaseHas('programs', ['code' => 'REGULER']);
        $this->assertDatabaseHas('roles', ['name' => 'superadmin']);
        $this->assertDatabaseHas('roles', ['name' => 'admin_ppdb']);
        $this->assertDatabaseHas('users', ['email' => 'admin@minsatama.sch.id']);
    }
}
