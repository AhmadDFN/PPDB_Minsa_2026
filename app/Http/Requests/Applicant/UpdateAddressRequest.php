<?php

declare(strict_types=1);

namespace App\Http\Requests\Applicant;

use App\Support\AdministrativeRegionRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $address = $this->user()?->applicant?->address;
        if (! $address) {
            return;
        }

        $stored = [
            'address_line' => $address->address_line,
            'rt' => $address->rt,
            'rw' => $address->rw,
            'kelurahan' => $address->kelurahan,
            'kecamatan' => $address->kecamatan,
            'city' => $address->city,
            'province' => $address->province,
            'postal_code' => $address->postal_code,
            'distance_to_school_km' => $address->distance_to_school_km,
        ];
        $this->merge(array_filter(array_diff_key($stored, $this->all()), static fn (mixed $value): bool => $value !== null));
    }

    public function authorize(): bool
    {
        return $this->user() && $this->user()->isApplicant();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'address_line' => ['required', 'string', 'min:5', 'max:500'],
            'rt' => ['required', 'string', 'max:5'],
            'rw' => ['required', 'string', 'max:5'],
            'kelurahan' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'digits:5'],
            'distance_to_school_km' => ['required_without:latitude', 'nullable', 'numeric', 'min:0', 'max:999.99'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['required_with:latitude', 'nullable', 'numeric', 'between:-180,180'],
            'province_code' => ['nullable', 'string', 'max:10'],
            'city_code' => ['nullable', 'string', 'max:15'],
            'district_code' => ['nullable', 'string', 'max:20'],
            'village_code' => ['nullable', 'string', 'max:25'],
        ];

        return array_merge($rules, AdministrativeRegionRules::for($this));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'address_line.required' => 'Alamat jalan / dusun domisili tempat tinggal wajib diisi.',
            'address_line.min' => 'Alamat domisili minimal 5 karakter.',
            'kelurahan.required' => 'Kelurahan / Desa domisili wajib diisi.',
            'kecamatan.required' => 'Kecamatan domisili wajib diisi.',
            'city.required' => 'Kota / Kabupaten domisili wajib diisi.',
            'province.required' => 'Provinsi domisili wajib diisi.',
            'postal_code.max' => 'Kode pos maksimal 10 karakter.',
            'distance_to_school_km.numeric' => 'Estimasi jarak ke madrasah harus berupa angka (kilometer).',
            'postal_code.required' => 'Kode pos wajib diisi.',
            'postal_code.digits' => 'Kode pos harus terdiri dari 5 angka.',
            'latitude.between' => 'Titik lintang lokasi tidak valid.',
            'longitude.between' => 'Titik bujur lokasi tidak valid.',
        ];
    }
}
