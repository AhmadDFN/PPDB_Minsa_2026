<?php

declare(strict_types=1);

namespace App\Actions\Interview;

use App\Models\InterviewBatch;
use App\Models\User;
use App\Services\AdminConfigurationAuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveInterviewBatchAction
{
    public function __construct(
        private AdminConfigurationAuditService $auditService
    ) {}

    /**
     * Create or update an InterviewBatch, ensuring no sequence overlaps and logging audit.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(array $data, ?InterviewBatch $batch = null, ?User $actor = null): InterviewBatch
    {
        return DB::transaction(function () use ($data, $batch, $actor): InterviewBatch {
            $periodId = (int) ($data['admission_period_id'] ?? $batch?->admission_period_id);
            $start = (int) $data['sequence_start'];
            $end = (int) $data['sequence_end'];
            $batchId = $batch?->id;

            // 1. Double-check overlap inside transaction
            $overlap = InterviewBatch::query()
                ->where('admission_period_id', $periodId)
                ->when($batchId, fn ($q) => $q->where('id', '!=', $batchId))
                ->where(function ($query) use ($start, $end): void {
                    $query->where('sequence_start', '<=', $end)
                        ->where('sequence_end', '>=', $start);
                })
                ->lockForUpdate()
                ->first();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'sequence_start' => "Rentang nomor urut ({$start}–{$end}) bertabrakan dengan jadwal '{$overlap->title}' ({$overlap->sequence_start}–{$overlap->sequence_end}).",
                ]);
            }

            // 2. Determine batch_number if not provided
            if (empty($data['batch_number'])) {
                if ($batch) {
                    $data['batch_number'] = $batch->batch_number;
                } else {
                    $maxBatch = (int) InterviewBatch::query()
                        ->where('admission_period_id', $periodId)
                        ->max('batch_number');
                    $data['batch_number'] = $maxBatch + 1;
                }
            }

            $before = $batch ? $batch->toArray() : null;

            if ($batch) {
                $batch->update($data);
                $savedBatch = $batch->fresh();
                $action = 'updated';
            } else {
                $savedBatch = InterviewBatch::query()->create($data);
                $action = 'created';
            }

            // 3. Record Audit Log
            $this->auditService->record(
                'interview_batch',
                $savedBatch->id,
                $action,
                $actor,
                $before,
                $savedBatch->toArray()
            );

            return $savedBatch;
        });
    }
}
