<?php
class AtomElement
{
    const XMLNS_NS = 'http://www.w3.org/2000/xmlns/';

    function __construct($document, $name, $id, $title, $updated_date)
    {
        $this->element = $document->createElement($name);

        $this->element->appendChild($document->createElement('id', $id));

        $titleElement = $document->createElement('title', $title);
        $titleElement->setAttribute('type', 'text');
        $this->element->appendChild($titleElement);

        $this->element->appendChild($document->createElement('updated', gmdate('Y-m-d\TH:i:s\Z', $updated_date)));


    }

    static function GenerateEE()
    {
        $document = new DOMDocument('1.0', 'UTF-8');

        $feed = $document->appendChild($document->createElement('feed'));

        $feed->setAttributeNS(XMLNS_NS, 'xmlns', 'http://www.w3.org/2005/Atom');
        $feed->setAttributeNS(XMLNS_NS, 'xmlns:d', 'http://schemas.microsoft.com/ado/2007/08/dataservices');
        $feed->setAttributeNS(XMLNS_NS, 'xmlns:m', 'http://schemas.microsoft.com/ado/2007/08/dataservices/metadata');

        $feed->setAttribute('xml:base', 'http://localhosts/api/v2/');


//http://www.w3.org/2005/Atom
        //<feed xmlns="http://www.w3.org/2005/Atom" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices"
        //xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata"
        //xml:base="http://www.nuget.org/api/v2/">


        //$channelNode = $feed->appendChild($document->createElement('channel'));
        //$channelNode->appendChild($document->createElement('title', 'Removed'));
        //$channelNode->appendChild($document->createElement('description', 'Removed'));
        //$channelNode->appendChild($document->createElement('link', 'Removed'));


        return Response::make($document->saveXML(), 200, ['Content-Type' => 'application/xml']);
    }
}