<?php

declare(strict_types=1);

namespace App\Actions\Applicant;

use App\Enums\GuardianRelationship;
use App\Models\AdministrativeRegion;
use App\Models\Applicant;
use App\Models\ApplicantGuardian;
use App\Support\NikManager;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SaveApplicantParentsAction
{
    /**
     * Atomically save or update both mandatory parents (Father & Mother) and optional Guardian.
     */
    public function execute(Applicant $applicant, array $data): void
    {
        $regionCodes = [];
        foreach (['father', 'mother', 'guardian'] as $prefix) {
            foreach (['province', 'city', 'district', 'village'] as $level) {
                $regionCodes[$prefix.'_'.$level.'_code'] = $data[$prefix.'_'.$level.'_code'] ?? null;
            }
        }
        $regionNames = AdministrativeRegion::query()
            ->whereIn('code', array_filter($regionCodes))
            ->pluck('name', 'code');

        DB::transaction(function () use ($applicant, $data, $regionNames) {
            // 1. Father (Mandatory)
            ApplicantGuardian::updateOrCreate(
                [
                    'applicant_id' => $applicant->id,
                    'relationship_type' => GuardianRelationship::FATHER->value,
                ],
                [
                    'name' => trim($data['father_name']),
                    'nik' => null,
                    'nik_encrypted' => Crypt::encryptString(NikManager::normalize($data['father_nik'])),
                    'nik_lookup_hash' => NikManager::hash($data['father_nik']),
                    'birth_place' => ! empty($data['father_birth_place']) ? trim($data['father_birth_place']) : null,
                    'birth_date' => ! empty($data['father_birth_date']) ? $data['father_birth_date'] : null,
                    'status_label' => $data['father_status_label'],
                    'is_alive' => $data['father_status_label'] === 'Masih Hidup',
                    'education' => ! empty($data['father_education']) ? trim($data['father_education']) : null,
                    'occupation' => trim($data['father_occupation']),
                    'monthly_income' => ! empty($data['father_monthly_income']) ? trim($data['father_monthly_income']) : null,
                    'phone' => ! empty($data['father_phone']) ? trim($data['father_phone']) : null,
                    'residence' => trim($data['father_residence']),
                    'address_line' => trim($data['father_address_line']),
                    'rt' => trim($data['father_rt']),
                    'rw' => trim($data['father_rw']),
                    'province' => $regionNames[$data['father_province_code'] ?? ''] ?? trim($data['father_province']),
                    'province_code' => $data['father_province_code'] ?? null,
                    'city' => $regionNames[$data['father_city_code'] ?? ''] ?? trim($data['father_city']),
                    'city_code' => $data['father_city_code'] ?? null,
                    'kecamatan' => $regionNames[$data['father_district_code'] ?? ''] ?? trim($data['father_kecamatan']),
                    'district_code' => $data['father_district_code'] ?? null,
                    'kelurahan' => $regionNames[$data['father_village_code'] ?? ''] ?? trim($data['father_kelurahan']),
                    'village_code' => $data['father_village_code'] ?? null,
                    'postal_code' => trim($data['father_postal_code']),
                    'residence_status' => $data['father_residence_status'],
                ]
            );

            // 2. Mother (Mandatory)
            ApplicantGuardian::updateOrCreate(
                [
                    'applicant_id' => $applicant->id,
                    'relationship_type' => GuardianRelationship::MOTHER->value,
                ],
                [
                    'name' => trim($data['mother_name']),
                    'nik' => null,
                    'nik_encrypted' => Crypt::encryptString(NikManager::normalize($data['mother_nik'])),
                    'nik_lookup_hash' => NikManager::hash($data['mother_nik']),
                    'birth_place' => ! empty($data['mother_birth_place']) ? trim($data['mother_birth_place']) : null,
                    'birth_date' => ! empty($data['mother_birth_date']) ? $data['mother_birth_date'] : null,
                    'status_label' => $data['mother_status_label'],
                    'is_alive' => $data['mother_status_label'] === 'Masih Hidup',
                    'education' => ! empty($data['mother_education']) ? trim($data['mother_education']) : null,
                    'occupation' => trim($data['mother_occupation']),
                    'monthly_income' => ! empty($data['mother_monthly_income']) ? trim($data['mother_monthly_income']) : null,
                    'phone' => ! empty($data['mother_phone']) ? trim($data['mother_phone']) : null,
                    'residence' => trim($data['mother_residence']),
                    'address_line' => trim($data['mother_address_line']),
                    'rt' => trim($data['mother_rt']),
                    'rw' => trim($data['mother_rw']),
                    'province' => $regionNames[$data['mother_province_code'] ?? ''] ?? trim($data['mother_province']),
                    'province_code' => $data['mother_province_code'] ?? null,
                    'city' => $regionNames[$data['mother_city_code'] ?? ''] ?? trim($data['mother_city']),
                    'city_code' => $data['mother_city_code'] ?? null,
                    'kecamatan' => $regionNames[$data['mother_district_code'] ?? ''] ?? trim($data['mother_kecamatan']),
                    'district_code' => $data['mother_district_code'] ?? null,
                    'kelurahan' => $regionNames[$data['mother_village_code'] ?? ''] ?? trim($data['mother_kelurahan']),
                    'village_code' => $data['mother_village_code'] ?? null,
                    'postal_code' => trim($data['mother_postal_code']),
                    'residence_status' => $data['mother_residence_status'],
                ]
            );

            // 3. Guardian (Optional)
            $hasGuardian = ! empty($data['has_guardian']) && ! empty($data['guardian_name']);
            if ($hasGuardian) {
                ApplicantGuardian::updateOrCreate(
                    [
                        'applicant_id' => $applicant->id,
                        'relationship_type' => GuardianRelationship::GUARDIAN->value,
                    ],
                    [
                        'name' => trim($data['guardian_name']),
                        'nik' => null,
                        'nik_encrypted' => ! empty($data['guardian_nik']) ? Crypt::encryptString(NikManager::normalize($data['guardian_nik'])) : null,
                        'nik_lookup_hash' => ! empty($data['guardian_nik']) ? NikManager::hash($data['guardian_nik']) : null,
                        'birth_place' => ! empty($data['guardian_birth_place']) ? trim($data['guardian_birth_place']) : null,
                        'birth_date' => ! empty($data['guardian_birth_date']) ? $data['guardian_birth_date'] : null,
                        'status_label' => $data['guardian_status_label'] ?? null,
                        'is_alive' => ($data['guardian_status_label'] ?? 'Masih Hidup') === 'Masih Hidup',
                        'education' => ! empty($data['guardian_education']) ? trim($data['guardian_education']) : null,
                        'occupation' => ! empty($data['guardian_occupation']) ? trim($data['guardian_occupation']) : null,
                        'monthly_income' => ! empty($data['guardian_monthly_income']) ? trim($data['guardian_monthly_income']) : null,
                        'phone' => ! empty($data['guardian_phone']) ? trim($data['guardian_phone']) : null,
                        'residence' => isset($data['guardian_residence']) ? trim($data['guardian_residence']) : null,
                        'address_line' => isset($data['guardian_address_line']) ? trim($data['guardian_address_line']) : null,
                        'rt' => isset($data['guardian_rt']) ? trim($data['guardian_rt']) : null,
                        'rw' => isset($data['guardian_rw']) ? trim($data['guardian_rw']) : null,
                        'province' => $regionNames[$data['guardian_province_code'] ?? ''] ?? (isset($data['guardian_province']) ? trim($data['guardian_province']) : null),
                        'province_code' => $data['guardian_province_code'] ?? null,
                        'city' => $regionNames[$data['guardian_city_code'] ?? ''] ?? (isset($data['guardian_city']) ? trim($data['guardian_city']) : null),
                        'city_code' => $data['guardian_city_code'] ?? null,
                        'kecamatan' => $regionNames[$data['guardian_district_code'] ?? ''] ?? (isset($data['guardian_kecamatan']) ? trim($data['guardian_kecamatan']) : null),
                        'district_code' => $data['guardian_district_code'] ?? null,
                        'kelurahan' => $regionNames[$data['guardian_village_code'] ?? ''] ?? (isset($data['guardian_kelurahan']) ? trim($data['guardian_kelurahan']) : null),
                        'village_code' => $data['guardian_village_code'] ?? null,
                        'postal_code' => isset($data['guardian_postal_code']) ? trim($data['guardian_postal_code']) : null,
                        'residence_status' => $data['guardian_residence_status'] ?? null,
                    ]
                );
            } else {
                // If user unchecks guardian, remove existing guardian record if present
                ApplicantGuardian::where('applicant_id', $applicant->id)
                    ->where('relationship_type', GuardianRelationship::GUARDIAN->value)
                    ->delete();
            }
        });
    }
}
