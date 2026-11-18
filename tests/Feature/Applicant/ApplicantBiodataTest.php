<?php

declare(strict_types=1);

namespace Tests\Feature\Applicant;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\Gender;
use App\Models\AdmissionPeriod;
use App\Models\Applicant;
use App\Models\User;
use App\Support\NikManager;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantBiodataTest extends TestCase
{
    use RefreshDatabase;

    private function createApplicantUser(array $userAttributes = [], array $applicantAttributes = []): array
    {
        $nik = $userAttributes['nik'] ?? '3573010101190001';
        unset($userAttributes['nik']);

        $user = User::factory()->create(array_merge([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'nik_lookup_hash' => NikManager::hash($nik),
            'nik_encrypted' => NikManager::encrypt($nik),
        ], $userAttributes));

        AdmissionPeriod::factory()->create();

        $applicant = Applicant::factory()->create(array_merge([
            'user_id' => $user->id,
            'full_name' => 'Ahmad Dahlan',
            'gender' => Gender::MALE,
            'birth_place' => 'Madiun',
            'birth_date' => Carbon::parse('2019-05-15'),
        ], $applicantAttributes));

        return [$user, $applicant];
    }

    public function test_guest_cannot_view_biodata_form(): void
    {
        $response = $this->get(route('applicant.biodata.edit'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_applicant_can_view_biodata_form(): void
    {
        [$user, $applicant] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->get(route('applicant.biodata.edit'));

        $response->assertStatus(200);
        $response->assertSee('Biodata Calon Siswa');
        $response->assertSee($applicant->full_name);
        $response->assertSee('3573********0001'); // Masked NIK
        $response->assertSee('NIK terkunci');
    }

    public function test_applicant_can_update_biodata_and_redirect_back(): void
    {
        [$user, $applicant] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->post(route('applicant.biodata.update'), [
            'full_name' => 'Muhammad Rizqi Pratama',
            'gender' => 'L',
            'birth_place' => 'Kota Madiun',
            'birth_date' => '2019-08-20',
            'nisn' => '0123456789',
            'child_order' => 2,
            'siblings_count' => 3,
            'nav_action' => 'save',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $applicant->refresh();
        $this->assertSame('Muhammad Rizqi Pratama', $applicant->full_name);
        $this->assertSame(Gender::MALE, $applicant->gender);
        $this->assertSame('Kota Madiun', $applicant->birth_place);
        $this->assertSame('2019-08-20', $applicant->birth_date->format('Y-m-d'));
        $this->assertSame('0123456789', $applicant->nisn);
        $this->assertSame(2, $applicant->child_order);
        $this->assertSame(3, $applicant->siblings_count);
    }

    public function test_applicant_can_save_and_continue_to_address_step(): void
    {
        [$user, $applicant] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->post(route('applicant.biodata.update'), [
            'full_name' => 'Fatimah Az-Zahra',
            'gender' => 'P',
            'birth_place' => 'Madiun',
            'birth_date' => '2019-04-10',
            'nisn' => null,
            'child_order' => 1,
            'siblings_count' => 1,
            'nav_action' => 'next',
        ]);

        $response->assertRedirect(route('applicant.address.edit'));
        $response->assertSessionHas('success');
    }

    public function test_biodata_validation_rejects_missing_mandatory_fields(): void
    {
        [$user] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->post(route('applicant.biodata.update'), [
            'full_name' => '',
            'gender' => '',
            'birth_place' => '',
            'birth_date' => '',
            'child_order' => '',
            'siblings_count' => '',
        ]);

        $response->assertSessionHasErrors([
            'full_name',
            'gender',
            'birth_place',
            'birth_date',
            'child_order',
            'siblings_count',
        ]);
    }

    public function test_biodata_validation_rejects_invalid_gender(): void
    {
        [$user] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->post(route('applicant.biodata.update'), [
            'full_name' => 'Testing Name',
            'gender' => 'X',
            'birth_place' => 'Madiun',
            'birth_date' => '2019-01-01',
            'child_order' => 1,
            'siblings_count' => 1,
        ]);

        $response->assertSessionHasErrors(['gender']);
    }

    public function test_biodata_update_ignores_tampered_nik_field(): void
    {
        $originalNik = '3573010101190001';
        [$user, $applicant] = $this->createApplicantUser(['nik' => $originalNik]);

        $this->actingAs($user, 'web')->post(route('applicant.biodata.update'), [
            'nik' => '3573999999999999', // Tampered input
            'full_name' => 'Updated Name',
            'gender' => 'L',
            'birth_place' => 'Madiun',
            'birth_date' => '2019-05-15',
            'child_order' => 1,
            'siblings_count' => 2,
        ]);

        $user->refresh();
        $this->assertSame($originalNik, $user->getDecryptedNik());
        $this->assertSame(NikManager::hash($originalNik), $user->nik_lookup_hash);
    }

    public function test_cannot_update_another_applicants_biodata(): void
    {
        [$userA, $applicantA] = $this->createApplicantUser(['nik' => '3573010101190001']);
        [$userB, $applicantB] = $this->createApplicantUser(['nik' => '3573010101190002', 'phone' => '081299999999']);

        // User A attempts to send an applicant_id for User B
        $this->actingAs($userA, 'web')->post(route('applicant.biodata.update'), [
            'applicant_id' => $applicantB->id,
            'full_name' => 'Hacked Name',
            'gender' => 'L',
            'birth_place' => 'Madiun',
            'birth_date' => '2019-01-01',
            'child_order' => 1,
            'siblings_count' => 1,
        ]);

        $applicantA->refresh();
        $applicantB->refresh();

        // User A's name is updated, but User B remains untouched
        $this->assertSame('Hacked Name', $applicantA->full_name);
        $this->assertNotSame('Hacked Name', $applicantB->full_name);
    }
}
