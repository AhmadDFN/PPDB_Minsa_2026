<?php

declare(strict_types=1);

use App\Enums\AdmissionPeriodStatus;
use App\Http\Controllers\Applicant\DashboardController;
use App\Http\Controllers\Applicant\RegistrationFormController;
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
        return redirect()->route('admin.preview');
    }

    return redirect()->route('applicant.dashboard');
})->name('dashboard');

// Protected Applicant Area
Route::middleware(['auth', 'active', 'applicant'])->prefix('pendaftar')->name('applicant.')->group(function () {
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
});

// Protected Administrator Area
Route::middleware(['auth', 'active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/preview', function () {
        return view('admin.preview');
    })->name('preview');

    Route::get('/dashboard', function () {
        return view('admin.preview');
    })->name('dashboard');
});
