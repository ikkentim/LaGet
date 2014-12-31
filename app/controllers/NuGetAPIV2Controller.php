<?php

class NuGetAPIV2Controller extends BaseController
{
    private function createResponse($status, $statusName)
    {
        //@todo fix
        return Response::make($statusName, $status)
            ->header('Status', $status . ' ' . $statusName);

    }

    private function getApiUser()
    {
        $apikey = Request::header('X-Nuget-Apikey');
        if (empty($apikey))
            return null;

        return User::fromApikey($apikey);
    }

    private function GetUploadedFile($requestedName)
    {
        preg_match('/boundary=(.*)$/', Request::header('Content-Type'), $boundary);

        if (!count($boundary))
            return null;

        $blocks = preg_split("/-+{$boundary[1]}/", file_get_contents('php://input'));
        array_pop($blocks); // Last block is empty

        foreach ($blocks as $block) {
            if (empty($block) || strpos($block, 'application/octet-stream') === false)
                continue;

            preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $nameAndStream);

            $name = $nameAndStream[1];
            $stream = $nameAndStream[2];

            if ($requestedName != $name)
                continue;

            $check = unpack('C*', substr($stream, -2));
            if ($check[1] == 13 && $check[2] == 10)
                $stream = substr($stream, 0, -2);

            $tmpPath = tempnam(sys_get_temp_dir(), '');
            file_put_contents($tmpPath, $stream);

            return $tmpPath;
        }

        return null;
    }

    private function findNuspecFilePath($files)
    {
        foreach ($files as $file) {
            if (substr($file, -7) == ".nuspec")
                return $file;
        }

        return false;
    }

    public function index()
    {
        return Response::make('ready', 200);
    }
    public function upload()
    {
        /*
         * API key check.
         */
        $user = $this->getApiUser();

        if ($user == null)
            return $this->createResponse(403, 'Invalid API key');

        /*
         * File check
         */
        $file = $this->GetUploadedFile('package');
        if ($file == null)
            return $this->createResponse(400, 'invalid package');

        $size = filesize($file);
        if ($size <= 0)
            return $this->createResponse(400, 'invalid package');

        /*
         * File is OK at this point. Check the content
         */
        $zipper = new \Chumper\Zipper\Zipper;

        $contents = $zipper->zip($file)->listFiles();
        $nuspecPath = $this->findNuspecFilePath($contents);

        if ($nuspecPath === false) {
            $zipper->close();
            return $this->createResponse(400, 'package does not contain nuspec');
        }

        $nuspec = $zipper->getFileContent($nuspecPath);
        $zipper->close();

        $nuspec = new SimpleXMLElement($nuspec);

        if ($nuspec === null || $nuspec->metadata === null)
            return $this->createResponse(400, 'invalid nuspec');

        // Read metadata
        $id = (string)$nuspec->metadata->id;
        $version = (string)$nuspec->metadata->version;
        $title = (string)$nuspec->metadata->title;
        $authors = (string)$nuspec->metadata->authors;
        $owners = (string)$nuspec->metadata->owners;
        $licenseUrl = (string)$nuspec->metadata->licenseUrl;
        $projectUrl = (string)$nuspec->metadata->projectUrl;
        $iconUrl = (string)$nuspec->metadata->iconUrl;
        $requireLicenseAcceptance = (string)$nuspec->metadata->requireLicenseAcceptance;
        $developmentDependency = (string)$nuspec->metadata->developmentDependency;
        $description = (string)$nuspec->metadata->description;
        $summary = (string)$nuspec->metadata->summary;
        $releaseNotes = (string)$nuspec->metadata->releaseNotes;
        $copyright = (string)$nuspec->metadata->copyright;
        $dependencies = $nuspec->metadata->dependencies;
        $minClientVersion = $nuspec->metadata->minClientVersion;
        $language = $nuspec->metadata->language;

        if (empty($id) || empty($version))
            return $this->createResponse(400, 'invalid nuspec');

        $packagePath = app_path() . "/storage/packages/$id.$version.nupkg";
        rename($file, $packagePath);

        $hash_algorithm = strtoupper(Config::get('laget.hash_algorithm'));
        $hash = base64_encode(hash(strtolower($hash_algorithm), file_get_contents($packagePath), true));
        /*
         * Insert into database
         */
        $package = NuGetPackageRevision::where('package_id', $id)
            ->where('version', $version)
            ->first();

        if ($package == null)
            $package = new NuGetPackageRevision;

        $package->path = $packagePath;
        $package->package_id = $id;
        $package->version = $version;
        $package->is_prerelease = false; //@todo
        $package->title = $title;
        $package->authors = $authors;
        $package->owners = $owners;
        $package->icon_url = $iconUrl;
        $package->license_url = $licenseUrl;
        $package->project_url = $projectUrl;
        $package->require_license_acceptance = $requireLicenseAcceptance;
        $package->development_dependency = $developmentDependency == 'true'; //@todo
        $package->description = $description;
        $package->summary = $summary;
        $package->release_notes = $releaseNotes;
        $package->dependencies = ''; //@todo
        $package->hash = $hash;
        $package->hash_algorithm = $hash_algorithm;
        $package->size = $size;
        $package->copyright = $copyright;
        $package->tags = ''; //@todo
        $package->is_absolute_latest_version = true; //@todo
        $package->is_latest_version = true; //@todo
        $package->is_listed = true;
        $package->version_download_count = 0;
        $package->min_client_version = $minClientVersion; //@todo
        $package->language = $language;
        $package->user_id = $user->id;

        $package->save();

        Log::notice('A package has been uploaded.', $package->toArray());

        return $this->createResponse(200, 'OK');
    }

    const XMLNS_NS = 'http://www.w3.org/2000/xmlns/';

    protected $fieldMappings = [
        'Version' => ['field' => 'version'],
        'Title' => ['field' => 'title'],
        'Dependencies' => ['field' => 'dependencies'],
        'LicenseUrl' => ['field' => 'license_url'],
        'Copyright' => ['field' => 'copyright'],
        'DownloadCount' => ['field' => 'download_count', 'type' => 'Edm.Int32'],
        'Projecturl' => ['field' => 'project_url'],
        'RequireLicenseAcceptance' => ['field' => 'require_license_acceptance', 'type' => 'Edm.Boolean'],
        //GalleryDetailsUrl @todo
        'Description' => ['field' => 'description'],
        'ReleaseNotes' => ['field' => 'release_notes'],
        'PackageHash' => ['field' => 'hash'],
        'PackageHashAlgorithm' => ['field' => 'hash_algorithm'],
        'PackageSize' => ['field' => 'size', 'type' => 'Edm.Int64'],
        'Published' => ['field' => 'created_at', 'type' => 'Edm.DateTime'], //@todo
        //'Tags' => ['fields' => 'tags'], //@todo
        'IsLatestVersion' => ['field' => 'is_latest_version', 'type' => 'Edm.Boolean', 'isFilterable' => true],
        'VersionDownloadCount' => ['field' => 'version_download_count', 'type' => 'Edm.Int32'],
        'Summary' => ['field' => 'summary'],
        'IsAbsoluteLatestVersion' => ['field' => 'is_absolute_latest_version', 'type' => 'Edm.Boolean', 'isFilterable' => true], //@todo
        'Listed' => ['field' => 'is_listed', 'type' => 'Edm.Boolean'],
        'IconUrl' => ['field' => 'icon_url'],
    ];

    private function applyFilter($builder, $filter)
    {
        if (!array_has($this->fieldMappings, $filter)) {
            Log::warning($filter . ' not in mapping');
            return $builder;
        }

        $mapping = $this->fieldMappings[$filter];

        if (array_has($mapping, 'isFilterable') && $mapping['isFilterable'] === true) {
            return $builder->where($mapping['field'], true);
        }

        return $builder;
    }

    private function applyOrder($builder, $order)
    {
        $parts = explode(' ', $order, 2);

        $field = $parts[0];
        $order = count($parts) < 2 ? 'desc' : $parts[1];

        if (!array_has($this->fieldMappings, $field)) {
            Log::warning($field . ' not in mapping');
            return $builder;
        }
        $mapping = $this->fieldMappings[$field];

        Log::notice('ordering ' . $order . ' as ' . $mapping['field']);
        return $builder->orderBy($mapping['field'], $order);
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

        $builder = NuGetPackageRevision::where('is_listed', true);

        if (Input::has('$filter'))
            foreach (explode(',', Input::get('$filter')) as $filter)
                $builder = $this->applyFilter($builder, $filter);

        if (Input::has('$orderby'))
            foreach (explode(',', Input::get('$orderby')) as $order)
                $builder = $this->applyOrder($builder, $order);

        if (Input::has('$top'))
            $builder = $builder->take(Input::get('$top'));

        if (Input::has('$skip'))
            $builder = $builder->skip(Input::get('$skip'));

        $packages = $builder->get();

        $count = count($packages);

        // render
        $document = new DOMDocument('1.0', 'utf-8');
        $document->formatOutput = true;
        $feed = $document->appendChild($document->createElement('feed'));

        $feed->setAttributeNS(self::XMLNS_NS, 'xmlns', 'http://www.w3.org/2005/Atom');
        $feed->setAttributeNS(self::XMLNS_NS, 'xmlns:d', 'http://schemas.microsoft.com/ado/2007/08/dataservices');
        $feed->setAttributeNS(self::XMLNS_NS, 'xmlns:m', 'http://schemas.microsoft.com/ado/2007/08/dataservices/metadata');

        $feed->setAttribute('xml:base', route('nuget.api.v2'));

        if($inlinecount != null)
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
            return array_has($this->fieldMappings, $prop);
        }) : array_keys($this->fieldMappings);

        if(in_array('Version', $select)) array_push($select, 'Version');

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
            $editLinkElement->setAttribute('href', 'Packages(Id=\'' . $package->package_id . '\',Version=\'' . $package->version .'\')');
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
            $editMediaLinkElement->setAttribute('href', 'Packages(Id=\'' . $package->package_id . '\',Version=\'' . $package->version .'\')/$value');
            $entry->appendChild($editMediaLinkElement);

            $content = $document->createElement('content');
            $content->setAttribute('type', 'application/zip');
            $content->setAttribute('src', 'http://www.nuget.org/api/v2/package/Newtonsoft.Json/6.0.7');
            $entry->appendChild($content);
            //<content type="application/zip" src="http://www.nuget.org/api/v2/package/Newtonsoft.Json/6.0.7"/>

            $properties = $document->createElement('m:properties');

            Log::notice('processing properties', $select);
            foreach ($select as $property) {
                $mapping = $this->fieldMappings[$property];

                $value = null;
                if (array_has($mapping, 'function')) {
                    $func = $mapping['function'];
                    $value = $package->$func();
                }
                if (array_has($mapping, 'field')) {
                    $field = $mapping['field'];
                    $value = $package->$field;
                }


                if($property == 'DownloadCount' && $value === null) {
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

