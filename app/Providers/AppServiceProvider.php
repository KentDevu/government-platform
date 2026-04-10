<?php

namespace App\Providers;

use App\Http\Middleware\AdminDeviceMiddleware;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Gate::policy(User::class, UserPolicy::class);

        // Share isAdminDevice with the main layout
        View::composer('layouts.app', function ($view) {
            $view->with('isAdminDevice', AdminDeviceMiddleware::isAdminDevice(request()));
        });

        // API rate limiters
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by(
            $request->user()?->id ?: $request->ip(),
        ));

        RateLimiter::for('api-auth', fn (Request $request) => Limit::perMinute(5)->by(
            $request->ip(),
        ));
    }
}
