<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordNotTemporary
{
    /**
     * Handle an incoming request.
     * Enforce mandatory password change for applicant accounts on temporary password.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isApplicant() && $user->must_change_password) {
            // Allow accessing the change password form, submitting change password, and logout
            if (! $request->routeIs('applicant.password.change*') && ! $request->routeIs('logout')) {
                return redirect()->route('applicant.password.change')
                    ->with('warning', 'Anda menggunakan kata sandi sementara. Demi keamanan akun, Anda wajib membuat kata sandi baru terlebih dahulu.');
            }
        }

        return $next($request);
    }
}
