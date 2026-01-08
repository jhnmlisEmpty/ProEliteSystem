<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // If user doesn't have a branch and is not an admin, redirect or abort
            if (!$user->branch_id && !$user->isAdmin()) {
                abort(403, 'You must be assigned to a branch to access this resource.');
            }

            // Share current branch info with views
            view()->share('currentBranch', $user->branch);
            view()->share('canAccessAllBranches', $user->canAccessAllBranches());
        }

        return $next($request);
    }
}
