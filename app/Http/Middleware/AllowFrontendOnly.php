<?php

namespace App\Http\Middleware;

use App\Bmd\Generals\GeneralHelper2;
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
        $envFrontendUrl = GeneralHelper2::getAppFrontendUrl();
        $frontendUrl = $theHeaders['Origin'] ?? null;
        $frontendUrl = substr($frontendUrl, 0, strlen($envFrontendUrl)); // BMD-ON-STAGING

        // For bmdworker, disregard the middleware.
        if (env('APP_SUBNAME', '') === 'bmdworker') {
            return $next($request);
        }
        

        if (
            isset($frontendUrl)
            && $frontendUrl === $envFrontendUrl
        ) {
            return $next($request);
        }

        return response("BmdException: Bad Frontend URL.", 501);
    }
}
