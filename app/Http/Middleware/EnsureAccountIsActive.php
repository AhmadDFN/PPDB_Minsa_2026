<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\AccountStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->account_status !== AccountStatus::ACTIVE) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $user->account_status === AccountStatus::SUSPENDED
                ? 'Akun Anda sedang dinonaktifkan oleh panitia. Silakan hubungi PPDB MIN 1 Kota Madiun.'
                : 'Akun Anda belum aktif. Silakan selesaikan proses aktivasi akun terlebih dahulu.';

            return redirect()->route('login')->with('error', $message);
        }

        return $next($request);
    }
}
