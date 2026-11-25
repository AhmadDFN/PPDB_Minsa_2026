<?php

declare(strict_types=1);

namespace App\Actions\Interview;

use App\Models\AdmissionPeriod;
use App\Models\InterviewStage;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Builder;

class InterviewRecapQueryBuilder
{
    /**
     * Build a filtered query of verified registrations for interview recaps & CSV export.
     *
     * @param  array<string, mixed>  $filters
     */
    public function build(AdmissionPeriod $period, array $filters): Builder
    {
        $stages = InterviewStage::query()
            ->where('admission_period_id', $period->id)
            ->orderBy('stage_order')
            ->get();

        $allStageIds = $stages->where('program_scope', 'ALL')->pluck('id')->all();
        $unggulanStageIds = $stages->pluck('id')->all();

        $query = Registration::query()
            ->where('admission_period_id', $period->id)
            ->whereNotNull('verified_at')
            ->with([
                'applicant',
                'admissionPeriodProgram.program',
                'interviewBatch',
                'interviewSessions.scores',
                'interviewSessions.interviewer',
                'interviewSessions.lastEditor',
            ]);

        if (! empty($filters['batch_id'])) {
            $query->where('interview_batch_id', (int) $filters['batch_id']);
        }

        if (! empty($filters['program_id'])) {
            $programId = (int) $filters['program_id'];
            $query->whereHas('admissionPeriodProgram', function (Builder $q) use ($programId): void {
                $q->where('program_id', $programId);
            });
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $q) use ($search): void {
                $q->where('registration_number', 'like', "%{$search}%")
                    ->orWhereHas('applicant', function (Builder $aq) use ($search): void {
                        $aq->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        $completionStatus = $filters['completion_status'] ?? 'all';
        if ($completionStatus === 'completed') {
            $this->applyCompletedFilter($query, $allStageIds, $unggulanStageIds);
        } elseif ($completionStatus === 'incomplete') {
            $this->applyIncompleteFilter($query, $allStageIds, $unggulanStageIds);
        }

        return $query->orderBy('queue_sequence')->orderBy('id');
    }

    /**
     * @param  array<int, int>  $allStageIds
     * @param  array<int, int>  $unggulanStageIds
     */
    private function applyCompletedFilter(Builder $query, array $allStageIds, array $unggulanStageIds): void
    {
        $query->where(function (Builder $outer) use ($allStageIds, $unggulanStageIds): void {
            // Non-unggulan completed when all ALL-scope stages are COMPLETED
            $outer->where(function (Builder $reguler) use ($allStageIds): void {
                $reguler->whereHas('admissionPeriodProgram.program', function (Builder $pq): void {
                    $pq->where('code', 'not like', '%UNGGULAN%');
                });
                foreach ($allStageIds as $stageId) {
                    $reguler->whereHas('interviewSessions', function (Builder $sq) use ($stageId): void {
                        $sq->where('interview_stage_id', $stageId)->where('status', 'COMPLETED');
                    });
                }
            })->orWhere(function (Builder $unggulan) use ($unggulanStageIds): void {
                $unggulan->whereHas('admissionPeriodProgram.program', function (Builder $pq): void {
                    $pq->where('code', 'like', '%UNGGULAN%');
                });
                foreach ($unggulanStageIds as $stageId) {
                    $unggulan->whereHas('interviewSessions', function (Builder $sq) use ($stageId): void {
                        $sq->where('interview_stage_id', $stageId)->where('status', 'COMPLETED');
                    });
                }
            });
        });
    }

    /**
     * @param  array<int, int>  $allStageIds
     * @param  array<int, int>  $unggulanStageIds
     */
    private function applyIncompleteFilter(Builder $query, array $allStageIds, array $unggulanStageIds): void
    {
        $query->where(function (Builder $outer) use ($allStageIds, $unggulanStageIds): void {
            $outer->where(function (Builder $reguler) use ($allStageIds): void {
                $reguler->whereHas('admissionPeriodProgram.program', function (Builder $pq): void {
                    $pq->where('code', 'not like', '%UNGGULAN%');
                });
                $reguler->where(function (Builder $missing) use ($allStageIds): void {
                    foreach ($allStageIds as $stageId) {
                        $missing->orWhereDoesntHave('interviewSessions', function (Builder $sq) use ($stageId): void {
                            $sq->where('interview_stage_id', $stageId)->where('status', 'COMPLETED');
                        });
                    }
                });
            })->orWhere(function (Builder $unggulan) use ($unggulanStageIds): void {
                $unggulan->whereHas('admissionPeriodProgram.program', function (Builder $pq): void {
                    $pq->where('code', 'like', '%UNGGULAN%');
                });
                $unggulan->where(function (Builder $missing) use ($unggulanStageIds): void {
                    foreach ($unggulanStageIds as $stageId) {
                        $missing->orWhereDoesntHave('interviewSessions', function (Builder $sq) use ($stageId): void {
                            $sq->where('interview_stage_id', $stageId)->where('status', 'COMPLETED');
                        });
                    }
                });
            });
        });
    }
}
