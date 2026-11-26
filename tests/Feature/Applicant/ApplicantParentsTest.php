<?php

declare(strict_types=1);

namespace Tests\Feature\Applicant;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\GuardianRelationship;
use App\Models\AdmissionPeriod;
use App\Models\Applicant;
use App\Models\ApplicantGuardian;
use App\Models\User;
use App\Support\NikManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantParentsTest extends TestCase
{
    use RefreshDatabase;

    private function completeParentPayload(): array
    {
        $parent = [
            'name' => 'Parent Test',
            'nik' => '3573010101800001',
            'birth_place' => 'Madiun',
            'birth_date' => '1980-01-01',
            'status_label' => 'Masih Hidup',
            'education' => 'D4/S1',
            'occupation' => 'PNS',
            'monthly_income' => '2.500.001 - 3.500.000',
            'phone' => '081234567890',
            'residence' => 'Kota Madiun',
            'address_line' => 'Jl. Sitinggil No. 1',
            'rt' => '001',
            'rw' => '002',
            'province' => 'Jawa Timur',
            'city' => 'Kota Madiun',
            'kecamatan' => 'Taman',
            'kelurahan' => 'Demangan',
            'postal_code' => '63136',
            'residence_status' => 'Rumah Orangtua',
        ];
        $payload = [];
        foreach (['father', 'mother'] as $prefix) {
            foreach ($parent as $field => $value) {
                $payload[$prefix.'_'.$field] = $value;
            }
        }
        $payload['has_guardian'] = '0';

        return $payload;
    }

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

    public function test_guest_cannot_view_parents_form(): void
    {
        $response = $this->get(route('applicant.parents.edit'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_applicant_can_view_parents_form(): void
    {
        [$user] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->get(route('applicant.parents.edit'));

        $response->assertStatus(200);
        $response->assertSee('Data Orang Tua');
        $response->assertSee('Data Ayah Kandung');
        $response->assertSee('Data Ibu Kandung');
        $response->assertSee('Data Wali');
    }

    public function test_applicant_can_save_father_and_mother_data(): void
    {
        [$user, $applicant] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->post(route('applicant.parents.update'), array_merge($this->completeParentPayload(), [
            // Father
            'father_name' => 'Bambang Sudibyo',
            'father_nik' => '3573010101800001',
            'father_education' => 'D4/S1',
            'father_occupation' => 'PNS',
            'father_monthly_income' => '2.500.001 - 3.500.000',
            'father_phone' => '081234567890',

            // Mother
            'mother_name' => 'Siti Aminah',
            'mother_nik' => '3573010101850002',
            'mother_education' => 'D4/S1',
            'mother_occupation' => 'Guru/Dosen',
            'mother_monthly_income' => '2.500.001 - 3.500.000',
            'mother_phone' => '081234567891',

            'has_guardian' => '0',
            'nav_action' => 'save',
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('applicant_guardians', [
            'applicant_id' => $applicant->id,
            'relationship_type' => GuardianRelationship::FATHER->value,
            'name' => 'Bambang Sudibyo',
            'occupation' => 'PNS',
        ]);

        $this->assertDatabaseHas('applicant_guardians', [
            'applicant_id' => $applicant->id,
            'relationship_type' => GuardianRelationship::MOTHER->value,
            'name' => 'Siti Aminah',
            'occupation' => 'Guru/Dosen',
        ]);

        $this->assertSame(2, ApplicantGuardian::where('applicant_id', $applicant->id)->count());
    }

    public function test_applicant_can_save_optional_guardian(): void
    {
        [$user, $applicant] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->post(route('applicant.parents.update'), array_merge($this->completeParentPayload(), [
            'father_name' => 'Bambang Sudibyo',
            'father_nik' => '3573010101800001',
            'father_status_label' => 'Masih Hidup',
            'father_education' => 'D4/S1',
            'father_occupation' => 'Wiraswasta',
            'father_monthly_income' => '2.500.001 - 3.500.000',
            'father_phone' => '081234567890',
            'mother_name' => 'Siti Aminah',
            'mother_nik' => '3573010101850002',
            'mother_status_label' => 'Masih Hidup',
            'mother_education' => 'D4/S1',
            'mother_occupation' => 'Tidak bekerja',
            'mother_monthly_income' => '2.500.001 - 3.500.000',
            'mother_phone' => '081234567891',

            // Guardian
            'has_guardian' => '1',
            'guardian_name' => 'H. Abdul Qodir',
            'guardian_nik' => '3573010101600003',
            'guardian_phone' => '081299998888',
            'guardian_occupation' => 'Pensiunan',
            'nav_action' => 'next',
        ]));

        $response->assertRedirect(route('applicant.education.edit'));

        $this->assertSame(3, ApplicantGuardian::where('applicant_id', $applicant->id)->count());
        $this->assertDatabaseHas('applicant_guardians', [
            'applicant_id' => $applicant->id,
            'relationship_type' => GuardianRelationship::GUARDIAN->value,
            'name' => 'H. Abdul Qodir',
        ]);

        // When subsequently toggled off, guardian record is removed
        $this->actingAs($user, 'web')->post(route('applicant.parents.update'), array_merge($this->completeParentPayload(), [
            'father_name' => 'Bambang Sudibyo',
            'father_nik' => '3573010101800001',
            'father_education' => 'D4/S1',
            'father_occupation' => 'Wiraswasta',
            'father_monthly_income' => '2.500.001 - 3.500.000',
            'father_phone' => '081234567890',
            'mother_name' => 'Siti Aminah',
            'mother_nik' => '3573010101850002',
            'mother_education' => 'D4/S1',
            'mother_occupation' => 'Tidak bekerja',
            'mother_monthly_income' => '2.500.001 - 3.500.000',
            'mother_phone' => '081234567891',
            'has_guardian' => '0',
            'nav_action' => 'save',
        ]));

        $this->assertSame(2, ApplicantGuardian::where('applicant_id', $applicant->id)->count());
        $this->assertDatabaseMissing('applicant_guardians', [
            'applicant_id' => $applicant->id,
            'relationship_type' => GuardianRelationship::GUARDIAN->value,
        ]);
    }

    public function test_parents_validation_requires_father_and_mother_mandatory_fields(): void
    {
        [$user] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->post(route('applicant.parents.update'), [
            'father_name' => '',
            'father_occupation' => '',
            'mother_name' => '',
            'mother_occupation' => '',
        ]);

        $response->assertSessionHasErrors([
            'father_name',
            'father_nik',
            'father_education',
            'father_occupation',
            'father_monthly_income',
            'father_phone',
            'mother_name',
            'mother_nik',
            'mother_education',
            'mother_occupation',
            'mother_monthly_income',
            'mother_phone',
        ]);
    }

    public function test_guardian_name_is_required_if_has_guardian_is_checked(): void
    {
        [$user] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->post(route('applicant.parents.update'), [
            'father_name' => 'Ayah',
            'father_nik' => '3573010101800001',
            'father_is_alive' => '1',
            'father_education' => 'SMA/SMK',
            'father_occupation' => 'Swasta',
            'father_monthly_income' => '3.000.000 - 5.000.000',
            'father_phone' => '081234567890',
            'mother_name' => 'Ibu',
            'mother_nik' => '3573010101850002',
            'mother_is_alive' => '1',
            'mother_education' => 'SMA/SMK',
            'mother_occupation' => 'Swasta',
            'mother_monthly_income' => '3.000.000 - 5.000.000',
            'mother_phone' => '081234567891',
            'has_guardian' => '1',
            'guardian_name' => '', // Empty
        ]);

        $response->assertSessionHasErrors(['guardian_name']);
    }
}
