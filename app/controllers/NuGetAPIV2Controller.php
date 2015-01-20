<?php

use \LaGet\NuGet\PackageRepository;
use \LaGet\NuGet\AtomElement;

class NuGetApiV2Controller extends NuGetApiController
{
    private function generateError($message, $language = 'en-US', $status)
    {
        $document = new DOMDocument('1.0', 'utf-8');
        $document->formatOutput = true;

        $error = $document->appendChild($document->createElement('m:error'));
        $error->appendChild($document->createElement('m:code'));
        $error->appendChild($document->createElement('m:message', $message))
            ->setAttribute('xml:lang', $language);

        $error->setAttributeNS(AtomElement::XMLNS_NS, 'xmlns:m', 'http://schemas.microsoft.com/ado/2007/08/dataservices/metadata');

        return Response::xml($document, $status, ['Content-Type' => 'application/xml;charset=utf-8']);
    }

    private function generateResourceNotFoundError($segmentName)
    {
        return $this->generateError("Resource not found for the segment '$segmentName'.", 'en-US', 404);
    }

    public function index()
    {
        $document = new DOMDocument('1.0', 'utf-8');
        $document->formatOutput = true;

        $service = $document->appendChild($document->createElement('service'));
        $workspace = $service->appendChild($document->createElement('workspace'));

        $workspace->appendChild($document->createElement('atom:title', 'Default'));

        $workspace->appendChild($document->createElement('collection'))
            ->appendChild($document->createElement('atom:title', 'Packages'));


        $service->setAttribute('xml:base', route('nuget.api.v2'));

        $service->setAttributeNS(AtomElement::XMLNS_NS, 'xmlns', 'http://www.w3.org/2007/app');
        $service->setAttributeNS(AtomElement::XMLNS_NS, 'xmlns:atom', 'http://www.w3.org/2005/Atom');

        return Response::xml($document, 200, ['Content-Type' => 'application/xml;charset=utf-8']);
    }

    public function package($id, $version)
    {
        $package = NuGetPackageRevision::where('package_id', $id)
            ->where('version', $version)
            ->first();

        if ($package == null)
            return $this->generateResourceNotFoundError('Packages');

        $entry = with(new AtomElement('entry', $package->getApiUrl(), $package->package_id, $package->updated_at, $package->package_id))
            ->addLink('edit', 'V2FeedPackage', $package->getApiQuery())
            ->addLink('edit-media', 'V2FeedPackage', $package->getApiQuery() . '/$value')
            ->setCategory('NuGetGallery.V2FeedPackage', 'http://schemas.microsoft.com/ado/2007/08/dataservices/scheme')
            ->setContent('application/zip', $package->getDownloadUrl());

        foreach (explode(',', $package->authors) as $author)
            $entry->addAuthor(trim($author));

        foreach (PackageRepository::getAllProperties() as $property) {
            $mapping = PackageRepository::getMapping($property);

            $value = null;
            if (array_has($mapping, 'function')) {
                $func = $mapping['function'];
                $value = $package->$func();
            }
            if (array_has($mapping, 'field')) {
                $field = $mapping['field'];
                $value = $package->$field;
            }

            $entry->addProperty($property, PackageRepository::castType($property, $value), array_has($mapping, 'type') ? $mapping['type'] : null);
        }

        return Response::xml($entry->getDocument(route('nuget.api.v2')));
    }

    public function displayPackages($packages, $id, $title, $updated)
    {
        $inlinecount = Input::has('$inlinecount') ? Input::get('$inlinecount') : null;
        $select = Input::has('$select') ? array_filter(array_map('trim', explode(',', Input::get('$select'))), '\LaGet\NuGet\PackageRepository::isProperty') : PackageRepository::getAllProperties();

        $count = count($packages);

        $atom = new AtomElement('feed', $id, $title, $updated);
        $atom->addLink('self', $title, $title);
        if ($inlinecount != null)
            $atom->setCount($count);

        foreach ($packages as $package) {
            $entry = with(new AtomElement('entry', $package->getApiUrl(), $package->package_id, $package->updated_at, $package->package_id))
                ->addLink('edit', 'V2FeedPackage', $package->getApiQuery())
                ->addLink('edit-media', 'V2FeedPackage', $package->getApiQuery() . '/$value')
                ->setCategory('NuGetGallery.V2FeedPackage', 'http://schemas.microsoft.com/ado/2007/08/dataservices/scheme')
                ->setContent('application/zip', $package->getDownloadUrl());

            foreach (explode(',', $package->authors) as $author)
                $entry->addAuthor($author);

            $atom->appendChild($entry);

            foreach ($select as $property) {
                $mapping = PackageRepository::getMapping($property);

                $value = null;
                if (array_has($mapping, 'function')) {
                    $func = $mapping['function'];
                    $value = $package->$func();
                }
                if (array_has($mapping, 'field')) {
                    $field = $mapping['field'];
                    $value = $package->$field;
                }

                $entry->addProperty($property, PackageRepository::castType($property, $value), array_has($mapping, 'type') ? $mapping['type'] : null);
            }
        }

        return Response::xml($atom->getDocument(route('nuget.api.v2')));
    }

    public function packages()
    {
        $packages = PackageRepository::query(Input::get('$filter'),
            Input::get('$orderby'), Input::get('$top'), Input::get('$skip'))->get();

        return $this->displayPackages($packages, route('nuget.api.v2.packages'), 'Packages', time());
    }

    public function updates()
    {
        $package_ids = explode('|',  trim(Input::get('packageIds'), "'"));
        $package_versions = explode('|', trim(Input::get('versions'), "'"));
        $include_prerelease = Input::get('includePrerelease') === 'true';

        //$include_all_versions = Input::get('includeAllVersions') === 'true';//@todo ??
        //$version_constraints= explode('|', Input::get('versionConstraints'));//@todo ??
        //$target_frameworks= explode('|', Input::get('targetFrameworks'));//@todo ??


        if (count($package_ids) != count($package_versions)) {
            return $this->generateError('Invalid version count', 'eu-US', 301);
        }

        $packages = [];
        foreach($package_ids as $index => $id)
        {
            $version = $package_versions[$index];

            $builder = \NuGetPackageRevision::where('package_id', $id);

            if (!$include_prerelease)
                $builder = $builder->where('is_prerelease', false);

            $latest = $builder->orderBy('created_at', 'desc')
                ->first();

            if($latest != null && $latest->version != $version)
                array_push($packages, $latest);
        }

        return $this->displayPackages($packages, route('nuget.api.v2.updates'), 'GetUpdates', time());
    }

    private function processSearchQuery()
    {
        //@todo: Improve search_term querying (split words?)
        $search_term = trim(Input::get('searchTerm', ''), '\' \t\n\r\0\x0B');
        $target_framework = Input::get('targetFramework');//@todo ;; eg. "'net45'"
        $include_prerelease = Input::get('includePrerelease') === 'true';

        $builder = PackageRepository::query(Input::get('$filter'),
            Input::get('$orderby'), Input::get('$top'), Input::get('$skip'));
        if (!empty($search_term)) {
            $builder = $builder->where(function ($query) use ($search_term) {

                $query->where('package_id', 'LIKE', "%$search_term%");
                $query->orWhere('title', 'LIKE', "%$search_term%");
                $query->orWhere('description', 'LIKE', "%$search_term%");
                $query->orWhere('summary', 'LIKE', "%$search_term%");
                $query->orWhere('tags', 'LIKE', "%$search_term%");
                $query->orWhere('authors', 'LIKE', "%$search_term%");

            });
        }

        if (!$include_prerelease)
            $builder->where('is_prerelease', false);

        return $builder;
    }

    public function search($action)
    {
        if ($action == 'count' || $action == '$count') {
            $count = $this->processSearchQuery()->count();
            return $count;
        }
    }

    public function searchNoAction()
    {
        $packages = $this->processSearchQuery()->get();
        return $this->displayPackages($packages, route('nuget.api.v2.search'), 'Search', time());
    }

    public function metadata()
    {
        return Response::view('api.v2.metadata')
            ->header('Content-Type', 'application/xml');
    }
}
