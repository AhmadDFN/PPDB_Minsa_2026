<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\AccountType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->account_type !== AccountType::APPLICANT) {
            abort(403, 'Halaman ini khusus untuk pendaftar calon siswa.');
        }

        return $next($request);
    }
}
