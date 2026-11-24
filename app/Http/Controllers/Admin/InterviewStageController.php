<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UpdateInterviewStageRequest;
use App\Models\AdmissionPeriod;
use App\Models\InterviewStage;
use App\Services\AdminConfigurationAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InterviewStageController
{
    public function index(Request $request): View
    {
        $period = AdmissionPeriod::query()->where('is_active', true)->first()
            ?? AdmissionPeriod::query()->latest('start_date')->first();

        $stages = $period
            ? InterviewStage::query()
                ->where('admission_period_id', $period->id)
                ->with(['criteria' => fn ($query) => $query->withCount('scores')->orderBy('sort_order')])
                ->orderBy('stage_order')
                ->get()
            : collect();

        return view('admin.interview-stages.index', [
            'period' => $period,
            'stages' => $stages,
        ]);
    }

    public function update(
        UpdateInterviewStageRequest $request,
        InterviewStage $interviewStage,
        AdminConfigurationAuditService $auditService
    ): RedirectResponse {
        $before = $interviewStage->toArray();

        $interviewStage->update([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $auditService->record(
            'interview_stage',
            (int) $interviewStage->id,
            'updated',
            $request->user(),
            $before,
            $interviewStage->fresh()->toArray()
        );

        return redirect()->route('admin.interview-stages.index')
            ->with('success', "Tahap wawancara '{$interviewStage->title}' berhasil diperbarui.");
    }
}
