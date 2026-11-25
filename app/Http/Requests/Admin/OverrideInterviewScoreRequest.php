<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OverrideInterviewScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('override_interview_scores');
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'scores' => ['required', 'array', 'min:1'],
            'scores.*' => ['required', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'scores.required' => 'Daftar nilai poin wawancara wajib diisi.',
            'scores.array' => 'Format daftar nilai tidak valid.',
            'scores.*.required' => 'Setiap poin nilai wajib diisi.',
            'scores.*.integer' => 'Nilai harus berupa angka bulat.',
            'scores.*.min' => 'Nilai minimal adalah 0.',
            'scores.*.max' => 'Nilai maksimal adalah 100.',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
            'reason.required' => 'Alasan koreksi nilai wajib diisi.',
            'reason.min' => 'Alasan koreksi nilai wajib diisi minimal 10 karakter.',
            'reason.max' => 'Alasan koreksi nilai maksimal 500 karakter.',
        ];
    }
}
