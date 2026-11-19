<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminLoginRequest;
use App\Services\Authentication\AdminAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    /**
     * Show administrator login screen.
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

        return view('auth.admin-login');
    }

    /**
     * Handle administrator authentication attempt.
     */
    public function store(
        AdminLoginRequest $request,
        AdminAuthenticationService $authService
    ): RedirectResponse {
        $result = $authService->authenticate(
            $request->validated('email'),
            $request->validated('password'),
            $request->ip()
        );

        if (! $result['success']) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => $result['message']]);
        }

        Auth::guard('web')->login($result['user'], $request->boolean('remember'));

        // Prevent session fixation
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }
}
