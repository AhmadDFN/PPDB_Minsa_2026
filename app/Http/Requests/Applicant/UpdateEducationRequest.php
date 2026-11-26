<?php

declare(strict_types=1);

namespace App\Http\Requests\Applicant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEducationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $education = $this->user()?->applicant?->education;
        if (! $education) {
            return;
        }

        $stored = [
            'education_level' => $education->education_level?->value ?? $education->education_level,
            'school_name' => $education->school_name,
            'npsn' => $education->npsn,
            'city' => $education->city,
            'nisn_unavailable' => $education->nisn_unavailable,
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
        return [
            'education_level' => ['required', 'string', 'in:ra,tk,paud,other,none'],
            'school_name' => ['required', 'string', 'max:255'],
            'npsn' => ['required', 'string', 'regex:/^\d{8}$/'],
            'nisn_unavailable' => ['nullable', 'boolean'],
            'city' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'education_level.required' => 'Jenjang pendidikan anak sebelumnya wajib dipilih.',
            'education_level.in' => 'Pilihan jenjang pendidikan tidak valid.',
            'school_name.required' => 'Nama sekolah / lembaga asal wajib diisi.',
            'school_name.max' => 'Nama sekolah maksimal 255 karakter.',
            'npsn.required' => 'NPSN TK/RA wajib diisi.',
            'npsn.regex' => 'NPSN harus terdiri dari 8 digit angka.',
        ];
    }
}
