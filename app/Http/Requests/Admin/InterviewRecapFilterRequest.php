<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InterviewRecapFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('view_interview_recaps');
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'batch_id' => ['nullable', 'integer', 'exists:interview_batches,id'],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'completion_status' => ['nullable', 'string', 'in:all,completed,incomplete'],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'batch_id.exists' => 'Jadwal gelombang wawancara yang dipilih tidak ditemukan.',
            'program_id.exists' => 'Program yang dipilih tidak ditemukan.',
            'completion_status.in' => 'Status kelengkapan wawancara tidak valid.',
            'search.max' => 'Kata kunci pencarian maksimal 100 karakter.',
        ];
    }
}
