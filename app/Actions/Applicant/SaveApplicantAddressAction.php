<?php

declare(strict_types=1);

namespace App\Actions\Applicant;

use App\Models\Applicant;
use App\Models\ApplicantAddress;

class SaveApplicantAddressAction
{
    /**
     * Save or update applicant residential address.
     *
     * @param  array{
     *     address_line: string,
     *     rt?: ?string,
     *     rw?: ?string,
     *     kelurahan: string,
     *     kecamatan: string,
     *     city: string,
     *     province: string,
     *     postal_code?: ?string,
     *     distance_to_school_km?: ?float
     * }  $data
     */
    public function execute(Applicant $applicant, array $data): ApplicantAddress
    {
        return ApplicantAddress::updateOrCreate(
            ['applicant_id' => $applicant->id],
            [
                'address_line' => trim($data['address_line']),
                'rt' => ! empty($data['rt']) ? trim((string) $data['rt']) : null,
                'rw' => ! empty($data['rw']) ? trim((string) $data['rw']) : null,
                'kelurahan' => trim($data['kelurahan']),
                'kecamatan' => trim($data['kecamatan']),
                'city' => trim($data['city']),
                'province' => trim($data['province']),
                'postal_code' => ! empty($data['postal_code']) ? trim((string) $data['postal_code']) : null,
                'distance_to_school_km' => isset($data['distance_to_school_km']) && $data['distance_to_school_km'] !== '' ? (float) $data['distance_to_school_km'] : null,
            ]
        );
    }
}
