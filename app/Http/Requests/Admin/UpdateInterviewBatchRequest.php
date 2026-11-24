<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\InterviewBatch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateInterviewBatchRequest extends FormRequest
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
            'batch_number' => ['nullable', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:100'],
            'interview_date' => ['required', 'date'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'quota' => ['required', 'integer', 'min:1', 'max:500'],
            'sequence_start' => ['required', 'integer', 'min:1'],
            'sequence_end' => ['required', 'integer', 'gte:sequence_start'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul jadwal gelombang wajib diisi.',
            'title.max' => 'Judul jadwal gelombang maksimal 100 karakter.',
            'interview_date.required' => 'Tanggal wawancara wajib diisi.',
            'interview_date.date' => 'Format tanggal wawancara tidak valid.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'end_time.required' => 'Jam selesai wajib diisi.',
            'quota.required' => 'Kuota wajib diisi.',
            'quota.min' => 'Kuota minimal 1 anak.',
            'quota.max' => 'Kuota maksimal 500 anak.',
            'sequence_start.required' => 'Nomor urut awal wajib diisi.',
            'sequence_start.min' => 'Nomor urut awal minimal 1.',
            'sequence_end.required' => 'Nomor urut akhir wajib diisi.',
            'sequence_end.gte' => 'Nomor urut akhir harus lebih besar atau sama dengan nomor urut awal.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $startTime = (string) $this->input('start_time');
            $endTime = (string) $this->input('end_time');

            if ($startTime !== '' && $endTime !== '' && $endTime <= $startTime) {
                $validator->errors()->add('end_time', 'Jam selesai harus lebih akhir dari jam mulai.');
            }

            /** @var InterviewBatch|null $batch */
            $batch = $this->route('interview_batch');
            $periodId = $batch?->admission_period_id ?? (int) $this->input('admission_period_id');
            $start = (int) $this->input('sequence_start');
            $end = (int) $this->input('sequence_end');
            $batchId = $batch?->id;

            if ($periodId > 0 && $start > 0 && $end >= $start) {
                $overlap = InterviewBatch::query()
                    ->where('admission_period_id', $periodId)
                    ->when($batchId, fn ($q) => $q->where('id', '!=', $batchId))
                    ->where(function ($query) use ($start, $end): void {
                        $query->where('sequence_start', '<=', $end)
                            ->where('sequence_end', '>=', $start);
                    })
                    ->first();

                if ($overlap) {
                    $validator->errors()->add(
                        'sequence_start',
                        "Rentang nomor urut ({$start}–{$end}) bertabrakan dengan jadwal '{$overlap->title}' ({$overlap->sequence_start}–{$overlap->sequence_end})."
                    );
                }
            }
        });
    }
}
