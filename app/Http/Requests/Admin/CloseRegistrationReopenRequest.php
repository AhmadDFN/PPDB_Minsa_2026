<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CloseRegistrationReopenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reopen_registration') ?? false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan penutupan akses wajib diisi.',
            'reason.max' => 'Alasan penutupan akses maksimal 1000 karakter.',
        ];
    }
}
