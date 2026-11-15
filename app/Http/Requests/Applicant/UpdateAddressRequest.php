<?php

declare(strict_types=1);

namespace App\Http\Requests\Applicant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isApplicant();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address_line' => ['required', 'string', 'min:5', 'max:500'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['nullable', 'string', 'max:5'],
            'kelurahan' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'distance_to_school_km' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
        ];
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
        ];
    }
}
