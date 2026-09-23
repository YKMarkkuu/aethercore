<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Registered in the global 'web' middleware group (see Kernel.php),
     * so it runs on every request for a logged-in user. A ban or an
     * active suspension takes effect immediately — the person is logged
     * out mid-session rather than waiting for it to expire naturally.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ($user->isBanned() || $user->isSuspended())) {
            $message = $user->isBanned()
                ? 'Your account has been banned.'
                : 'Your account is suspended until ' . $user->suspended_until->format('M j, Y g:i A') . '.';

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => $message]);
        }

        return $next($request);
    }
}
