<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OpenRegistrationReopenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reopen_registration') ?? false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
            'expires_at' => ['required', 'date', 'after:now'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan pembukaan akses wajib diisi.',
            'reason.max' => 'Alasan pembukaan akses maksimal 1000 karakter.',
            'expires_at.required' => 'Waktu kedaluwarsa wajib diisi.',
            'expires_at.date' => 'Waktu kedaluwarsa harus berupa tanggal dan waktu yang valid.',
            'expires_at.after' => 'Waktu kedaluwarsa harus berada di masa depan.',
        ];
    }
}
