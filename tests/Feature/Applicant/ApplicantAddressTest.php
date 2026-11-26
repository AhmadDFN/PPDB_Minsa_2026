<?php

declare(strict_types=1);

namespace Tests\Feature\Applicant;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\AdmissionPeriod;
use App\Models\Applicant;
use App\Models\ApplicantAddress;
use App\Models\User;
use App\Support\NikManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantAddressTest extends TestCase
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

    public function test_guest_cannot_view_address_form(): void
    {
        $response = $this->get(route('applicant.address.edit'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_applicant_can_view_address_form(): void
    {
        [$user] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->get(route('applicant.address.edit'));

        $response->assertStatus(200);
        $response->assertSee('Alamat Domisili Tempat Tinggal');
        $response->assertSee('Kota Madiun');
        $response->assertSee('offline-campus-map');
        $response->assertSee('Gunakan Lokasi Perangkat');
        $response->assertSee('OpenStreetMap contributors');
        $this->assertFileExists(public_path('maps/min1-madiun-campus.json'));
    }

    public function test_applicant_can_create_and_update_address(): void
    {
        [$user, $applicant] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->post(route('applicant.address.update'), [
            'address_line' => 'Jl. Pahlawan No. 12',
            'rt' => '003',
            'rw' => '002',
            'kelurahan' => 'Kartoharjo',
            'kecamatan' => 'Kartoharjo',
            'city' => 'Kota Madiun',
            'province' => 'Jawa Timur',
            'postal_code' => '63117',
            'distance_to_school_km' => 1.5,
            'nav_action' => 'save',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('applicant_addresses', [
            'applicant_id' => $applicant->id,
            'address_line' => 'Jl. Pahlawan No. 12',
            'rt' => '003',
            'rw' => '002',
            'kelurahan' => 'Kartoharjo',
            'kecamatan' => 'Kartoharjo',
            'city' => 'Kota Madiun',
            'province' => 'Jawa Timur',
            'postal_code' => '63117',
        ]);

        $address = $applicant->fresh()->address;
        $this->assertSame('003', $address->rt); // preserves leading zero
        $this->assertSame('002', $address->rw); // preserves leading zero

        // Second update does not duplicate rows
        $this->actingAs($user, 'web')->post(route('applicant.address.update'), [
            'address_line' => 'Jl. Pahlawan No. 99',
            'rt' => '005',
            'rw' => '001',
            'kelurahan' => 'Madiun Lor',
            'kecamatan' => 'Manguharjo',
            'city' => 'Kota Madiun',
            'province' => 'Jawa Timur',
            'postal_code' => '63122',
            'distance_to_school_km' => 2.0,
            'nav_action' => 'save',
        ]);

        $this->assertSame(1, ApplicantAddress::where('applicant_id', $applicant->id)->count());
        $this->assertSame('Jl. Pahlawan No. 99', $applicant->fresh()->address->address_line);
    }

    public function test_applicant_can_save_address_and_continue_to_parents_step(): void
    {
        [$user, $applicant] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->post(route('applicant.address.update'), [
            'address_line' => 'Jl. Panglima Sudirman No. 8',
            'rt' => '01',
            'rw' => '04',
            'kelurahan' => 'Pangongangan',
            'kecamatan' => 'Manguharjo',
            'city' => 'Kota Madiun',
            'province' => 'Jawa Timur',
            'postal_code' => '63136',
            'distance_to_school_km' => 2.5,
            'nav_action' => 'next',
        ]);

        $response->assertRedirect(route('applicant.parents.edit'));
        $response->assertSessionHas('success');
    }

    public function test_applicant_location_coordinates_calculate_distance_without_storing_coordinates(): void
    {
        [$user, $applicant] = $this->createApplicantUser();
        $campus = config('ppdb.school.campus_one');

        $response = $this->actingAs($user, 'web')->post(route('applicant.address.update'), [
            'address_line' => 'Jl. Sitinggil No. 3',
            'rt' => '003',
            'rw' => '002',
            'kelurahan' => 'Demangan',
            'kecamatan' => 'Taman',
            'city' => 'Kota Madiun',
            'province' => 'Jawa Timur',
            'postal_code' => '63136',
            'latitude' => $campus['latitude'],
            'longitude' => $campus['longitude'],
            'nav_action' => 'save',
        ]);

        $response->assertRedirect()->assertSessionHas('success');
        $address = $applicant->fresh()->address;
        $this->assertSame('0.00', $address->distance_to_school_km);
        $this->assertNull($address->getRawOriginal('latitude'));
        $this->assertNull($address->getRawOriginal('longitude'));
    }

    public function test_address_rejects_a_pin_without_both_coordinates(): void
    {
        [$user] = $this->createApplicantUser();

        $this->actingAs($user, 'web')->post(route('applicant.address.update'), [
            'address_line' => 'Jl. Sitinggil No. 3',
            'rt' => '003',
            'rw' => '002',
            'kelurahan' => 'Demangan',
            'kecamatan' => 'Taman',
            'city' => 'Kota Madiun',
            'province' => 'Jawa Timur',
            'postal_code' => '63136',
            'latitude' => '-7.6591',
            'distance_to_school_km' => '1.5',
            'nav_action' => 'save',
        ])->assertSessionHasErrors('longitude');
    }

    public function test_address_validation_rejects_missing_mandatory_fields(): void
    {
        [$user] = $this->createApplicantUser();

        $response = $this->actingAs($user, 'web')->post(route('applicant.address.update'), [
            'address_line' => '',
            'kelurahan' => '',
            'kecamatan' => '',
            'city' => '',
            'province' => '',
        ]);

        $response->assertSessionHasErrors([
            'address_line',
            'kelurahan',
            'kecamatan',
            'city',
            'province',
        ]);
    }
}
