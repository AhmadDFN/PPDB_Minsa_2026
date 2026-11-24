<?php

declare(strict_types=1);

namespace App\Http\Requests\Interview;

use Illuminate\Foundation\Http\FormRequest;

class SubmitInterviewScoreRequest extends FormRequest
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
            'scores' => ['required', 'array'],
            'scores.*' => ['required', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'scores.required' => 'Nilai penilaian wawancara wajib diisi.',
            'scores.array' => 'Format daftar nilai tidak valid.',
            'scores.*.required' => 'Setiap poin penilaian wajib diisi.',
            'scores.*.integer' => 'Nilai harus berupa angka bulat.',
            'scores.*.min' => 'Nilai minimal adalah 0.',
            'scores.*.max' => 'Nilai maksimal adalah 100.',
            'notes.string' => 'Catatan harus berupa teks.',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
