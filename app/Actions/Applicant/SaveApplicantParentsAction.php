<?php

declare(strict_types=1);

namespace App\Actions\Applicant;

use App\Enums\GuardianRelationship;
use App\Models\Applicant;
use App\Models\ApplicantGuardian;
use Illuminate\Support\Facades\DB;

class SaveApplicantParentsAction
{
    /**
     * Atomically save or update both mandatory parents (Father & Mother) and optional Guardian.
     */
    public function execute(Applicant $applicant, array $data): void
    {
        DB::transaction(function () use ($applicant, $data) {
            // 1. Father (Mandatory)
            ApplicantGuardian::updateOrCreate(
                [
                    'applicant_id' => $applicant->id,
                    'relationship_type' => GuardianRelationship::FATHER->value,
                ],
                [
                    'name' => trim($data['father_name']),
                    'nik' => ! empty($data['father_nik']) ? trim($data['father_nik']) : null,
                    'birth_place' => ! empty($data['father_birth_place']) ? trim($data['father_birth_place']) : null,
                    'birth_date' => ! empty($data['father_birth_date']) ? $data['father_birth_date'] : null,
                    'is_alive' => (bool) $data['father_is_alive'],
                    'education' => ! empty($data['father_education']) ? trim($data['father_education']) : null,
                    'occupation' => trim($data['father_occupation']),
                    'monthly_income' => ! empty($data['father_monthly_income']) ? trim($data['father_monthly_income']) : null,
                    'phone' => ! empty($data['father_phone']) ? trim($data['father_phone']) : null,
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
                    'nik' => ! empty($data['mother_nik']) ? trim($data['mother_nik']) : null,
                    'birth_place' => ! empty($data['mother_birth_place']) ? trim($data['mother_birth_place']) : null,
                    'birth_date' => ! empty($data['mother_birth_date']) ? $data['mother_birth_date'] : null,
                    'is_alive' => (bool) $data['mother_is_alive'],
                    'education' => ! empty($data['mother_education']) ? trim($data['mother_education']) : null,
                    'occupation' => trim($data['mother_occupation']),
                    'monthly_income' => ! empty($data['mother_monthly_income']) ? trim($data['mother_monthly_income']) : null,
                    'phone' => ! empty($data['mother_phone']) ? trim($data['mother_phone']) : null,
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
                        'nik' => ! empty($data['guardian_nik']) ? trim($data['guardian_nik']) : null,
                        'birth_place' => ! empty($data['guardian_birth_place']) ? trim($data['guardian_birth_place']) : null,
                        'birth_date' => ! empty($data['guardian_birth_date']) ? $data['guardian_birth_date'] : null,
                        'is_alive' => true,
                        'education' => ! empty($data['guardian_education']) ? trim($data['guardian_education']) : null,
                        'occupation' => ! empty($data['guardian_occupation']) ? trim($data['guardian_occupation']) : null,
                        'monthly_income' => ! empty($data['guardian_monthly_income']) ? trim($data['guardian_monthly_income']) : null,
                        'phone' => ! empty($data['guardian_phone']) ? trim($data['guardian_phone']) : null,
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
