<?php

class NuGetAPIV2Controller extends ApiController
{
    public function index()
    {
        return Response::make('ready', 200);
    }

    const XMLNS_NS = 'http://www.w3.org/2000/xmlns/';


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

        $packages = NuGetPackageProvider::query(Input::get('$filter'),
            Input::get('$orderby'), Input::get('$top'), Input::get('$skip'));

        $count = count($packages);

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

        $select = Input::has('$select') ? array_filter(array_map('trim', explode(',', Input::get('$select'))), function ($prop) {
            return array_has(NuGetPackageProvider::$fieldMappings, $prop);
        }) : array_keys(NuGetPackageProvider::$fieldMappings);

        if (in_array('Version', $select)) array_push($select, 'Version');

        //$packages=[];
        foreach ($packages as $package) {
            $entry = $document->createElement('entry');

            $package_url = route('nuget.api.v2.package',
                ['id' => $package->package_id, 'version' => $package->version]
            );
            $entry->appendChild($document->createElement('id', $package_url));
            //$entry->appendChild($document->createElement('id', 'http://localhost/api/v2/Packages(Id=\'SampSharp.GameMode\',Version=\'0.2.0.0\')'));

            $category = $document->createElement('category');
            $category->setAttribute('term', 'NuGetGallery.V2FeedPackage');
            $category->setAttribute('scheme', 'http://schemas.microsoft.com/ado/2007/08/dataservices/scheme');
            $entry->appendChild($category);

            $editLinkElement = $document->createElement('link');
            $editLinkElement->setAttribute('rel', 'edit');
            $editLinkElement->setAttribute('title', 'V2FeedPackage');
            $editLinkElement->setAttribute('href', 'Packages(Id=\'' . $package->package_id . '\',Version=\'' . $package->version . '\')');
            $entry->appendChild($editLinkElement);

            $titleElement = $document->createElement('title', $package->package_id);
            $titleElement->setAttribute('type', 'text');
            $entry->appendChild($titleElement);

            $summaryElement = $document->createElement('summary', 'SampSharp.GameMode');//$package->summary);
            $summaryElement->setAttribute('type', 'text');
            $entry->appendChild($summaryElement);

            $entry->appendChild($document->createElement('updated', $package->updated_at->format('Y-m-d\TH:i:s.000\Z')));

            $author = $document->createElement('author');
            $author->appendChild($document->createElement('name', 'Tim Potze'));
            $entry->appendChild($author);

            $editMediaLinkElement = $document->createElement('link');
            $editMediaLinkElement->setAttribute('rel', 'edit-media');
            $editMediaLinkElement->setAttribute('title', 'V2FeedPackage');
            $editMediaLinkElement->setAttribute('href', 'Packages(Id=\'' . $package->package_id . '\',Version=\'' . $package->version . '\')/$value');
            $entry->appendChild($editMediaLinkElement);

            $content = $document->createElement('content');
            $content->setAttribute('type', 'application/zip');
            $content->setAttribute('src', 'http://www.nuget.org/api/v2/package/Newtonsoft.Json/6.0.7');
            $entry->appendChild($content);
            //<content type="application/zip" src="http://www.nuget.org/api/v2/package/Newtonsoft.Json/6.0.7"/>

            $properties = $document->createElement('m:properties');

            Log::notice('processing properties', $select);
            foreach ($select as $property) {
                $mapping = NuGetPackageProvider::$fieldMappings[$property];

                $value = null;
                if (array_has($mapping, 'function')) {
                    $func = $mapping['function'];
                    $value = $package->$func();
                }
                if (array_has($mapping, 'field')) {
                    $field = $mapping['field'];
                    $value = $package->$field;
                }


                if ($property == 'DownloadCount' && $value === null) {
                    Log::warning('applied fix');
                    $value = 0;
                }

                $propertyElement = $document->createElement('d:' . $property, $this->castType($mapping, $value));
                if (array_has($mapping, 'type'))
                    $propertyElement->setAttribute('m:type', $mapping['type']);
                if ($value === null)
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
        Log::notice('return 1 for ' . $action);
        return 1;//$count
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

