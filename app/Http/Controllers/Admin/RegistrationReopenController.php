<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Registration\CloseRegistrationReopenAction;
use App\Actions\Registration\OpenRegistrationReopenAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CloseRegistrationReopenRequest;
use App\Http\Requests\Admin\OpenRegistrationReopenRequest;
use App\Models\Registration;
use App\Models\RegistrationReopenWindow;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistrationReopenController extends Controller
{
    public function index(): View
    {
        $registrations = Registration::query()
            ->where('status', 'SUBMITTED')
            ->with([
                'applicant:id,full_name',
                'admissionPeriod:id,name,academic_year',
                'admissionPeriodProgram.program:id,name,code',
                'activeReopenWindow',
            ])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.registrations.index', [
            'registrations' => $registrations,
        ]);
    }

    public function create(Registration $registration): View
    {
        $registration->load([
            'applicant:id,full_name',
            'admissionPeriod:id,name,academic_year',
            'admissionPeriodProgram.program:id,name,code',
            'reopenWindows' => fn ($query) => $query
                ->with(['openedBy:id,name', 'closedBy:id,name'])
                ->orderByDesc('opened_at'),
            'activeReopenWindow',
        ]);

        return view('admin.registrations.reopen', [
            'registration' => $registration,
        ]);
    }

    public function store(
        OpenRegistrationReopenRequest $request,
        Registration $registration,
        OpenRegistrationReopenAction $action
    ): RedirectResponse {
        $action->execute(
            $registration,
            $request->user(),
            $request->validated('reason'),
            $request->validated('expires_at'),
        );

        return redirect()
            ->route('admin.registrations.index')
            ->with('success', 'Akses edit ulang pendaftaran berhasil dibuka.');
    }

    public function close(
        CloseRegistrationReopenRequest $request,
        RegistrationReopenWindow $reopenWindow,
        CloseRegistrationReopenAction $action
    ): RedirectResponse {
        $action->execute(
            $reopenWindow,
            $request->user(),
            $request->validated('reason'),
        );

        return back()->with('success', 'Akses edit ulang pendaftaran berhasil ditutup.');
    }
}
