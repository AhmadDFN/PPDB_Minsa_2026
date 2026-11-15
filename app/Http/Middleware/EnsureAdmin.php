<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\AccountType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->account_type !== AccountType::ADMIN) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin mengakses area administrator.');
        }

        if (! $user->hasAnyRole(['superadmin', 'admin_ppdb'])) {
            abort(403, 'Akses ditolak. Peran administrator tidak mencukupi.');
        }

        return $next($request);
    }
}
