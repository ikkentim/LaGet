<?php

namespace Laget\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class DebugRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Log::notice('Hit: ' . $request->url() . ' ?' . $_SERVER['QUERY_STRING']);
        return $next($request);
    }
}
