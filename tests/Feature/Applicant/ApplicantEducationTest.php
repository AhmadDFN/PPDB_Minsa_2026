<?php

declare(strict_types=1);

namespace Tests\Feature\Applicant;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PreviousEducationLevel;
use App\Models\AdmissionPeriod;
use App\Models\Applicant;
use App\Models\ApplicantEducation;
use App\Models\User;
use App\Support\NikManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantEducationTest extends TestCase
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

        AdmissionPeriod::factory()->active()->create();

        $applicant = Applicant::factory()->create(array_merge([
            'user_id' => $user->id,
            'full_name' => 'Calon Siswa Tes',
        ], $applicantAttributes));

        return [$user, $applicant];
    }

    public function test_guest_cannot_view_education_form(): void
    {
        $response = $this->get(route('applicant.education.edit'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_applicant_can_view_education_form(): void
    {
        [$user] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->get(route('applicant.education.edit'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Pendidikan Asal');
        $response->assertSee('Raudhatul Athfal (RA)');
        $response->assertSee('Taman Kanak-kanak (TK)');
    }

    public function test_applicant_can_save_education_with_school_name(): void
    {
        [$user, $applicant] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->post(route('applicant.education.update'), [
            'education_level' => 'tk',
            'school_name' => 'TK Pembina 1 Madiun',
            'npsn' => '69752310',
            'city' => 'Kota Madiun',
            'nav_action' => 'save',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('applicant_educations', [
            'applicant_id' => $applicant->id,
            'education_level' => PreviousEducationLevel::TK->value,
            'school_name' => 'TK Pembina 1 Madiun',
            'npsn' => '69752310',
            'city' => 'Kota Madiun',
        ]);
    }

    public function test_applicant_can_save_none_level_when_required_school_identifiers_are_provided(): void
    {
        [$user, $applicant] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->post(route('applicant.education.update'), [
            'education_level' => 'none',
            'school_name' => 'TK Contoh',
            'npsn' => '12345678',
            'nav_action' => 'next',
        ]);

        $response->assertRedirect(route('applicant.program.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('applicant_educations', [
            'applicant_id' => $applicant->id,
            'education_level' => PreviousEducationLevel::NONE->value,
            'school_name' => 'TK Contoh',
        ]);
    }

    public function test_education_validation_requires_school_name_when_level_is_not_none(): void
    {
        [$user] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->post(route('applicant.education.update'), [
            'education_level' => 'ra',
            'school_name' => '',
        ]);

        $response->assertSessionHasErrors(['school_name']);
    }

    public function test_education_update_does_not_duplicate_records(): void
    {
        [$user, $applicant] = $this->createApplicantUser();

        $this->actingAs($user, 'web')->post(route('applicant.education.update'), [
            'education_level' => 'paud',
            'school_name' => 'PAUD Melati',
            'npsn' => '12345678',
        ]);

        $this->actingAs($user, 'web')->post(route('applicant.education.update'), [
            'education_level' => 'tk',
            'school_name' => 'TK Harapan Bangsa',
            'npsn' => '12345679',
        ]);

        $this->assertSame(1, ApplicantEducation::where('applicant_id', $applicant->id)->count());
        $this->assertSame('TK Harapan Bangsa', $applicant->fresh()->education->school_name);
    }
}
