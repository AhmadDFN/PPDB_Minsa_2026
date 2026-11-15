<?php

declare(strict_types=1);

namespace App\Actions\Applicant;

use App\Enums\Gender;
use App\Models\Applicant;

class UpdateApplicantBiodataAction
{
    /**
     * Update applicant personal identity.
     *
     * @param  array{
     *     full_name: string,
     *     gender: string|Gender,
     *     birth_place: string,
     *     birth_date: string,
     *     nisn?: ?string,
     *     child_order: int,
     *     siblings_count: int
     * }  $data
     */
    public function execute(Applicant $applicant, array $data): Applicant
    {
        $gender = $data['gender'] instanceof Gender ? $data['gender']->value : $data['gender'];

        $applicant->update([
            'full_name' => trim($data['full_name']),
            'gender' => $gender,
            'birth_place' => trim($data['birth_place']),
            'birth_date' => $data['birth_date'],
            'nisn' => ! empty($data['nisn']) ? trim($data['nisn']) : null,
            'child_order' => (int) $data['child_order'],
            'siblings_count' => (int) $data['siblings_count'],
        ]);

        // Keep User.name synchronized
        $applicant->user->update([
            'name' => trim($data['full_name']),
        ]);

        return $applicant->fresh();
    }
}
