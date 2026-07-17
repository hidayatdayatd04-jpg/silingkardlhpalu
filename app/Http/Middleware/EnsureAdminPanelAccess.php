<?php

namespace App\Http\Middleware;

use App\Support\AdminAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPanelAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! AdminAccess::hasAnyPanelRole($request->user())) {
            abort(403, 'Akun Anda tidak memiliki akses admin.');
        }

        return $next($request);
    }
}
