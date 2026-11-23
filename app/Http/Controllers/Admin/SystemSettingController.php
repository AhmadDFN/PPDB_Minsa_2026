<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\SystemSettingLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class SystemSettingController extends Controller
{
    /**
     * Display system settings screen for superadmins.
     */
    public function index(): View
    {
        $institutionName = SystemSetting::getInstitutionName();
        $recentLogs = SystemSettingLog::query()
            ->with('user')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('admin.settings.index', [
            'institutionName' => $institutionName,
            'recentLogs' => $recentLogs,
        ]);
    }

    /**
     * Update dynamic institutional branding settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institution_display_name' => ['required', 'string', 'min:3', 'max:100'],
        ], [
            'institution_display_name.required' => 'Nama institusi wajib diisi.',
            'institution_display_name.min' => 'Nama institusi minimal terdiri dari 3 karakter.',
            'institution_display_name.max' => 'Nama institusi maksimal 100 karakter.',
        ]);

        $sanitizedName = strip_tags(trim($validated['institution_display_name']));

        try {
            SystemSetting::set(
                key: 'institution_display_name',
                value: $sanitizedName,
                description: 'Nama institusi dan branding tampilan utama sistem PPDB.',
                byUser: $request->user()
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Pengaturan belum dapat disimpan. Tidak ada perubahan yang diterapkan.');
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan nama institusi berhasil diperbarui.');
    }
}
