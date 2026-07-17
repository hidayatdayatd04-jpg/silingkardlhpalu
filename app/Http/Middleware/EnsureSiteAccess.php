<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('site_access_granted')) {
            return redirect()->route('access-gate.show');
        }

        return $next($request);
    }
}
