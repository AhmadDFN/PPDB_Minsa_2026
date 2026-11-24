<?php

declare(strict_types=1);

namespace App\Http\Requests\Interview;

use Illuminate\Foundation\Http\FormRequest;

class LookupApplicantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hasAnyRole([
            'interviewer_stage_1',
            'interviewer_stage_2',
            'interviewer_stage_3',
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'identifier.required' => 'Nomor urut pendaftaran atau kode QR wajib diisi.',
            'identifier.string' => 'Format nomor pendaftaran atau kode QR tidak valid.',
            'identifier.max' => 'Panjang nomor pendaftaran atau kode QR maksimal 255 karakter.',
        ];
    }
}
