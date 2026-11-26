<?php

declare(strict_types=1);

namespace Tests\Feature\Applicant;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\AdministrativeRegion;
use App\Models\AdmissionPeriod;
use App\Models\Applicant;
use App\Models\User;
use App\Support\NikManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrativeRegionTest extends TestCase
{
    use RefreshDatabase;

    public function test_applicant_can_load_only_children_of_selected_region(): void
    {
        [$user] = $this->createApplicant();
        AdministrativeRegion::query()->create(['code' => '35', 'level' => 1, 'name' => 'Jawa Timur']);
        AdministrativeRegion::query()->create(['code' => '3577', 'parent_code' => '35', 'level' => 2, 'name' => 'Kota Madiun']);
        AdministrativeRegion::query()->create(['code' => '33', 'level' => 1, 'name' => 'Jawa Tengah']);
        AdministrativeRegion::query()->create(['code' => '3375', 'parent_code' => '33', 'level' => 2, 'name' => 'Kota Surakarta']);

        $this->actingAs($user)
            ->getJson(route('applicant.regions.index', ['parent' => '35']))
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.code', '3577');
    }

    public function test_region_endpoint_rejects_unknown_parent_code(): void
    {
        [$user] = $this->createApplicant();

        $this->actingAs($user)
            ->getJson(route('applicant.regions.index', ['parent' => '9999']))
            ->assertNotFound();
    }

    public function test_address_rejects_a_city_code_from_another_province(): void
    {
        [$user] = $this->createApplicant();
        AdministrativeRegion::query()->create(['code' => '35', 'level' => 1, 'name' => 'Jawa Timur']);
        AdministrativeRegion::query()->create(['code' => '33', 'level' => 1, 'name' => 'Jawa Tengah']);
        AdministrativeRegion::query()->create(['code' => '3375', 'parent_code' => '33', 'level' => 2, 'name' => 'Kota Surakarta']);

        $this->actingAs($user)
            ->post(route('applicant.address.update'), [
                'address_line' => 'Jalan Melati Nomor 1',
                'rt' => '001',
                'rw' => '002',
                'province' => 'Jawa Timur',
                'province_code' => '35',
                'city' => 'Kota Surakarta',
                'city_code' => '3375',
                'kecamatan' => 'Laweyan',
                'kelurahan' => 'Sondakan',
                'postal_code' => '57147',
                'distance_to_school_km' => '2.5',
            ])
            ->assertSessionHasErrors('city_code');
    }

    public function test_address_persists_catalog_names_instead_of_submitted_region_labels(): void
    {
        [$user] = $this->createApplicant();
        $applicant = $user->applicant;
        AdministrativeRegion::query()->create(['code' => '35', 'level' => 1, 'name' => 'Jawa Timur']);
        AdministrativeRegion::query()->create(['code' => '3577', 'parent_code' => '35', 'level' => 2, 'name' => 'Kota Madiun']);
        AdministrativeRegion::query()->create(['code' => '357703', 'parent_code' => '3577', 'level' => 3, 'name' => 'Taman']);
        AdministrativeRegion::query()->create(['code' => '3577031008', 'parent_code' => '357703', 'level' => 4, 'name' => 'Demangan']);

        $this->actingAs($user)->post(route('applicant.address.update'), [
            'address_line' => 'Jalan Melati Nomor 1',
            'rt' => '001',
            'rw' => '002',
            'province' => 'Nama Palsu',
            'province_code' => '35',
            'city' => 'Nama Palsu',
            'city_code' => '3577',
            'kecamatan' => 'Nama Palsu',
            'district_code' => '357703',
            'kelurahan' => 'Nama Palsu',
            'village_code' => '3577031008',
            'postal_code' => '63136',
            'distance_to_school_km' => '2.5',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('applicant_addresses', [
            'applicant_id' => $applicant->id,
            'province' => 'Jawa Timur',
            'city' => 'Kota Madiun',
            'kecamatan' => 'Taman',
            'kelurahan' => 'Demangan',
            'province_code' => '35',
            'city_code' => '3577',
            'district_code' => '357703',
            'village_code' => '3577031008',
        ]);
    }

    private function createApplicant(): array
    {
        $nik = '3573010101190001';
        $user = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'nik_lookup_hash' => NikManager::hash($nik),
            'nik_encrypted' => NikManager::encrypt($nik),
        ]);
        AdmissionPeriod::factory()->active()->create();
        Applicant::factory()->create(['user_id' => $user->id]);

        return [$user];
    }
}
