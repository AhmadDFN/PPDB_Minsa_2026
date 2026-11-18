<?php

declare(strict_types=1);

namespace App\Http\Requests\Applicant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isApplicant() ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'consent' => ['required', 'accepted'],
            'consent_name' => ['required', 'string', 'min:2', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'consent.required' => 'Persetujuan orang tua/wali wajib dicentang sebelum mengirim pendaftaran.',
            'consent.accepted' => 'Persetujuan orang tua/wali wajib dicentang sebelum mengirim pendaftaran.',
            'consent_name.required' => 'Nama orang tua/wali yang menyetujui wajib diisi.',
            'consent_name.min' => 'Nama orang tua/wali minimal 2 karakter.',
            'consent_name.max' => 'Nama orang tua/wali maksimal 255 karakter.',
        ];
    }
}
