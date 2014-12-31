<?php

class AtomFeed extends AtomElement
{
    function __construct($document, $id, $title, $updated_date)
    {
        parent::__construct($document, 'feed', $id, $title, $updated_date);
    }

    static function Generate()
    {
        $document = new DOMDocument('1.0', 'UTF-8');

        $feed = new AtomFeed($document, 'id', 'title', time());

        return Response::make((string)$feed, 200, ['Content-Type' => 'application/xml']);
    }
}