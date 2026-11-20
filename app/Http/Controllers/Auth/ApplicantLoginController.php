<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ApplicantLoginRequest;
use App\Services\Authentication\NikAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApplicantLoginController extends Controller
{
    /**
     * Show applicant login form.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('applicant.dashboard');
        }

        return view('auth.applicant-login');
    }

    /**
     * Handle applicant authentication attempt.
     */
    public function store(
        ApplicantLoginRequest $request,
        NikAuthService $authService
    ): RedirectResponse {
        $result = $authService->authenticate(
            $request->validated('nik'),
            $request->validated('password'),
            $request->ip()
        );

        if (! $result['success']) {
            if ($result['code'] === 'PENDING_ACTIVATION' && $result['user']) {
                session(['activation_user_id' => $result['user']->id]);

                return redirect()->route('activation.form')
                    ->with('info', $result['message']);
            }

            return back()
                ->withInput($request->only('nik', 'remember'))
                ->withErrors(['nik' => $result['message']]);
        }

        // Authenticate via Laravel web guard
        Auth::guard('web')->login($result['user'], $request->boolean('remember'));

        // Prevent session fixation
        $request->session()->regenerate();

        if ($result['user']->must_change_password) {
            return redirect()->route('applicant.password.change')
                ->with('warning', 'Anda masuk menggunakan kata sandi sementara. Demi keamanan akun, silakan buat kata sandi baru terlebih dahulu.');
        }

        return redirect()->intended(route('applicant.dashboard'));
    }
}
