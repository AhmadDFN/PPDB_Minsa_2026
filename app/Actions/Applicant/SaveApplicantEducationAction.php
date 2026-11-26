<?php

declare(strict_types=1);

namespace App\Actions\Applicant;

use App\Enums\PreviousEducationLevel;
use App\Models\Applicant;
use App\Models\ApplicantEducation;

class SaveApplicantEducationAction
{
    /**
     * Save or update previous education history.
     *
     * @param  array{
     *     education_level: string|PreviousEducationLevel,
     *     school_name?: ?string,
     *     npsn?: ?string,
     *     city?: ?string
     * }  $data
     */
    public function execute(Applicant $applicant, array $data): ApplicantEducation
    {
        $level = $data['education_level'] instanceof PreviousEducationLevel
            ? $data['education_level']->value
            : $data['education_level'];

        $isNone = $level === PreviousEducationLevel::NONE->value;

        return ApplicantEducation::updateOrCreate(
            ['applicant_id' => $applicant->id],
            [
                'education_level' => $level,
                'school_name' => ! empty($data['school_name']) ? trim($data['school_name']) : null,
                'npsn' => ! empty($data['npsn']) ? trim($data['npsn']) : null,
                'city' => $isNone ? null : (! empty($data['city']) ? trim($data['city']) : null),
                'nisn_unavailable' => (bool) ($data['nisn_unavailable'] ?? false),
            ]
        );
    }
}
