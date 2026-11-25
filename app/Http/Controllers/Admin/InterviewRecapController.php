<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Interview\AdminOverrideInterviewScoreAction;
use App\Actions\Interview\ExportInterviewRecapCsvAction;
use App\Actions\Interview\InterviewRecapQueryBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InterviewRecapFilterRequest;
use App\Http\Requests\Admin\OverrideInterviewScoreRequest;
use App\Models\AdmissionPeriod;
use App\Models\InterviewBatch;
use App\Models\InterviewSession;
use App\Models\InterviewStage;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InterviewRecapController extends Controller
{
    public function index(
        InterviewRecapFilterRequest $request,
        InterviewRecapQueryBuilder $queryBuilder
    ): View {
        $period = $this->resolveActivePeriod();
        $filters = $request->validated();

        $stages = collect();
        $batches = collect();
        $registrations = collect();
        $summary = [
            'total_verified' => 0,
            'total_completed' => 0,
            'total_incomplete' => 0,
        ];

        if ($period) {
            $stages = InterviewStage::query()
                ->where('admission_period_id', $period->id)
                ->with(['criteria' => function ($query): void {
                    $query->where('is_active', true)->orderBy('sort_order');
                }])
                ->orderBy('stage_order')
                ->get();

            $batches = InterviewBatch::query()
                ->where('admission_period_id', $period->id)
                ->orderBy('batch_number')
                ->get();

            $registrations = $queryBuilder->build($period, $filters)
                ->paginate(20)
                ->withQueryString();

            // Compute summary stats across the active period (unfiltered)
            $summary['total_verified'] = $queryBuilder->build($period, [])->count();
            $summary['total_completed'] = $queryBuilder->build($period, ['completion_status' => 'completed'])->count();
            $summary['total_incomplete'] = max(0, $summary['total_verified'] - $summary['total_completed']);
        }

        $programs = Program::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.interview-recaps.index', [
            'period' => $period,
            'stages' => $stages,
            'batches' => $batches,
            'programs' => $programs,
            'registrations' => $registrations,
            'filters' => $filters,
            'summary' => $summary,
        ]);
    }

    public function override(
        OverrideInterviewScoreRequest $request,
        InterviewSession $session,
        AdminOverrideInterviewScoreAction $action
    ): RedirectResponse {
        $action->execute(
            $request->user(),
            $session,
            $request->validated('scores'),
            $request->validated('reason'),
            $request->validated('notes')
        );

        $studentName = $session->registration->applicant?->full_name ?? 'Calon Siswa';

        return back()->with(
            'success',
            "Nilai wawancara {$session->stage->title} untuk {$studentName} berhasil dikoreksi dan tercatat pada Audit Log."
        );
    }

    public function export(
        InterviewRecapFilterRequest $request,
        ExportInterviewRecapCsvAction $action
    ): StreamedResponse|RedirectResponse {
        $period = $this->resolveActivePeriod();

        if (! $period) {
            return back()->with('warning', 'Tidak ada periode pendaftaran yang aktif untuk diekspor.');
        }

        return $action->execute($period, $request->validated());
    }

    private function resolveActivePeriod(): ?AdmissionPeriod
    {
        return AdmissionPeriod::query()->where('is_active', true)->first()
            ?? AdmissionPeriod::query()->latest('start_date')->first();
    }
}
