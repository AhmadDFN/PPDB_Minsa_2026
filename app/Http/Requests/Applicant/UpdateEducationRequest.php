<?php

declare(strict_types=1);

namespace App\Http\Requests\Applicant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEducationRequest extends FormRequest
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
            'education_level' => ['required', 'string', 'in:ra,tk,paud,other,none'],
            'school_name' => ['required_unless:education_level,none', 'nullable', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:10'],
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
            'school_name.required_unless' => 'Nama sekolah / lembaga asal wajib diisi jika anak pernah bersekolah TK/RA/PAUD.',
            'school_name.max' => 'Nama sekolah maksimal 255 karakter.',
            'npsn.max' => 'NPSN maksimal 10 karakter.',
        ];
    }
}
