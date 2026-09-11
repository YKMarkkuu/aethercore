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
    }
}