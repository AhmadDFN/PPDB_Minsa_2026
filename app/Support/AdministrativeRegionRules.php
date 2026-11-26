<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdministrativeRegionRules
{
    /** @return array<string, array<int, mixed>> */
    public static function for(FormRequest $request, string $prefix = ''): array
    {
        $province = $prefix.'province_code';
        $city = $prefix.'city_code';
        $district = $prefix.'district_code';
        $village = $prefix.'village_code';

        return [
            $province => ['nullable', 'string', Rule::exists('administrative_regions', 'code')->where(fn ($query) => $query->where('level', 1))],
            $city => ['nullable', 'string', 'required_with:'.$province, Rule::exists('administrative_regions', 'code')->where(fn ($query) => $query->where('level', 2)->where('parent_code', $request->input($province)))],
            $district => ['nullable', 'string', 'required_with:'.$city, Rule::exists('administrative_regions', 'code')->where(fn ($query) => $query->where('level', 3)->where('parent_code', $request->input($city)))],
            $village => ['nullable', 'string', 'required_with:'.$district, Rule::exists('administrative_regions', 'code')->where(fn ($query) => $query->where('level', 4)->where('parent_code', $request->input($district)))],
        ];
    }
}
