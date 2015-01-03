<?php namespace LaGet\NuGet;

use \DOMDocument;
use \DOMElement;

/**
 * Class AtomElement
 * @package LaGet\NuGet
 */
class AtomElement
{
    const XMLNS_NS = 'http://www.w3.org/2000/xmlns/';

    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $title;
    /**
     * @var int|mixed
     */
    private $updated;
    /**
     * @var null|string
     */
    private $summary;
    /**
     * @var null|int
     */
    private $count = null;
    /**
     * @var null|string
     */
    private $category = null;
    /**
     * @var null|string
     */
    private $categoryScheme = null;
    /**
     * @var array
     */
    private $authors = [];
    /**
     * @var null|string
     */
    private $contentType = null;
    /**
     * @var null|string
     */
    private $contentSrc = null;
    /**
     * @var array
     */
    private $links = [];
    /**
     * @var AtomElement[]
     */
    private $childElements = [];
    /**
     * @var array
     */
    private $properties = [];

    /**
     * @param string $type
     * @param string $id
     * @param string $title
     * @param int|mixed $updated
     * @param string $summary
     */
    function __construct($type, $id, $title, $updated, $summary = null)
    {
        $this->type = $type;
        $this->id = $id;
        $this->title = $title;
        $this->updated = $updated;
        $this->summary = $summary;
    }

    /**
     * @param string $rel
     * @param string $title
     * @param string $href
     * @return AtomElement
     */
    function addLink($rel, $title, $href)
    {
        array_push($this->links, ['rel' => $rel, 'title' => $title, 'href' => $href]);

        return $this;
    }

    /**
     * @param AtomElement $element
     * @return AtomElement
     */
    function appendChild($element)
    {
        array_push($this->childElements, $element);

        return $this;
    }

    /**
     * @param string $author
     * @return AtomElement $this
     */
    function addAuthor($author)
    {
        array_push($this->authors, $author);

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @param string|null $type
     * @return AtomElement
     */
    function addProperty($name, $value, $type = null)
    {
        array_push($this->properties, ['name' => $name, 'value' => $value, 'type' => $type]);

        return $this;
    }

    /**
     * @param int $count
     * @return AtomElement $this
     */
    function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @param string $category
     * @param string $scheme
     * @return AtomElement
     */
    function setCategory($category, $scheme)
    {
        $this->category = $category;
        $this->categoryScheme = $scheme;

        return $this;
    }

    /**
     * @param string $contentType
     * @param string $contentSrc
     * @return AtomElement
     */
    function setContent($contentType, $contentSrc)
    {
        $this->contentType = $contentType;
        $this->contentSrc = $contentSrc;

        return $this;
    }

    /**
     * @param DOMDocument $document
     * @return DOMElement
     */
    function createElementTree($document)
    {
        $self = $document->appendChild($document->createElement($this->type));

        // Data
        $self->appendChild($document->createElement('id', $this->id));
        $self->appendChild($document->createElement('title', $this->title)
            ->setAttribute('type', 'text'));
        $self->appendChild($document->createElement('summary', $this->summary)
            ->setAttribute('type', 'text'));

        $self->appendChild($document->createElement('updated', is_numeric($this->updated)
            ? date('Y-m-d\TH:i:s\Z', $this->updated) : $this->updated->format('Y-m-d\TH:i:s\Z')));

        if ($this->count !== null)
            $self->appendChild($document->createElement('m:count', $this->count));

        // Authors
        if (!empty($this->authors)) {
            $authorElement = $self->appendChild($document->createElement('author'));
            foreach ($this->authors as $author) {
                $authorElement->appendChild($document->createElement('name', $author));
            }
        }

        // Category
        if ($this->category !== null && $this->categoryScheme !== null) {
            $categoryElement = $document->createElement('category');
            $self->appendChild($categoryElement);

            $categoryElement->setAttribute('term', $this->category);
            $categoryElement->setAttribute('scheme', $this->categoryScheme);
        }

        // Links
        foreach ($this->links as $link) {
            $linkElement = $document->createElement('link');
            $self->appendChild($linkElement);

            foreach ($link as $key => $value)
                $linkElement->setAttribute($key, $value);
        }

        // Content
        if ($this->contentType !== null && $this->contentSrc !== null) {
            $contentElement = $document->createElement('content');
            $contentElement->setAttribute('type', $this->contentType);
            $contentElement->setAttribute('src', $this->contentSrc);

            $self->appendChild($contentElement);
        }

        // Child elements
        foreach ($this->childElements as $childElement) {
            $self->appendChild($childElement->createElementTree($document));
        }

        // Properties
        if (!empty($this->properties)) {
            $propertiesElement = $self->appendChild($document->createElement('m:properties'));

            foreach ($this->properties as $property) {
                $propertyElement = $document->createElement('d:' . $property['name'], $property['value']);
                $propertiesElement->appendChild($propertyElement);

                if ($property['type'] !== null)
                    $propertyElement->setAttribute('m:type', $property['type']);

                if (!is_numeric($property['value']) && empty($property['value']))
                    $propertyElement->setAttribute('m:null', 'true');
            }
        }
        return $self;
    }

    function render($base)
    {
        $document = new DOMDocument('1.0', 'utf-8');
        $document->formatOutput = true;

        $self = $this->createElementTree($document);
        $self->setAttributeNS(self::XMLNS_NS, 'xmlns', 'http://www.w3.org/2005/Atom');
        $self->setAttributeNS(self::XMLNS_NS, 'xmlns:d', 'http://schemas.microsoft.com/ado/2007/08/dataservices');
        $self->setAttributeNS(self::XMLNS_NS, 'xmlns:m', 'http://schemas.microsoft.com/ado/2007/08/dataservices/metadata');

        $self->setAttribute('xml:base', $base);


        return $document->saveXML();
    }
}