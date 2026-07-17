<?php

namespace App\Http\Middleware;

use App\Models\WebsiteVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackWebsiteVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ! $request->is('admin*') && ! $request->is('api/*') && Schema::hasTable('website_visits')) {
            $sessionId = $request->session()->getId();
            $ip = $request->ip() ?? '0.0.0.0';

            WebsiteVisit::query()->firstOrCreate([
                'visit_date' => today()->toDateString(),
                'ip_address' => $ip,
                'session_id' => $sessionId,
            ]);
        }

        return $next($request);
    }
}
