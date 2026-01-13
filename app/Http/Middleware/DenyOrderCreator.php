<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DenyOrderCreator
{
    /**
     * Block order_creator role from accessing routes this middleware protects.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isOrderCreator()) {
            abort(403, 'Only administrators or staff can access this page.');
        }

        return $next($request);
    }
}
