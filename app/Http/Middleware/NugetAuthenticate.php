<?php

namespace Laget\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Laget\Http\Requests\NugetRequest;

class NugetAuthenticate {
    /**
     * @var NugetRequest
     */
    private $nugetRequest;

    /**
     * NugetAuthenticate constructor.
     *
     * @param NugetRequest $nugetRequest
     */
    public function __construct(NugetRequest $nugetRequest)
    {
        $this->nugetRequest = $nugetRequest;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $this->nugetRequest->getUser();

        if ($user === null)
        {
            return response('Unauthorized.', 400);
        }

        return $next($request);
    }
}
