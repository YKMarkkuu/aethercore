<?php

namespace App\Providers;

use App\Models\Space;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // partials.sidebar-left renders on every page (see layouts/app.blade.php),
        // so its "Your Spaces" list needs $mySpaces available regardless
        // of which controller/route actually handled the request.
        // partials.sidebar-right also needs it now, for the real
        // Friends • Spaces count on the default profile-card view.
        View::composer(['partials.sidebar-left', 'partials.sidebar-right'], function ($view) {
            $view->with('mySpaces', Auth::check()
                ? Space::whereHas('members', fn ($q) => $q->where('user_id', Auth::id()))->get()
                : collect());
        });

        View::composer('partials.settings-content', function ($view) {
            $sessions = collect();
            if (Auth::check() && config('session.driver') === 'database') {
                $sessions = \Illuminate\Support\Facades\DB::table('sessions')
                    ->where('user_id', Auth::id())
                    ->orderByDesc('last_activity')
                    ->get();
            }

            $blockedUsers = Auth::check()
                ? Auth::user()->blockedUsers()->get(['users.id', 'users.name', 'users.username'])
                : collect();

            $view->with([
                'sessionDriverSupported' => config('session.driver') === 'database',
                'settingsSessions' => $sessions,
                'currentSessionId' => session()->getId(),
                'settingsBlockedUsers' => $blockedUsers,
            ]);
        });

        View::composer('layouts.admin', function ($view) {
            $view->with('pendingReportsCount', Auth::check() && Auth::user()->isAdmin()
                ? \App\Models\Report::where('status', 'pending')->count()
                : 0);
        });
    }
}