<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreInterviewCriterionRequest;
use App\Http\Requests\Admin\UpdateInterviewCriterionRequest;
use App\Models\InterviewCriterion;
use App\Models\InterviewStage;
use App\Services\AdminConfigurationAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InterviewCriterionController
{
    public function store(
        StoreInterviewCriterionRequest $request,
        InterviewStage $interviewStage,
        AdminConfigurationAuditService $auditService
    ): RedirectResponse {
        $sortOrder = $request->validated('sort_order')
            ? (int) $request->validated('sort_order')
            : (int) ($interviewStage->criteria()->max('sort_order') ?? 0) + 1;

        $criterion = $interviewStage->criteria()->create([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'min_score' => 0,
            'max_score' => 100,
            'sort_order' => $sortOrder,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $auditService->record(
            'interview_criterion',
            (int) $criterion->id,
            'created',
            $request->user(),
            null,
            $criterion->toArray()
        );

        return redirect()->route('admin.interview-stages.index')
            ->with('success', "Poin penilaian '{$criterion->title}' berhasil ditambahkan ke {$interviewStage->title}.");
    }

    public function update(
        UpdateInterviewCriterionRequest $request,
        InterviewCriterion $interviewCriterion,
        AdminConfigurationAuditService $auditService
    ): RedirectResponse {
        $stage = $interviewCriterion->stage;
        $willBeActive = $request->boolean('is_active', true);

        // Pastikan minimal 1 poin tetap aktif pada tahap yang aktif
        if (! $willBeActive && $stage?->is_active) {
            $otherActive = $stage->criteria()
                ->where('is_active', true)
                ->where('id', '!=', $interviewCriterion->id)
                ->count();

            if ($otherActive === 0) {
                return back()->withInput()->with(
                    'error',
                    'Tahap wawancara yang aktif harus memiliki minimal satu poin penilaian yang aktif.'
                );
            }
        }

        $before = $interviewCriterion->toArray();

        $interviewCriterion->update([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'sort_order' => $request->validated('sort_order') ? (int) $request->validated('sort_order') : $interviewCriterion->sort_order,
            'is_active' => $willBeActive,
        ]);

        $auditService->record(
            'interview_criterion',
            (int) $interviewCriterion->id,
            'updated',
            $request->user(),
            $before,
            $interviewCriterion->fresh()->toArray()
        );

        return redirect()->route('admin.interview-stages.index')
            ->with('success', "Poin penilaian '{$interviewCriterion->title}' berhasil diperbarui.");
    }

    public function destroy(
        Request $request,
        InterviewCriterion $interviewCriterion,
        AdminConfigurationAuditService $auditService
    ): RedirectResponse {
        // Proteksi Task 3.3: Poin yang sudah memiliki riwayat nilai TIDAK BOLEH dihapus
        if ($interviewCriterion->scores()->exists()) {
            return back()->with(
                'error',
                "Poin penilaian '{$interviewCriterion->title}' sudah memiliki riwayat penilaian dari pewawancara dan tidak dapat dihapus. Anda dapat menonaktifkannya."
            );
        }

        $stage = $interviewCriterion->stage;
        if ($stage?->is_active && $interviewCriterion->is_active) {
            $activeCount = $stage->criteria()->where('is_active', true)->count();
            if ($activeCount <= 1) {
                return back()->with(
                    'error',
                    'Tahap wawancara yang aktif harus memiliki minimal satu poin penilaian yang aktif.'
                );
            }
        }

        $before = $interviewCriterion->toArray();
        $criterionId = (int) $interviewCriterion->id;
        $title = $interviewCriterion->title;

        $interviewCriterion->delete();

        $auditService->record(
            'interview_criterion',
            $criterionId,
            'deleted',
            $request->user(),
            $before,
            null
        );

        return redirect()->route('admin.interview-stages.index')
            ->with('success', "Poin penilaian '{$title}' berhasil dihapus.");
    }
}
