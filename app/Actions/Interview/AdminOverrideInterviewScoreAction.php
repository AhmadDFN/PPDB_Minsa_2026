<?php

declare(strict_types=1);

namespace App\Actions\Interview;

use App\Models\InterviewScore;
use App\Models\InterviewSession;
use App\Models\User;
use App\Services\AdminConfigurationAuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminOverrideInterviewScoreAction
{
    public function __construct(
        private readonly AdminConfigurationAuditService $auditService
    ) {}

    /**
     * Override scores for a completed interview session with mandatory audit reason.
     *
     * @param  array<int|string, int|string>  $scores
     *
     * @throws ValidationException
     */
    public function execute(
        User $admin,
        InterviewSession $session,
        array $scores,
        string $reason,
        ?string $notes = null
    ): InterviewSession {
        $trimmedReason = trim($reason);
        if (mb_strlen($trimmedReason) < 10) {
            throw ValidationException::withMessages([
                'reason' => 'Alasan koreksi nilai wajib diisi minimal 10 karakter.',
            ]);
        }

        if ($session->status !== 'COMPLETED') {
            throw ValidationException::withMessages([
                'scores' => 'Hanya sesi wawancara yang sudah selesai (COMPLETED) yang dapat dikoreksi oleh admin.',
            ]);
        }

        $activeCriteria = $session->stage->criteria()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($activeCriteria->isEmpty()) {
            throw ValidationException::withMessages([
                'scores' => 'Tidak ada kriteria penilaian aktif pada tahap ini.',
            ]);
        }

        foreach ($activeCriteria as $criterion) {
            if (! isset($scores[$criterion->id]) || $scores[$criterion->id] === '') {
                throw ValidationException::withMessages([
                    "scores.{$criterion->id}" => "Nilai untuk '{$criterion->title}' wajib diisi.",
                ]);
            }

            $val = (int) $scores[$criterion->id];
            if ($val < 0 || $val > 100) {
                throw ValidationException::withMessages([
                    "scores.{$criterion->id}" => "Nilai untuk '{$criterion->title}' harus antara 0 hingga 100.",
                ]);
            }
        }

        return DB::transaction(function () use ($admin, $session, $activeCriteria, $scores, $trimmedReason, $notes): InterviewSession {
            $lockedSession = InterviewSession::query()
                ->with('scores')
                ->where('id', $session->id)
                ->lockForUpdate()
                ->firstOrFail();

            $before = [
                'registration_id' => $lockedSession->registration_id,
                'interview_stage_id' => $lockedSession->interview_stage_id,
                'total_score' => $lockedSession->total_score,
                'average_score' => $lockedSession->average_score !== null ? (float) $lockedSession->average_score : null,
                'notes' => $lockedSession->notes,
                'scores' => $lockedSession->scores->pluck('score', 'interview_criterion_id')->toArray(),
            ];

            $totalScore = 0;
            $count = $activeCriteria->count();

            foreach ($activeCriteria as $criterion) {
                $val = (int) $scores[$criterion->id];
                $totalScore += $val;

                InterviewScore::query()->updateOrCreate(
                    [
                        'interview_session_id' => $lockedSession->id,
                        'interview_criterion_id' => $criterion->id,
                    ],
                    [
                        'score' => $val,
                    ]
                );
            }

            $averageScore = $count > 0 ? round($totalScore / $count, 2) : 0.00;

            $lockedSession->update([
                'total_score' => $totalScore,
                'average_score' => $averageScore,
                'notes' => $notes !== null ? (trim($notes) !== '' ? trim($notes) : null) : $lockedSession->notes,
                'last_edited_by' => $admin->id,
                'last_edit_reason' => $trimmedReason,
            ]);

            $fresh = $lockedSession->fresh(['scores']);

            $after = [
                'registration_id' => $fresh->registration_id,
                'interview_stage_id' => $fresh->interview_stage_id,
                'total_score' => $fresh->total_score,
                'average_score' => $fresh->average_score !== null ? (float) $fresh->average_score : null,
                'notes' => $fresh->notes,
                'last_edited_by' => $fresh->last_edited_by,
                'last_edit_reason' => $fresh->last_edit_reason,
                'scores' => $fresh->scores->pluck('score', 'interview_criterion_id')->toArray(),
            ];

            $this->auditService->record(
                'interview_session',
                $fresh->id,
                'score_overridden',
                $admin,
                $before,
                $after
            );

            return $fresh;
        });
    }
}
