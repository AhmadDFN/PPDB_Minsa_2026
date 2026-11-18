<?php

declare(strict_types=1);

namespace App\Http\Requests\Applicant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateParentsRequest extends FormRequest
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
            // Data Ayah Kandung (Wajib)
            'father_name' => ['required', 'string', 'min:2', 'max:255'],
            'father_nik' => ['required', 'string', 'regex:/^\d{16}$/'],
            'father_birth_place' => ['nullable', 'string', 'max:100'],
            'father_birth_date' => ['nullable', 'date'],
            'father_is_alive' => ['required', 'boolean'],
            'father_education' => ['required', 'string', 'max:50'],
            'father_occupation' => ['required', 'string', 'max:100'],
            'father_monthly_income' => ['required', 'string', 'max:50'],
            'father_phone' => ['required', 'string', 'max:25'],

            // Data Ibu Kandung (Wajib)
            'mother_name' => ['required', 'string', 'min:2', 'max:255'],
            'mother_nik' => ['required', 'string', 'regex:/^\d{16}$/'],
            'mother_birth_place' => ['nullable', 'string', 'max:100'],
            'mother_birth_date' => ['nullable', 'date'],
            'mother_is_alive' => ['required', 'boolean'],
            'mother_education' => ['required', 'string', 'max:50'],
            'mother_occupation' => ['required', 'string', 'max:100'],
            'mother_monthly_income' => ['required', 'string', 'max:50'],
            'mother_phone' => ['required', 'string', 'max:25'],

            // Data Wali (Opsional)
            'has_guardian' => ['nullable', 'boolean'],
            'guardian_name' => ['required_if:has_guardian,1', 'nullable', 'string', 'min:2', 'max:255'],
            'guardian_nik' => ['nullable', 'string', 'regex:/^\d{16}$/'],
            'guardian_birth_place' => ['nullable', 'string', 'max:100'],
            'guardian_birth_date' => ['nullable', 'date'],
            'guardian_education' => ['nullable', 'string', 'max:50'],
            'guardian_occupation' => ['nullable', 'string', 'max:100'],
            'guardian_monthly_income' => ['nullable', 'string', 'max:50'],
            'guardian_phone' => ['nullable', 'string', 'max:25'],
        ];
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
