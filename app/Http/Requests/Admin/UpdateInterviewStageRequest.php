<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInterviewStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('interview.manage_master')
            || $this->user()?->can('manage_interview_settings')
            || ($this->user()?->hasRole('superadmin') ?? false);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul tahap wawancara wajib diisi.',
            'title.max' => 'Judul tahap wawancara maksimal 150 karakter.',
            'description.max' => 'Deskripsi panduan maksimal 1000 karakter.',
        ];
    }
}
