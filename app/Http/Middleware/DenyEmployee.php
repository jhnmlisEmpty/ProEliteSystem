<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DenyEmployee
{
    /**
     * Block employee role from accessing protected routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'employee') {
            abort(403, 'Employees can have no access to this section.');
        }

        return $next($request);
    }
}
