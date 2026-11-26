<?php

declare(strict_types=1);

namespace App\Http\Requests\Applicant;

use App\Support\AdministrativeRegionRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateParentsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $applicant = $this->user()?->applicant;
        if (! $applicant) {
            return;
        }

        $stored = [];
        foreach (['father', 'mother', 'guardian'] as $prefix) {
            $record = $applicant->{$prefix};
            if (! $record) {
                continue;
            }

            foreach (['name', 'nik', 'birth_place', 'birth_date', 'education', 'occupation', 'monthly_income', 'phone', 'residence', 'address_line', 'rt', 'rw', 'province', 'city', 'kecamatan', 'kelurahan', 'postal_code', 'residence_status', 'province_code', 'city_code', 'district_code', 'village_code'] as $field) {
                $stored[$prefix.'_'.$field] = $record->{$field};
            }
            $stored[$prefix.'_status_label'] = $record->status_label
                ?? ($record->is_alive ? 'Masih Hidup' : 'Sudah Meninggal');
        }

        $stored['has_guardian'] = $applicant->guardian !== null;
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
        $occupations = [
            'Tidak bekerja', 'Pensiunan', 'PNS', 'TNI/POLRI', 'Guru/Dosen', 'Wiraswasta',
            'Pegawai Swasta', 'Pengacara/Jaksa/Hakim/Notaris', 'Seniman/Pelukis/Artis',
            'Dokter/Bidan/Perawat', 'Pilot/Pramugara', 'Buruh(Tani/Pabrik,Bangunan)', 'Politikus', 'Lainnya',
        ];
        $education = ['SD/Sederajat', 'SMP/Sederajat', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3', 'Tidak Bersekolah', 'Lainnya'];
        $income = ['dibawah 800.000', '800.001 - 1.200.000', '1.200.001 - 1.800.000', '1.800.001 - 2.500.000', '2.500.001 - 3.500.000', '3.500.001 - 4.800.000', '4.800.001 - 6.500.000', '6.500.001 - 10.000.000', '10.000.001 - 20.000.000', 'diatas 20.000.001'];

        $rules = [
            // Data Ayah Kandung (Wajib)
            'father_name' => ['required', 'string', 'min:2', 'max:255'],
            'father_nik' => ['required', 'string', 'regex:/^\d{16}$/'],
            'father_birth_place' => ['required', 'string', 'max:100'],
            'father_birth_date' => ['required', 'date', 'before:today'],
            'father_status_label' => ['required', 'string', 'in:Masih Hidup,Sudah Meninggal,Tidak di ketahui'],
            'father_education' => ['required', 'string', Rule::in($education)],
            'father_occupation' => ['required', 'string', Rule::in($occupations)],
            'father_monthly_income' => ['required', 'string', Rule::in($income)],
            'father_phone' => ['required', 'string', 'max:25'],
            'father_residence' => ['required', 'string', 'max:255'],
            'father_address_line' => ['required', 'string', 'max:500'],
            'father_rt' => ['required', 'string', 'max:5'],
            'father_rw' => ['required', 'string', 'max:5'],
            'father_province' => ['required', 'string', 'max:100'],
            'father_city' => ['required', 'string', 'max:100'],
            'father_kecamatan' => ['required', 'string', 'max:100'],
            'father_kelurahan' => ['required', 'string', 'max:100'],
            'father_postal_code' => ['required', 'string', 'digits:5'],
            'father_residence_status' => ['required', 'string', 'in:Rumah Orangtua,Rumah Saudara/Kerabat,Rumah Dinas,Rumah Sewa/Kontrak,Lainnya'],

            // Data Ibu Kandung (Wajib)
            'mother_name' => ['required', 'string', 'min:2', 'max:255'],
            'mother_nik' => ['required', 'string', 'regex:/^\d{16}$/'],
            'mother_birth_place' => ['required', 'string', 'max:100'],
            'mother_birth_date' => ['required', 'date', 'before:today'],
            'mother_status_label' => ['required', 'string', 'in:Masih Hidup,Sudah Meninggal,Tidak di ketahui'],
            'mother_education' => ['required', 'string', Rule::in($education)],
            'mother_occupation' => ['required', 'string', Rule::in($occupations)],
            'mother_monthly_income' => ['required', 'string', Rule::in($income)],
            'mother_phone' => ['required', 'string', 'max:25'],
            'mother_residence' => ['required', 'string', 'max:255'],
            'mother_address_line' => ['required', 'string', 'max:500'],
            'mother_rt' => ['required', 'string', 'max:5'],
            'mother_rw' => ['required', 'string', 'max:5'],
            'mother_province' => ['required', 'string', 'max:100'],
            'mother_city' => ['required', 'string', 'max:100'],
            'mother_kecamatan' => ['required', 'string', 'max:100'],
            'mother_kelurahan' => ['required', 'string', 'max:100'],
            'mother_postal_code' => ['required', 'string', 'digits:5'],
            'mother_residence_status' => ['required', 'string', 'in:Rumah Orangtua,Rumah Saudara/Kerabat,Rumah Dinas,Rumah Sewa/Kontrak,Lainnya'],

            // Data Wali (Opsional)
            'has_guardian' => ['nullable', 'boolean'],
            'guardian_name' => ['required_if:has_guardian,1', 'nullable', 'string', 'min:2', 'max:255'],
            'guardian_nik' => ['nullable', 'string', 'regex:/^\d{16}$/'],
            'guardian_birth_place' => ['nullable', 'string', 'max:100'],
            'guardian_birth_date' => ['nullable', 'date', 'before:today'],
            'guardian_status_label' => ['nullable', 'string', 'in:Masih Hidup,Sudah Meninggal,Tidak di ketahui'],
            'guardian_education' => ['nullable', 'string', Rule::in($education)],
            'guardian_occupation' => ['nullable', 'string', Rule::in($occupations)],
            'guardian_monthly_income' => ['nullable', 'string', Rule::in($income)],
            'guardian_phone' => ['nullable', 'string', 'max:25'],
            'guardian_residence' => ['nullable', 'string', 'max:255'],
            'guardian_address_line' => ['nullable', 'string', 'max:500'],
            'guardian_rt' => ['nullable', 'string', 'max:5'],
            'guardian_rw' => ['nullable', 'string', 'max:5'],
            'guardian_province' => ['nullable', 'string', 'max:100'],
            'guardian_city' => ['nullable', 'string', 'max:100'],
            'guardian_kecamatan' => ['nullable', 'string', 'max:100'],
            'guardian_kelurahan' => ['nullable', 'string', 'max:100'],
            'guardian_postal_code' => ['nullable', 'string', 'digits:5'],
            'guardian_residence_status' => ['nullable', 'string', 'in:Rumah Orangtua,Rumah Saudara/Kerabat,Rumah Dinas,Rumah Sewa/Kontrak,Lainnya'],
        ];

        return array_merge(
            $rules,
            AdministrativeRegionRules::for($this, 'father_'),
            AdministrativeRegionRules::for($this, 'mother_'),
            AdministrativeRegionRules::for($this, 'guardian_'),
        );
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'father_name.required' => 'Nama ayah kandung wajib diisi.',
            'father_name.min' => 'Nama ayah minimal 2 karakter.',
            'father_nik.required' => 'NIK ayah wajib diisi.',
            'father_nik.regex' => 'NIK ayah harus terdiri dari 16 digit angka.',
            'father_education.required' => 'Pendidikan terakhir ayah wajib dipilih.',
            'father_occupation.required' => 'Pekerjaan ayah wajib dipilih / diisi.',
            'father_monthly_income.required' => 'Penghasilan ayah wajib dipilih.',
            'father_phone.required' => 'Nomor telepon ayah wajib diisi.',
            'mother_name.required' => 'Nama ibu kandung wajib diisi.',
            'mother_name.min' => 'Nama ibu minimal 2 karakter.',
            'mother_nik.required' => 'NIK ibu wajib diisi.',
            'mother_nik.regex' => 'NIK ibu harus terdiri dari 16 digit angka.',
            'mother_education.required' => 'Pendidikan terakhir ibu wajib dipilih.',
            'mother_occupation.required' => 'Pekerjaan ibu wajib dipilih / diisi.',
            'mother_monthly_income.required' => 'Penghasilan ibu wajib dipilih.',
            'mother_phone.required' => 'Nomor telepon ibu wajib diisi.',
            'guardian_name.required_if' => 'Nama wali wajib diisi jika opsi data wali dicentang.',
            'guardian_nik.regex' => 'NIK wali harus terdiri dari 16 digit angka.',
        ];
    }
}
