<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OngoingDevelopment
{
    /**
     * Handle an incoming request.
     * If this middleware is applied to a route, show the
     * Ongoing Development page instead of proceeding.
     */
    public function handle(Request $request, Closure $next)
    {
        return response()->view('ongoing-development');
    }
}
