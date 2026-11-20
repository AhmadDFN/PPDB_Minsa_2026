<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\ApplicantActivationController;
use App\Http\Controllers\Auth\ApplicantLoginController;
use App\Http\Controllers\Auth\ApplicantPasswordResetController;
use App\Http\Controllers\Auth\ApplicantRegisterController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

// === Public & Guest Applicant Routes ===
Route::middleware('guest')->group(function () {
    // Pendaftaran Awal
    Route::get('/daftar', [ApplicantRegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [ApplicantRegisterController::class, 'store'])->name('register.store');

    // Aktivasi Akun & Password Sementara
    Route::get('/aktivasi', [ApplicantActivationController::class, 'showOtpForm'])->name('activation.form');
    Route::post('/aktivasi/verifikasi', [ApplicantActivationController::class, 'verifyOtp'])->name('activation.verify');
    Route::post('/aktivasi/kirim-ulang', [ApplicantActivationController::class, 'resendOtp'])->name('activation.resend');
    Route::post('/aktivasi/kirim-ulang-password', [ApplicantActivationController::class, 'resendTemporaryPassword'])->name('activation.resend_password');
    Route::get('/aktivasi/buat-password', [ApplicantActivationController::class, 'showPasswordForm'])->name('activation.password.form');
    Route::post('/aktivasi/buat-password', [ApplicantActivationController::class, 'savePassword'])->name('activation.password.store');

    // Masuk / Login Calon Siswa (NIK + Password)
    Route::get('/login', [ApplicantLoginController::class, 'create'])->name('login');
    Route::post('/login', [ApplicantLoginController::class, 'store'])->name('login.store');

    // Pemulihan Kata Sandi (Lupa Password)
    Route::get('/lupa-password', [ApplicantPasswordResetController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/lupa-password', [ApplicantPasswordResetController::class, 'sendResetOtp'])->name('password.send');
    Route::get('/lupa-password/verifikasi', [ApplicantPasswordResetController::class, 'showVerifyOtpForm'])->name('password.reset.verify.form');
    Route::post('/lupa-password/verifikasi', [ApplicantPasswordResetController::class, 'verifyResetOtp'])->name('password.reset.verify');
    Route::post('/lupa-password/kirim-ulang', [ApplicantPasswordResetController::class, 'resendResetOtp'])->name('password.reset.resend');
    Route::get('/lupa-password/reset', [ApplicantPasswordResetController::class, 'showResetPasswordForm'])->name('password.reset.form');
    Route::post('/lupa-password/reset', [ApplicantPasswordResetController::class, 'resetPassword'])->name('password.reset.store');

    // Masuk / Login Administrator PPDB (Email + Password)
    Route::get('/admin/login', [AdminLoginController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AdminLoginController::class, 'store'])->name('admin.login.store');
});

// === Authenticated Logout Routes ===
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    Route::post('/admin/logout', [LogoutController::class, 'adminLogout'])->name('admin.logout');
});
