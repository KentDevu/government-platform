<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Middleware\AdminDeviceMiddleware;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Must be from an admin device AND authenticated as admin
        if (!AdminDeviceMiddleware::isAdminDevice($request)) {
            abort(404);
        }

        if (!auth()->check() || !auth()->user()->canAccessAdminPanel()) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
