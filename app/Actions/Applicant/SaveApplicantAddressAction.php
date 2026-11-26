<?php

declare(strict_types=1);

namespace App\Actions\Applicant;

use App\Models\AdministrativeRegion;
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
        $latitude = isset($data['latitude']) ? (float) $data['latitude'] : null;
        $longitude = isset($data['longitude']) ? (float) $data['longitude'] : null;
        $distance = isset($data['distance_to_school_km']) && $data['distance_to_school_km'] !== ''
            ? (float) $data['distance_to_school_km']
            : null;

        if ($latitude !== null && $longitude !== null) {
            $distance = $this->distanceToCampusOne($latitude, $longitude);
        }

        $regionNames = AdministrativeRegion::query()
            ->whereIn('code', array_filter([
                $data['province_code'] ?? null,
                $data['city_code'] ?? null,
                $data['district_code'] ?? null,
                $data['village_code'] ?? null,
            ]))
            ->pluck('name', 'code');

        return ApplicantAddress::updateOrCreate(
            ['applicant_id' => $applicant->id],
            [
                'address_line' => trim($data['address_line']),
                'rt' => ! empty($data['rt']) ? trim((string) $data['rt']) : null,
                'rw' => ! empty($data['rw']) ? trim((string) $data['rw']) : null,
                'kelurahan' => $regionNames[$data['village_code'] ?? ''] ?? trim($data['kelurahan']),
                'kecamatan' => $regionNames[$data['district_code'] ?? ''] ?? trim($data['kecamatan']),
                'city' => $regionNames[$data['city_code'] ?? ''] ?? trim($data['city']),
                'province' => $regionNames[$data['province_code'] ?? ''] ?? trim($data['province']),
                'postal_code' => ! empty($data['postal_code']) ? trim((string) $data['postal_code']) : null,
                'distance_to_school_km' => $distance,
                'province_code' => $data['province_code'] ?? null,
                'city_code' => $data['city_code'] ?? null,
                'district_code' => $data['district_code'] ?? null,
                'village_code' => $data['village_code'] ?? null,
            ]
        );
    }

    private function distanceToCampusOne(float $latitude, float $longitude): float
    {
        $schoolLatitude = config('ppdb.school.campus_one.latitude');
        $schoolLongitude = config('ppdb.school.campus_one.longitude');
        if (! is_numeric($schoolLatitude) || ! is_numeric($schoolLongitude)) {
            throw new \RuntimeException('Koordinat Kampus 1 belum dikonfigurasi.');
        }
        $schoolLatitude = (float) $schoolLatitude;
        $schoolLongitude = (float) $schoolLongitude;
        $earthRadiusKm = 6371.0088;
        $latDelta = deg2rad($schoolLatitude - $latitude);
        $lonDelta = deg2rad($schoolLongitude - $longitude);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($latitude)) * cos(deg2rad($schoolLatitude)) * sin($lonDelta / 2) ** 2;

        return round($earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }
}
