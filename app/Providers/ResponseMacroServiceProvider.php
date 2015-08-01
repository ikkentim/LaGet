<?php

namespace Laget\Providers;

use DOMDocument;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Routing\ResponseFactory;

class ResponseMacroServiceProvider extends ServiceProvider {
    /**
     * Perform post-registration booting of services.
     *
     * @param  ResponseFactory $factory
     * @return void
     */
    public function boot(ResponseFactory $factory)
    {
        $factory->macro('atom', function ($xml, $status = 200, array $header = []) use ($factory)
        {
            if (is_null($xml))
                $xml = new DOMDocument('1.0', 'utf-8');
            if (empty($header) || !array_has($header, 'Content-Type'))
                $header['Content-Type'] = 'application/atom+xml;type=feed;charset=utf-8';
            return $factory->make($xml->saveXML(), $status, $header);
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }
}