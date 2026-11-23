<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\DocumentVerificationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AdminReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('export_reports') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'period_id' => $this->filled('period_id') ? $this->integer('period_id') : null,
            'program_id' => $this->filled('program_id') ? $this->integer('program_id') : null,
            'verification_status' => $this->filled('verification_status') ? strtoupper(trim((string) $this->input('verification_status'))) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'period_id' => ['nullable', 'integer', 'exists:admission_periods,id'],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
            'verification_status' => ['nullable', 'in:'.implode(',', array_column(DocumentVerificationStatus::cases(), 'value'))],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('date_from') && $this->filled('date_to') && $this->input('date_from') > $this->input('date_to')) {
                $validator->errors()->add('date_to', 'Tanggal akhir harus sama atau setelah tanggal mulai.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'period_id.exists' => 'Periode pendaftaran tidak ditemukan.',
            'program_id.exists' => 'Program pendaftaran tidak ditemukan.',
            'date_from.date_format' => 'Tanggal mulai harus berformat YYYY-MM-DD.',
            'date_to.date_format' => 'Tanggal akhir harus berformat YYYY-MM-DD.',
            'verification_status.in' => 'Status verifikasi dokumen tidak valid.',
        ];
    }
}
