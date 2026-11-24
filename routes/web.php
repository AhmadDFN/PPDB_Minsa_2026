<?php

declare(strict_types=1);

use App\Enums\AdmissionPeriodStatus;
use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdmissionPeriodController;
use App\Http\Controllers\Admin\ApplicantManagementController;
use App\Http\Controllers\Admin\CommunicationChannelController;
use App\Http\Controllers\Admin\DocumentRequirementController;
use App\Http\Controllers\Admin\DocumentVerificationController;
use App\Http\Controllers\Admin\InterviewBatchController;
use App\Http\Controllers\Admin\InterviewCriterionController;
use App\Http\Controllers\Admin\InterviewStageController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\RegistrationReopenController;
use App\Http\Controllers\Admin\RolePermissionController;
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

    Route::middleware('can:export_reports')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AdminReportController::class, 'index'])->name('index');
        Route::get('/export', [AdminReportController::class, 'export'])->name('export');
    });

    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])
        ->middleware('can:view_activity_logs')
        ->name('audit-logs.index');

    Route::middleware('can:verify_documents')->prefix('document-verification')->name('document-verification.')->group(function () {
        Route::get('/', [DocumentVerificationController::class, 'index'])->name('index');
        Route::get('/{registration}/documents/{document}/preview', [DocumentVerificationController::class, 'preview'])->name('preview');
        Route::post('/{registration}/documents/{document}/approve', [DocumentVerificationController::class, 'approve'])->name('approve');
        Route::post('/{registration}/documents/{document}/reject', [DocumentVerificationController::class, 'reject'])->name('reject');
        Route::post('/{registration}/documents/{document}/revision', [DocumentVerificationController::class, 'revision'])->name('revision');
        Route::get('/{registration}/documents/{document}/history', [DocumentVerificationController::class, 'history'])->name('history');
        Route::post('/{registration}/sahkan', [DocumentVerificationController::class, 'approveRegistration'])->name('approve-registration');
    });

    Route::middleware('can:manage_system_settings')->group(function () {
        Route::get('/pengaturan', [SystemSettingController::class, 'index'])->name('settings.index');
        Route::post('/pengaturan', [SystemSettingController::class, 'update'])->name('settings.update');
    });

    Route::prefix('communication')->name('communication.')->group(function () {
        Route::get('/', [CommunicationChannelController::class, 'index'])->name('index');
        Route::put('/message-templates', [CommunicationChannelController::class, 'updateMessageTemplates'])->name('message-templates.update');
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

    Route::middleware('can:manage_admission_periods')->prefix('admission-periods')->name('admission-periods.')->group(function () {
        Route::get('/', [AdmissionPeriodController::class, 'index'])->name('index');
        Route::get('/create', [AdmissionPeriodController::class, 'create'])->name('create');
        Route::post('/', [AdmissionPeriodController::class, 'store'])->name('store');
        Route::get('/{admissionPeriod}/edit', [AdmissionPeriodController::class, 'edit'])->name('edit');
        Route::put('/{admissionPeriod}', [AdmissionPeriodController::class, 'update'])->name('update');
        Route::post('/{admissionPeriod}/toggle', [AdmissionPeriodController::class, 'toggle'])->name('toggle');
    });

    Route::middleware('can:manage_programs')->prefix('programs')->name('programs.')->group(function () {
        Route::get('/', [ProgramController::class, 'index'])->name('index');
        Route::get('/create', [ProgramController::class, 'create'])->name('create');
        Route::post('/', [ProgramController::class, 'store'])->name('store');
        Route::get('/{program}/edit', [ProgramController::class, 'edit'])->name('edit');
        Route::put('/{program}', [ProgramController::class, 'update'])->name('update');
        Route::post('/{program}/toggle', [ProgramController::class, 'toggle'])->name('toggle');
    });

    Route::middleware('can:manage_document_requirements')->prefix('document-requirements')->name('document-requirements.')->group(function () {
        Route::get('/', [DocumentRequirementController::class, 'index'])->name('index');
        Route::get('/create', [DocumentRequirementController::class, 'create'])->name('create');
        Route::post('/', [DocumentRequirementController::class, 'store'])->name('store');
        Route::get('/{documentRequirement}/edit', [DocumentRequirementController::class, 'edit'])->name('edit');
        Route::put('/{documentRequirement}', [DocumentRequirementController::class, 'update'])->name('update');
        Route::post('/{documentRequirement}/toggle', [DocumentRequirementController::class, 'toggle'])->name('toggle');
    });

    Route::middleware('can:manage_admin_users')->prefix('admin-users')->name('admin-users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/create', [AdminUserController::class, 'create'])->name('create');
        Route::post('/', [AdminUserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
        Route::post('/{user}/toggle', [AdminUserController::class, 'toggle'])->name('toggle');
        Route::post('/{user}/password-reset', [AdminUserController::class, 'passwordReset'])->name('password-reset');
        Route::post('/{user}/invitation-resend', [AdminUserController::class, 'resendInvitation'])->name('invitation-resend');
    });

    Route::middleware('can:manage_admin_users')->prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RolePermissionController::class, 'index'])->name('index');
        Route::get('/{role}/permissions/edit', [RolePermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('/{role}/permissions', [RolePermissionController::class, 'update'])->name('permissions.update');
    });

    // Master Pengaturan Wawancara (Superadmin only)
    Route::middleware('can:interview.manage_master')->group(function () {
        Route::resource('interview-batches', InterviewBatchController::class)->except(['show', 'create', 'edit']);
        Route::get('interview-stages', [InterviewStageController::class, 'index'])->name('interview-stages.index');
        Route::put('interview-stages/{interviewStage}', [InterviewStageController::class, 'update'])->name('interview-stages.update');

        Route::post('interview-stages/{interviewStage}/criteria', [InterviewCriterionController::class, 'store'])->name('interview-criteria.store');
        Route::put('interview-criteria/{interviewCriterion}', [InterviewCriterionController::class, 'update'])->name('interview-criteria.update');
        Route::delete('interview-criteria/{interviewCriterion}', [InterviewCriterionController::class, 'destroy'])->name('interview-criteria.destroy');
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
