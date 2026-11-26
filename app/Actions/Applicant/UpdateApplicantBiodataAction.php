<?php

declare(strict_types=1);

namespace App\Actions\Applicant;

use App\Enums\Gender;
use App\Models\Applicant;
use App\Models\ApplicantEducation;
use Illuminate\Support\Facades\Crypt;

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
            'student_phone' => trim($data['student_phone']),
            'nisn' => ! empty($data['nisn_unavailable'])
                ? null
                : (! empty($data['nisn']) ? trim($data['nisn']) : null),
            'nisn_unavailable_reason' => ! empty($data['nisn_unavailable']) ? 'Belum memiliki NISN dari asal TK' : null,
            'family_card_number_encrypted' => Crypt::encryptString(trim($data['family_card_number'])),
            'religion' => $data['religion'],
            'hobby' => $data['hobby'],
            'hobby_other' => $data['hobby'] === 'Lainnya' ? trim((string) $data['hobby_other']) : null,
            'aspiration' => $data['aspiration'],
            'aspiration_other' => $data['aspiration'] === 'Lainnya' ? trim((string) $data['aspiration_other']) : null,
            'residence_status' => $data['residence_status'],
            'travel_time' => $data['travel_time'],
            'transportation' => $data['transportation'],
            'social_assistance_number_encrypted' => Crypt::encryptString(trim($data['social_assistance_number'])),
            'child_order' => (int) $data['child_order'],
            'siblings_count' => (int) $data['siblings_count'],
        ]);

        ApplicantEducation::query()->updateOrCreate(
            ['applicant_id' => $applicant->id],
            ['nisn_unavailable' => ! empty($data['nisn_unavailable'])],
        );

        // Keep User.name synchronized
        $applicant->user->update([
            'name' => trim($data['full_name']),
        ]);

        return $applicant->fresh();
    }
}
