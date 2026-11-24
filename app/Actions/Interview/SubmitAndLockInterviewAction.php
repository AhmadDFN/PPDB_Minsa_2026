<?php

declare(strict_types=1);

namespace App\Actions\Interview;

use App\Models\InterviewScore;
use App\Models\InterviewSession;
use App\Models\User;
use App\Services\AdminConfigurationAuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitAndLockInterviewAction
{
    public function __construct(
        private readonly AdminConfigurationAuditService $auditService
    ) {}

    /**
     * Submit scores and permanently lock an interview session.
     *
     * @param  array<int|string, int|string>  $scores  Map of criterion_id => score
     *
     * @throws ValidationException
     */
    public function execute(User $interviewer, InterviewSession $session, array $scores, ?string $notes = null): InterviewSession
    {
        if ($session->status !== 'IN_PROGRESS') {
            throw ValidationException::withMessages([
                'scores' => 'Sesi wawancara ini sudah selesai dan tidak dapat diubah.',
            ]);
        }

        if ((int) $session->interviewer_id !== (int) $interviewer->id) {
            throw ValidationException::withMessages([
                'scores' => 'Anda tidak memiliki hak akses untuk menilai sesi wawancara ini.',
            ]);
        }

        $activeCriteria = $session->stage->criteria()
            ->where('is_active', true)
            ->get();

        if ($activeCriteria->isEmpty()) {
            throw ValidationException::withMessages([
                'scores' => 'Tidak ada kriteria penilaian aktif pada tahap ini.',
            ]);
        }

        // Validate all active criteria are scored
        foreach ($activeCriteria as $criterion) {
            if (! isset($scores[$criterion->id]) || $scores[$criterion->id] === '') {
                throw ValidationException::withMessages([
                    "scores.{$criterion->id}" => "Nilai untuk '{$criterion->title}' wajib diisi.",
                ]);
            }

            $scoreVal = (int) $scores[$criterion->id];
            if ($scoreVal < 0 || $scoreVal > 100) {
                throw ValidationException::withMessages([
                    "scores.{$criterion->id}" => "Nilai untuk '{$criterion->title}' harus berupa angka antara 0 hingga 100.",
                ]);
            }
        }

        return DB::transaction(function () use ($interviewer, $session, $activeCriteria, $scores, $notes): InterviewSession {
            $lockedSession = InterviewSession::query()
                ->where('id', $session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSession->status !== 'IN_PROGRESS') {
                throw ValidationException::withMessages([
                    'scores' => 'Sesi wawancara ini sudah dikunci sebelumnya.',
                ]);
            }

            $before = $lockedSession->toArray();

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
                'status' => 'COMPLETED',
                'completed_at' => now(),
                'total_score' => $totalScore,
                'average_score' => $averageScore,
                'notes' => $notes ? trim($notes) : null,
            ]);

            $after = $lockedSession->fresh()->toArray();

            // Record audit log
            $this->auditService->record(
                'interview_session',
                $lockedSession->id,
                'locked',
                $interviewer,
                $before,
                $after
            );

            return $lockedSession->fresh();
        });
    }
}
