<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminDeviceMiddleware
{
    /**
     * Only allow access from configured admin device IPs.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!self::isAdminDevice($request)) {
            abort(404);
        }

        return $next($request);
    }

    /**
     * Check if the current request comes from an admin device.
     */
    public static function isAdminDevice(Request $request): bool
    {
        $allowedIps = array_map('trim', explode(',', env('ADMIN_IPS', '127.0.0.1,::1')));

        return in_array($request->ip(), $allowedIps);
    }
}
