<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowFrontendOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $theHeaders = getallheaders();
        $frontendUrl = $theHeaders['Origin'] ?? null;
        $frontendUrl = substr($frontendUrl, 0, strlen(env('APP_FRONTEND_URL'))); // BMD-ON-STAGING

        if (
            isset($frontendUrl)
            && $frontendUrl === env('APP_FRONTEND_URL')
        ) {
            return $next($request);
        }

        return response("BmdException: Bad Frontend URL.", 501);
    }
}
