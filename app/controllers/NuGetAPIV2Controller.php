<?php

use \LaGet\NuGet\PackageRepository;

class NuGetApiV2Controller extends ApiController
{
    const XMLNS_NS = 'http://www.w3.org/2000/xmlns/';

    public function index()
    {
        $inlinecount = Input::has('$inlinecount') ? Input::get('$inlinecount') : null;

        $packages = PackageRepository::query(Input::get('$filter'),
            Input::get('$orderby'), Input::get('$top'), Input::get('$skip'));

        $count = count($packages);

        $select = Input::has('$select') ? array_filter(array_map('trim', explode(',', Input::get('$select'))), function ($prop) {
            return array_has(PackageRepository::$fieldMappings, $prop);
        }) : array_keys(PackageRepository::$fieldMappings);

        $atom = new \LaGet\NuGet\AtomElement('feed', route('nuget.api.v2.packages'), 'Packages', time());
        $atom->addLink('self', 'Packages', 'Packages');
        if ($inlinecount != null)
            $atom->setCount($count);

        foreach ($packages as $package) {
            $entry = with(new \LaGet\NuGet\AtomElement('entry', $package->getApiUrl(), $package->package_id, $package->updated_at, $package->package_id))
                ->addLink('edit', 'V2FeedPackage', $package->getApiQuery())
                ->addLink('edit-media', 'V2FeedPackage', $package->getApiQuery() . '/$value')
                ->setCategory('NuGetGallery.V2FeedPackage', 'http://schemas.microsoft.com/ado/2007/08/dataservices/scheme')
                ->addAuthor('Tmp')
                ->setContent('application/zip', $package->getDownloadUrl());

            $atom->appendChild($entry);

            foreach ($select as $property) {
                $mapping = PackageRepository::$fieldMappings[$property];

                $value = null;
                if (array_has($mapping, 'function')) {
                    $func = $mapping['function'];
                    $value = $package->$func();
                }
                if (array_has($mapping, 'field')) {
                    $field = $mapping['field'];
                    $value = $package->$field;
                }

                $entry->addProperty($property, $this->castType($mapping, $value), array_has($mapping, 'type') ? $mapping['type'] : null);
            }
        }
        return Response::make($atom->render(route('nuget.api.v2')), 200);
    }

    private function castType($mapping, $value)
    {
        if ($value === null)
            return null;

        if (!array_has($mapping, 'type'))
            return (string)$value;

        switch ($mapping['type']) {
            case 'Edm.DateTime':
                return $value->format('Y-m-d\TH:i:s.000\Z');
            case 'Edm.Boolean':
                return $value == true ? 'true' : 'false';
            default:
                return $value;
        }
    }

    public function package($id, $version)
    {
        Log::notice('Request to package()', [$id, $version]);
    }

    public function packages()
    {
        Log::notice('Request to packages()', Input::all());

        $inlinecount = null;
        if (Input::has('$inlinecount'))
            $inlinecount = Input::get('$inlinecount');

        $packages = PackageRepository::query(Input::get('$filter'),
            Input::get('$orderby'), Input::get('$top'), Input::get('$skip'));

        $count = count($packages);

        $select = Input::has('$select') ? array_filter(array_map('trim', explode(',', Input::get('$select'))), function ($prop) {
            return array_has(PackageRepository::$fieldMappings, $prop);
        }) : array_keys(PackageRepository::$fieldMappings);

        //if (in_array('Version', $select)) array_push($select, 'Version');//wtf?

        // render
        $document = new DOMDocument('1.0', 'utf-8');
        $document->formatOutput = true;
        $feed = $document->appendChild($document->createElement('feed'));

        $feed->setAttributeNS(self::XMLNS_NS, 'xmlns', 'http://www.w3.org/2005/Atom');
        $feed->setAttributeNS(self::XMLNS_NS, 'xmlns:d', 'http://schemas.microsoft.com/ado/2007/08/dataservices');
        $feed->setAttributeNS(self::XMLNS_NS, 'xmlns:m', 'http://schemas.microsoft.com/ado/2007/08/dataservices/metadata');

        $feed->setAttribute('xml:base', route('nuget.api.v2'));

        if ($inlinecount != null)
            $feed->appendChild($document->createElement('m:count', $count));
        $feed->appendChild($document->createElement('id', route('nuget.api.v2.packages')));

        $titleElement = $document->createElement('title', 'Packages');
        $titleElement->setAttribute('type', 'text');
        $feed->appendChild($titleElement);

        $feed->appendChild($document->createElement('updated', date('Y-m-d\TH:i:s\Z', time())));

        $linkElement = $document->createElement('link');
        $linkElement->setAttribute('rel', 'self');
        $linkElement->setAttribute('title', 'Packages');
        $linkElement->setAttribute('href', 'Packages');
        $feed->appendChild($linkElement);

        foreach ($packages as $package) {
            $entry = $document->createElement('entry');

            $package_url = route('nuget.api.v2.package',
                ['id' => $package->package_id, 'version' => $package->version]
            );
            $entry->appendChild($document->createElement('id', $package_url));

            $category = $document->createElement('category');
            $category->setAttribute('term', 'NuGetGallery.V2FeedPackage');
            $category->setAttribute('scheme', 'http://schemas.microsoft.com/ado/2007/08/dataservices/scheme');
            $entry->appendChild($category);

            $editLinkElement = $document->createElement('link');
            $editLinkElement->setAttribute('rel', 'edit');
            $editLinkElement->setAttribute('title', 'V2FeedPackage');
            $editLinkElement->setAttribute('href', $package->getApiQuery());
            $entry->appendChild($editLinkElement);

            $titleElement = $document->createElement('title', $package->package_id);
            $titleElement->setAttribute('type', 'text');
            $entry->appendChild($titleElement);

            $summaryElement = $document->createElement('summary', 'SampSharp.GameMode'); //$package->summary);
            $summaryElement->setAttribute('type', 'text');
            $entry->appendChild($summaryElement);

            $entry->appendChild($document->createElement('updated', $package->updated_at->format('Y-m-d\TH:i:s.000\Z')));

            $author = $document->createElement('author');
            $author->appendChild($document->createElement('name', 'Tim Potze'));
            $entry->appendChild($author);

            $editMediaLinkElement = $document->createElement('link');
            $editMediaLinkElement->setAttribute('rel', 'edit-media');
            $editMediaLinkElement->setAttribute('title', 'V2FeedPackage');
            $editMediaLinkElement->setAttribute('href', "{$package->getApiQuery()}/\$value");
            $entry->appendChild($editMediaLinkElement);

            $content = $document->createElement('content');
            $content->setAttribute('type', 'application/zip');
            $content->setAttribute('src', $package->getDownloadUrl());
            $entry->appendChild($content);

            $properties = $document->createElement('m:properties');

            Log::notice('processing properties', $select);
            foreach ($select as $property) {
                $mapping = PackageRepository::$fieldMappings[$property];

                $value = null;
                if (array_has($mapping, 'function')) {
                    $func = $mapping['function'];
                    $value = $package->$func();
                }
                if (array_has($mapping, 'field')) {
                    $field = $mapping['field'];
                    $value = $package->$field;
                }

                $propertyElement = $document->createElement('d:' . $property, $this->castType($mapping, $value));
                if (array_has($mapping, 'type'))
                    $propertyElement->setAttribute('m:type', $mapping['type']);
                if (empty($value) && !is_numeric($value))
                    $propertyElement->setAttribute('m:null', 'true');

                $properties->appendChild($propertyElement);

            }
            $entry->appendChild($properties);

            $feed->appendChild($entry);
        }
        return Response::xml($document);
    }

    public function search($action)
    {

        //@todo
        Log::notice('return 1 for ' . $action);
        return 1; //$count
    }

    public function metadata()
    {
        return Response::view('api.v2.metadata')
            ->header('Content-Type', 'application/xml');
    }
    /*
     * array (
      '$orderby' => 'DownloadCount desc',
      '$filter' => 'IsAbsoluteLatestVersion',
      '$skip' => '0',
      '$top' => '15',
      '$select' => 'Id,Version,Authors,DownloadCount,VersionDownloadCount,PackageHash,PackageSize,Published',
      '$inlinecount' => 'allpages',
    )
     */
    //api/v2/Packages()?$orderby=DownloadCount%20desc&$filter=IsAbsoluteLatestVersion&$skip=0&$top=15&$select=Id,Version,Authors,DownloadCount,VersionDownloadCount,PackageHash,PackageSize,Published&$inlinecount=allpages

}

/*
 * db:
 *
 *
 * Version
Title
Id
Author
IconUrl
LicenseUrl
ProjectUrl
DownloadCount
RequireLicenseAcceptance
Description
ReleaseNotes
Published
Dependencies
PackageHash
PackageHashAlgorithm
PackageSize
Copyright
Tags
IsAbsoluteLatestVersion
IsLatestVersion
Listed
VersionDownloadCount
References
TargetFramework
Summary
IsPreRelease
Owners
UserId
 */

