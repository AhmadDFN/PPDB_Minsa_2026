<?php

declare(strict_types=1);

namespace App\Http\Requests\Applicant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SelectProgramRequest extends FormRequest
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
            'admission_period_program_id' => [
                'required',
                'integer',
                'exists:admission_period_programs,id',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'admission_period_program_id.required' => 'Pilihan program pendidikan (Unggulan atau Reguler) wajib dipilih.',
            'admission_period_program_id.exists' => 'Program pendidikan yang dipilih tidak tersedia pada periode pendaftaran ini.',
        ];
    }
}
