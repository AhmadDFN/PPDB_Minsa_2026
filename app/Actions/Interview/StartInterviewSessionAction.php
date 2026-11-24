<?php

declare(strict_types=1);

namespace App\Actions\Interview;

use App\Models\InterviewSession;
use App\Models\InterviewStage;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StartInterviewSessionAction
{
    /**
     * Start or resume an interview session with soft locking.
     *
     * @throws ValidationException
     */
    public function execute(User $interviewer, Registration $registration, InterviewStage $stage): InterviewSession
    {
        return DB::transaction(function () use ($interviewer, $registration, $stage): InterviewSession {
            /** @var InterviewSession|null $existing */
            $existing = InterviewSession::query()
                ->where('registration_id', $registration->id)
                ->where('interview_stage_id', $stage->id)
                ->lockForUpdate()
                ->first();

            $studentName = $registration->applicant?->full_name ?? 'Calon siswa';

            if ($existing) {
                // Already completed and locked
                if ($existing->status === 'COMPLETED') {
                    throw ValidationException::withMessages([
                        'identifier' => "Penilaian tahap ini untuk calon siswa {$studentName} sudah selesai dan terkunci.",
                    ]);
                }

                // Currently in progress
                if ($existing->status === 'IN_PROGRESS') {
                    // Same interviewer -> resume session
                    if ((int) $existing->interviewer_id === (int) $interviewer->id) {
                        return $existing;
                    }

                    // Another interviewer -> check 30 minutes threshold
                    $diffMinutes = $existing->started_at ? $existing->started_at->diffInMinutes(now()) : 999;

                    if ($diffMinutes < 30) {
                        $otherInterviewerName = $existing->interviewer?->name ?? 'Pewawancara lain';
                        throw ValidationException::withMessages([
                            'identifier' => "Calon siswa sedang aktif diwawancarai oleh {$otherInterviewerName}. Silakan tunggu atau hubungi panitia.",
                        ]);
                    }

                    // Abandoned for 30 minutes or more -> takeover session
                    $existing->update([
                        'interviewer_id' => $interviewer->id,
                        'started_at' => now(),
                    ]);

                    return $existing->fresh();
                }
            }

            // Create new IN_PROGRESS session
            return InterviewSession::query()->create([
                'registration_id' => $registration->id,
                'interview_stage_id' => $stage->id,
                'interviewer_id' => $interviewer->id,
                'status' => 'IN_PROGRESS',
                'started_at' => now(),
            ]);
        });
    }
}
