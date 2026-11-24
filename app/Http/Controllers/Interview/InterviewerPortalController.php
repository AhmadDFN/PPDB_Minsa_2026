<?php

declare(strict_types=1);

namespace App\Http\Controllers\Interview;

use App\Actions\Interview\LookupApplicantForInterviewAction;
use App\Actions\Interview\StartInterviewSessionAction;
use App\Actions\Interview\SubmitAndLockInterviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Interview\LookupApplicantRequest;
use App\Http\Requests\Interview\SubmitInterviewScoreRequest;
use App\Models\InterviewScore;
use App\Models\InterviewSession;
use App\Models\RegistrationDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InterviewerPortalController extends Controller
{
    /**
     * Show interviewer main dashboard with QR scan and manual input.
     */
    public function index(): View
    {
        $user = auth()->user();
        $stage = LookupApplicantForInterviewAction::resolveStageForUser($user);

        $completedTodayCount = 0;
        $activeSession = null;

        if ($user) {
            $completedTodayCount = InterviewSession::query()
                ->where('interviewer_id', $user->id)
                ->where('status', 'COMPLETED')
                ->whereDate('completed_at', today())
                ->count();

            $activeSession = InterviewSession::query()
                ->with(['registration.applicant', 'registration.admissionPeriodProgram.program'])
                ->where('interviewer_id', $user->id)
                ->where('status', 'IN_PROGRESS')
                ->latest('started_at')
                ->first();
        }

        return view('interview.index', [
            'stage' => $stage,
            'completedTodayCount' => $completedTodayCount,
            'activeSession' => $activeSession,
        ]);
    }

    /**
     * Lookup an applicant and start or resume an interview session.
     */
    public function lookup(
        LookupApplicantRequest $request,
        LookupApplicantForInterviewAction $lookupAction,
        StartInterviewSessionAction $startAction
    ): RedirectResponse {
        $user = $request->user();
        $identifier = $request->validated('identifier');

        $result = $lookupAction->execute($user, $identifier);
        $session = $startAction->execute($user, $result['registration'], $result['stage']);

        return redirect()->route('interview.session', $session);
    }

    /**
     * Display the grading form for an active interview session.
     */
    public function showSession(InterviewSession $session): View|RedirectResponse
    {
        $user = auth()->user();

        // Ensure session belongs to current authenticated interviewer
        if ((int) $session->interviewer_id !== (int) $user->id) {
            abort(403, 'Akses ditolak. Sesi wawancara ini bukan milik akun Anda.');
        }

        // Absolute score isolation: locked sessions cannot be viewed or edited by interviewer
        if ($session->status === 'COMPLETED') {
            return redirect()
                ->route('interview.index')
                ->with('warning', 'Sesi wawancara untuk siswa ini sudah selesai dan dikunci.');
        }

        if ($session->status !== 'IN_PROGRESS') {
            return redirect()
                ->route('interview.index')
                ->with('warning', 'Sesi wawancara tidak valid.');
        }

        $session->load([
            'registration.applicant',
            'registration.admissionPeriodProgram.program',
            'registration.interviewBatch',
            'stage.criteria' => function ($query): void {
                $query->where('is_active', true)->orderBy('sort_order');
            },
        ]);

        return view('interview.session', [
            'session' => $session,
        ]);
    }

    /**
     * Cancel an in-progress interview session.
     */
    public function cancelSession(InterviewSession $session): RedirectResponse
    {
        $user = auth()->user();

        if ((int) $session->interviewer_id !== (int) $user->id) {
            abort(403, 'Akses ditolak. Sesi wawancara ini bukan milik akun Anda.');
        }

        if ($session->status !== 'IN_PROGRESS') {
            return redirect()
                ->route('interview.index')
                ->with('warning', 'Hanya sesi yang sedang berlangsung yang dapat dibatalkan.');
        }

        InterviewScore::query()
            ->where('interview_session_id', $session->id)
            ->delete();

        $session->delete();

        return redirect()
            ->route('interview.index')
            ->with('info', 'Sesi wawancara berhasil dibatalkan. Calon siswa dapat di-scan kembali.');
    }

    /**
     * Submit scores and permanently lock the session.
     */
    public function submitSession(
        SubmitInterviewScoreRequest $request,
        InterviewSession $session,
        SubmitAndLockInterviewAction $submitAction
    ): RedirectResponse {
        $user = $request->user();

        if ((int) $session->interviewer_id !== (int) $user->id) {
            abort(403, 'Akses ditolak. Sesi wawancara ini bukan milik akun Anda.');
        }

        if ($session->status !== 'IN_PROGRESS') {
            return redirect()
                ->route('interview.index')
                ->with('warning', 'Sesi ini sudah dikunci sebelumnya.');
        }

        $submitAction->execute(
            $user,
            $session,
            $request->input('scores', []),
            $request->input('notes')
        );

        $studentName = $session->registration->applicant?->full_name ?? 'Calon siswa';

        return redirect()
            ->route('interview.index')
            ->with('success', "Penilaian untuk {$studentName} berhasil disimpan dan dikunci.");
    }

    /**
     * Stream candidate's 3x4 photo for active in-progress session securely.
     */
    public function photo(InterviewSession $session): BinaryFileResponse|Response
    {
        $user = auth()->user();

        if ((int) $session->interviewer_id !== (int) $user->id) {
            abort(403);
        }

        if ($session->status !== 'IN_PROGRESS') {
            abort(403);
        }

        $photoDoc = RegistrationDocument::query()
            ->where('registration_id', $session->registration_id)
            ->whereHas('requirement', function ($query): void {
                $query->where('code', 'FOTO');
            })
            ->first();

        if ($photoDoc) {
            $disk = Storage::disk((string) config('ppdb.documents.disk', 'ppdb_private'));
            if ($disk->exists($photoDoc->file_path)) {
                return response()->file($disk->path($photoDoc->file_path), [
                    'Content-Type' => $photoDoc->mime_type,
                    'Content-Disposition' => 'inline',
                    'Cache-Control' => 'private, no-store, max-age=0',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }
        }

        // Return fallback SVG avatar if no photo uploaded
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 160" width="120" height="160" fill="none"><rect width="120" height="160" fill="#f1f5f9"/><circle cx="60" cy="55" r="28" fill="#cbd5e1"/><path d="M20 145c0-26 18-42 40-42s40 16 40 42z" fill="#cbd5e1"/><text x="60" y="150" font-size="9" text-anchor="middle" fill="#64748b" font-family="sans-serif">Foto Belum Diunggah</text></svg>';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
