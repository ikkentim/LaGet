<?php
Response::macro('xml', function ($xml, $status = 200, array $header = []) {
    if (is_null($xml))
        $xml = new DOMDocument('1.0', 'utf-8');

    if (empty($header)) {
        $header['Content-Type'] = 'application/atom+xml;type=feed;charset=utf-8';
    }
    return Response::make($xml->saveXML(), $status, $header);
});
