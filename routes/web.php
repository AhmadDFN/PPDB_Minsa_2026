<?php

declare(strict_types=1);

use App\Enums\AdmissionPeriodStatus;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ApplicantManagementController;
use App\Http\Controllers\Admin\CommunicationChannelController;
use App\Http\Controllers\Admin\RegistrationReopenController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Applicant\ApplicantPasswordChangeController;
use App\Http\Controllers\Applicant\DashboardController;
use App\Http\Controllers\Applicant\DocumentController;
use App\Http\Controllers\Applicant\RegistrationFormController;
use App\Http\Controllers\Applicant\RegistrationPdfController;
use App\Http\Controllers\Applicant\RegistrationReviewController;
use App\Http\Controllers\Applicant\RegistrationSubmissionController;
use App\Http\Controllers\RegistrationPdfVerificationController;
use App\Models\AdmissionPeriod;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Web Routes — PPDB Minsatama
|--------------------------------------------------------------------------
*/

// Public Homepage
Route::get('/', function () {
    $activePeriod = null;

    try {
        if (Schema::hasTable('admission_periods')) {
            $activePeriod = AdmissionPeriod::query()
                ->where('status', AdmissionPeriodStatus::ACTIVE)
                ->where('is_active', true)
                ->with(['periodPrograms.program'])
                ->first();
        }
    } catch (Throwable) {
        $activePeriod = null;
    }

    return view('welcome', [
        'activePeriod' => $activePeriod,
    ]);
})->name('home');

// Authentication Routes (Applicant & Administrator)
require __DIR__.'/auth.php';

// Generic authenticated dashboard redirector
Route::middleware(['auth', 'active'])->get('/dashboard', function () {
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('applicant.dashboard');
})->name('dashboard');

Route::get('/verifikasi/bukti-pendaftaran/{token}', [RegistrationPdfVerificationController::class, 'show'])
    ->middleware('throttle:10,1')
    ->where('token', '[a-fA-F0-9]{64}')
    ->name('public.registration-pdfs.verify');

// Protected Applicant Area
Route::middleware(['auth', 'active', 'applicant'])->prefix('pendaftar')->name('applicant.')->group(function () {
    // Mandatory Password Change (accessible even when on temporary password)
    Route::get('/ganti-password', [ApplicantPasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/ganti-password', [ApplicantPasswordChangeController::class, 'update'])->name('password.change.update');

    // Portal Features (guarded by ensure.password.not.temporary)
    Route::middleware('ensure.password.not.temporary')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Multi-Step Registration Profile Forms
        Route::get('/biodata', [RegistrationFormController::class, 'editBiodata'])->name('biodata.edit');
        Route::post('/biodata', [RegistrationFormController::class, 'updateBiodata'])->name('biodata.update');

        Route::get('/alamat', [RegistrationFormController::class, 'editAddress'])->name('address.edit');
        Route::post('/alamat', [RegistrationFormController::class, 'updateAddress'])->name('address.update');

        Route::get('/orang-tua', [RegistrationFormController::class, 'editParents'])->name('parents.edit');
        Route::post('/orang-tua', [RegistrationFormController::class, 'updateParents'])->name('parents.update');

        Route::get('/pendidikan', [RegistrationFormController::class, 'editEducation'])->name('education.edit');
        Route::post('/pendidikan', [RegistrationFormController::class, 'updateEducation'])->name('education.update');

        Route::get('/program', [RegistrationFormController::class, 'editProgram'])->name('program.edit');
        Route::post('/program', [RegistrationFormController::class, 'updateProgram'])->name('program.update');

        Route::get('/pendaftaran/tinjau', [RegistrationReviewController::class, 'show'])->name('registration.review');
        Route::post('/pendaftaran/kirim', [RegistrationSubmissionController::class, 'store'])->name('registration.submit');
        Route::post('/pendaftaran/pdf/draft', [RegistrationPdfController::class, 'storeDraft'])->middleware('throttle:5,1')->name('registration.pdf.draft');
        Route::post('/pendaftaran/pdf/final', [RegistrationPdfController::class, 'storeFinal'])->middleware('throttle:5,1')->name('registration.pdf.final');
        Route::get('/pendaftaran/pdf/{pdfVersion}/unduh', [RegistrationPdfController::class, 'download'])->name('registration.pdf.download');

        Route::get('/dokumen', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('/dokumen/{requirementId}', [DocumentController::class, 'upload'])->name('documents.upload');
        Route::get('/dokumen/{documentId}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
        Route::get('/dokumen/{documentId}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::get('/dokumen/{documentId}/revisi/{revisionId}/preview', [DocumentController::class, 'previewRevision'])->name('documents.revisions.preview');
        Route::get('/dokumen/{documentId}/revisi/{revisionId}/download', [DocumentController::class, 'downloadRevision'])->name('documents.revisions.download');
    });
});

// Protected Administrator Area
Route::middleware(['auth', 'active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('/preview', function () {
        return view('admin.preview');
    })->name('preview');

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
        ->middleware('can:view_dashboard')
        ->name('dashboard');

    Route::middleware('can:view_applicants')->group(function () {
        Route::get('/pendaftar', [ApplicantManagementController::class, 'index'])->name('applicants.index');
        Route::get('/pendaftar/{registration}', [ApplicantManagementController::class, 'show'])->name('applicants.show');
        Route::get('/pendaftar/{registration}/dokumen/{document}/preview', [ApplicantManagementController::class, 'previewDocument'])->name('applicants.documents.preview');
        Route::get('/pendaftar/{registration}/dokumen/{document}/download', [ApplicantManagementController::class, 'downloadDocument'])->name('applicants.documents.download');
    });

    Route::middleware('can:manage_system_settings')->group(function () {
        Route::get('/pengaturan', [SystemSettingController::class, 'index'])->name('settings.index');
        Route::post('/pengaturan', [SystemSettingController::class, 'update'])->name('settings.update');
    });

    Route::prefix('communication')->name('communication.')->group(function () {
        Route::get('/', [CommunicationChannelController::class, 'index'])->name('index');
        Route::post('/channels', [CommunicationChannelController::class, 'store'])->name('channels.store');
        Route::patch('/channels/{channel}', [CommunicationChannelController::class, 'update'])->name('channels.update');
        Route::post('/channels/{channel}/validate', [CommunicationChannelController::class, 'validateConnection'])->name('channels.validate');
        Route::post('/channels/{channel}/activate', [CommunicationChannelController::class, 'activate'])->name('channels.activate');
        Route::post('/channels/{channel}/deactivate', [CommunicationChannelController::class, 'deactivate'])->name('channels.deactivate');
    });

    Route::middleware('can:reopen_registration')->group(function () {
        Route::get('/registrations', [RegistrationReopenController::class, 'index'])->name('registrations.index');
        Route::get('/registrations/{registration}/reopen', [RegistrationReopenController::class, 'create'])->name('registrations.reopen.create');
        Route::post('/registrations/{registration}/reopen', [RegistrationReopenController::class, 'store'])->name('registrations.reopen.store');
        Route::post('/registration-reopen-windows/{reopenWindow}/close', [RegistrationReopenController::class, 'close'])->name('registrations.reopen.close');
    });

    // Fallback handler for upcoming modules to prevent 404 during navigation testing
    Route::get('/{module}', function (string $module) {
        $moduleNames = [
            'dokumen' => 'Verifikasi Berkas',
            'periode' => 'Periode Pendaftaran',
            'program' => 'Program MI',
            'laporan' => 'Laporan & Ekspor',
            'pengguna' => 'Manajemen Pengguna',
        ];

        $name = $moduleNames[$module] ?? ucfirst($module);

        return redirect()->route('admin.dashboard')
            ->with('warning', "Modul {$name} dijadwalkan pada fase pengembangan berikutnya.");
    })->where('module', 'dokumen|periode|program|laporan|pengguna');
});
