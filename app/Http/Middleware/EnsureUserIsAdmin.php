<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Register as the 'admin' alias in Kernel.php and stack it after
     * 'auth' on any /admin route. Checks $user->isAdmin() (the role
     * column) — never a hardcoded username, so this stays correct if
     * more admins/moderators are added later.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
