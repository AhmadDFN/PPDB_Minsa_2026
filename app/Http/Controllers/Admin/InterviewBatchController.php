<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Interview\SaveInterviewBatchAction;
use App\Http\Requests\Admin\StoreInterviewBatchRequest;
use App\Http\Requests\Admin\UpdateInterviewBatchRequest;
use App\Models\AdmissionPeriod;
use App\Models\InterviewBatch;
use App\Services\AdminConfigurationAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InterviewBatchController
{
    public function index(Request $request): View
    {
        $period = AdmissionPeriod::query()->where('is_active', true)->first()
            ?? AdmissionPeriod::query()->latest('start_date')->first();

        $batches = $period
            ? InterviewBatch::query()
                ->where('admission_period_id', $period->id)
                ->withCount('registrations')
                ->orderBy('batch_number')
                ->get()
            : collect();

        /** @var InterviewBatch|null $lastBatch */
        $lastBatch = $batches->last();
        $suggestedStart = $lastBatch ? (int) $lastBatch->sequence_end + 1 : 1;
        $suggestedEnd = $suggestedStart + 70 - 1;
        $suggestedBatchNumber = $lastBatch ? (int) $lastBatch->batch_number + 1 : 1;

        return view('admin.interview-batches.index', [
            'period' => $period,
            'batches' => $batches,
            'suggestedStart' => $suggestedStart,
            'suggestedEnd' => $suggestedEnd,
            'suggestedBatchNumber' => $suggestedBatchNumber,
        ]);
    }

    public function store(StoreInterviewBatchRequest $request, SaveInterviewBatchAction $action): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $batch = $action->execute($data, null, $request->user());

        return redirect()->route('admin.interview-batches.index')
            ->with('success', "Jadwal wawancara '{$batch->title}' berhasil ditambahkan.");
    }

    public function update(
        UpdateInterviewBatchRequest $request,
        InterviewBatch $interviewBatch,
        SaveInterviewBatchAction $action
    ): RedirectResponse {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $batch = $action->execute($data, $interviewBatch, $request->user());

        return redirect()->route('admin.interview-batches.index')
            ->with('success', "Jadwal wawancara '{$batch->title}' berhasil diperbarui.");
    }

    public function destroy(
        Request $request,
        InterviewBatch $interviewBatch,
        AdminConfigurationAuditService $auditService
    ): RedirectResponse {
        if ($interviewBatch->registrations()->exists()) {
            return back()->with(
                'error',
                "Jadwal gelombang '{$interviewBatch->title}' sudah memiliki pendaftar terplot dan tidak dapat dihapus. Anda dapat menonaktifkan statusnya."
            );
        }

        $before = $interviewBatch->toArray();
        $batchId = (int) $interviewBatch->id;
        $title = $interviewBatch->title;

        $interviewBatch->delete();

        $auditService->record('interview_batch', $batchId, 'deleted', $request->user(), $before, null);

        return redirect()->route('admin.interview-batches.index')
            ->with('success', "Jadwal gelombang '{$title}' berhasil dihapus.");
    }
}
