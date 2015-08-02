<?php

namespace Laget\Http\Controllers;

use DOMDocument;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Laget\Atom\AtomElement;
use Laget\Http\Requests;
use Laget\Http\Requests\NugetRequest;
use Laget\Nuget\NupkgFile;
use Laget\Repositories\NugetQueryBuilder;
use Laget\NugetPackage;

class ApiController extends Controller {
    private $queryBuilder;

    /**
     * ApiController constructor.
     *
     * @param NugetQueryBuilder $queryBuilder
     */
    public function __construct(NugetQueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Display the workspace contents.
     *
     * @return mixed
     */
    public function index()
    {
        $document = new DOMDocument('1.0', 'utf-8');
        $document->formatOutput = true;
        $service = $document->appendChild($document->createElement('service'));
        $workspace = $service->appendChild($document->createElement('workspace'));
        $workspace->appendChild($document->createElement('atom:title', 'Default'));
        $workspace->appendChild($document->createElement('collection'))
            ->appendChild($document->createElement('atom:title', 'Packages'));
        $service->setAttribute('xml:base', route('api.index'));
        $service->setAttributeNS(AtomElement::XMLNS_NS, 'xmlns', 'http://www.w3.org/2007/app');
        $service->setAttributeNS(AtomElement::XMLNS_NS, 'xmlns:atom', 'http://www.w3.org/2005/Atom');

        return Response::atom($document, 200, ['Content-Type' => 'application/xml;charset=utf-8']);
    }

    /**
     * Upload a package.
     *
     * @param NugetRequest $request
     * @return mixed
     */
    public function upload(NugetRequest $request)
    {
        $user = $request->getUser();
        $file = $request->getUploadedFile('package');

        Storage::makeDirectory('packages');
        $nupkg = new NupkgFile($file);
        $nupkg->savePackage($user);

        return Response::make('OK');
    }

    /**
     * Download a package.
     *
     * @param $id
     * @param $version
     * @return mixed
     */
    public function download($id, $version)
    {
        $package = NugetPackage::where('package_id', $id)
            ->where('version', $version)
            ->first();
        if ($package === null)
        {
            return Response::make('not found', 404);
        }
        $package->version_download_count++;
        $package->save();

        foreach (NugetPackage::where('package_id', $id)
                     ->get() as $vPackage)
        {
            $vPackage->download_count++;
            $vPackage->save();
        }

        return Response::download($package->getNupkgPath());
    }

    /**
     * Search and return a specific action.
     *
     * @param $action
     * @return mixed
     */
    public function search($action)
    {
        if ($action == 'count' || $action == '$count')
        {
            $count = $this->processSearchQuery()
                ->count();

            return $count;
        }
    }

    /**
     * Display search results.
     *
     * @return mixed
     */
    public function searchNoAction()
    {
        $eloquent = $this->processSearchQuery();
        $packages = $this->queryBuilder->limit($eloquent, Input::get('$top'), Input::get('$skip'))->get();

        $count = Input::has('$inlinecount') && Input::get('$inlinecount') == 'allpages' ? $eloquent->count()
            : count($packages);

        return $this->displayPackages($packages, route('api.search'), 'Search', time(), $count);
    }

    /**
     * Display the metadata of the API.
     *
     * @return mixed
     */
    public function metadata()
    {
        return Response::view('api.metadata')
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Display information about a package.
     *
     * @param $id
     * @param $version
     * @return mixed
     */
    public function package($id, $version)
    {
        /** @var NugetPackage $package */
        $package = NugetPackage::where('package_id', $id)
            ->where('version', $version)
            ->first();

        if ($package == null)
        {
            return $this->generateResourceNotFoundError('Packages');
        }

        $atomElement = $package->getAtomElement();
        $this->addPackagePropertiesToAtomElement($package, $this->queryBuilder->getAllProperties(), $atomElement);

        return Response::atom($atomElement->getDocument(route('api.index')));
    }

    /**
     * Display all packages.
     *
     * @return mixed
     */
    public function packages()
    {
        $eloquent = $this->queryBuilder->query(Input::get('$filter'), Input::get('$orderby'), trim(Input::get('id'), "' \t\n\r\0\x0B"));
        $packages = $this->queryBuilder->limit($eloquent, Input::get('$top'), Input::get('$skip'))
            ->get();

        $count = Input::has('$inlinecount') && Input::get('$inlinecount') == 'allpages' ? $eloquent->count()
            : count($packages);

        return $this->displayPackages($packages, route('api.packages'), 'Packages', time(), $count);
    }

    /**
     * Display all available updates.
     *
     * @return mixed
     */
    public function updates()
    {
        // Read input.
        $package_ids = explode('|', trim(Input::get('packageIds'), "'"));
        $package_versions = explode('|', trim(Input::get('versions'), "'"));
        $include_prerelease = Input::get('includePrerelease') === 'true';
        //$include_all_versions = Input::get('includeAllVersions') === 'true';//@todo ??
        //$version_constraints= explode('|', Input::get('versionConstraints'));//@todo ??
        //$target_frameworks= explode('|', Input::get('targetFrameworks'));//@todo ??

        if (count($package_ids) != count($package_versions))
        {
            return $this->generateError('Invalid version count', 'eu-US', 301);
        }

        // Query database.
        $packages = [];
        foreach ($package_ids as $index => $id)
        {
            $version = $package_versions[$index];
            $builder = NugetPackage::where('package_id', $id);
            if (!$include_prerelease)
            {
                $builder = $builder->where('is_prerelease', false);
            }
            $latest = $builder->orderBy('created_at', 'desc')
                ->first();
            if ($latest != null && $latest->version != $version)
            {
                array_push($packages, $latest);
            }
        }

        return $this->displayPackages($packages, route('api.updates'), 'GetUpdates', time(), count($packages));
    }

    private function generateError($message, $language = 'en-US', $status)
    {
        $document = new DOMDocument('1.0', 'utf-8');
        $document->formatOutput = true;
        $error = $document->appendChild($document->createElement('m:error'));
        $error->appendChild($document->createElement('m:code'));
        $error->appendChild($document->createElement('m:message', $message))
            ->setAttribute('xml:lang', $language);
        $error->setAttributeNS(AtomElement::XMLNS_NS, 'xmlns:m', 'http://schemas.microsoft.com/ado/2007/08/dataservices/metadata');

        return Response::atom($document, $status, ['Content-Type' => 'application/xml;charset=utf-8']);
    }

    private function generateResourceNotFoundError($segmentName)
    {
        return $this->generateError("Resource not found for the segment '$segmentName'.", 'en-US', 404);
    }

    /**
     * @param NugetPackage $package
     * @param array        $properties
     * @param AtomElement  $atomElement
     */
    private function addPackagePropertiesToAtomElement($package, $properties, $atomElement)
    {
        foreach ($properties as $property)
        {
            if (!$this->queryBuilder->isProperty($property))
            {
                continue;
            }

            $mapping = $this->queryBuilder->getMapping($property);

            if (array_has($mapping, 'function'))
            {
                $func = $mapping['function'];
                $value = $package->$func();
            }
            else
            {
                if (array_has($mapping, 'field'))
                {
                    $field = $mapping['field'];
                    $value = $package->$field;
                }
            }

            $atomElement->addProperty($property, $this->queryBuilder->castType($property, $value), array_has($mapping, 'type')
                ? $mapping['type'] : null);
        }
    }

    /**
     * Display a list of packages.
     *
     * @param        $packages
     * @param        $id
     * @param        $title
     * @param        $updated
     * @param  mixed $count
     * @return mixed
     */
    private function displayPackages($packages, $id, $title, $updated, $count = false)
    {
        $properties = Input::has('$select')
            ? array_filter(explode(',', Input::get('$select')), function ($name)
            {
                return $this->queryBuilder->isProperty($name);
            })
            : $this->queryBuilder->getAllProperties();

        $atom = with(new AtomElement('feed', $id, $title, $updated))
            ->addLink('self', $title, $title)
            ->setCount($count);

        /** @var NugetPackage $package */
        foreach ($packages as $package)
        {
            $atomElement = $package->getAtomElement();
            $this->addPackagePropertiesToAtomElement($package, $properties, $atomElement);
            $atom->appendChild($atomElement);
        }

        return Response::atom($atom->getDocument(route('api.index')));
    }

    /**
     * Build a query based on the input.
     *
     * @return mixed
     */
    private function processSearchQuery()
    {
        // Read input.
        //@todo: Improve search_term querying (split words?)
        $search_term = trim(Input::get('searchTerm', ''), '\' \t\n\r\0\x0B');
        $target_framework = Input::get('targetFramework');//@todo ;; eg. "'net45'"
        $include_prerelease = Input::get('includePrerelease') === 'true';

        // Query database.
        $eloquent = $this->queryBuilder->query(Input::get('$filter'), Input::get('$orderby'));

        if (!empty($search_term))
        {
            $eloquent = $eloquent->where(function ($query) use ($search_term)
            {
                $query->where('package_id', 'LIKE', "%$search_term%");
                $query->orWhere('title', 'LIKE', "%$search_term%");
                $query->orWhere('description', 'LIKE', "%$search_term%");
                $query->orWhere('summary', 'LIKE', "%$search_term%");
                $query->orWhere('tags', 'LIKE', "%$search_term%");
                $query->orWhere('authors', 'LIKE', "%$search_term%");
            });
        }
        if (!$include_prerelease)
        {
            $eloquent->where('is_prerelease', false);
        }

        return $eloquent;
    }
}
