<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInterviewCriterionRequest extends FormRequest
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
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Nama poin penilaian wajib diisi.',
            'title.max' => 'Nama poin penilaian maksimal 150 karakter.',
            'description.max' => 'Deskripsi indikator maksimal 1000 karakter.',
            'sort_order.integer' => 'Nomor urutan harus berupa angka.',
            'sort_order.min' => 'Nomor urutan minimal 1.',
        ];
    }
}
