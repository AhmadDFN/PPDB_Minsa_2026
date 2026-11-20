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
     * Password change on first login is now optional per user requirement.
     * Applicants can access the dashboard and portal directly without mandatory redirection.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
