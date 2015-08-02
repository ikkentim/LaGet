<?php

namespace Laget\Http\Middleware;

use Closure;
use Laget\Http\Requests\NugetRequest;

class NugetFile
{
    /**
     * @var NugetRequest
     */
    private $nugetRequest;

    /**
     * NugetFile constructor.
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
     * @param  string                   $filename
     * @return mixed
     */
    public function handle($request, Closure $next, $filename)
    {
        if(!$this->nugetRequest->hasUploadedFile($filename))
        {
            return response('Mising package.', 400);
        }
        return $next($request);
    }
}
