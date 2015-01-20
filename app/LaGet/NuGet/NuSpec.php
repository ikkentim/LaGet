<?php namespace LaGet\NuGet;

use \SimpleXMLElement;

class NuSpec
{
    public $id;
    public $version;
    public $title;
    public $authors;
    public $owners;
    public $licenseUrl;
    public $projectUrl;
    public $iconUrl;
    public $requireLicenseAcceptance;
    public $developmentDependency;
    public $description;
    public $summary;
    public $releaseNotes;
    public $copyright;
    public $dependencies;
    public $minClientVersion;
    public $language;

    private function __construct()
    {

    }

    public function getPackageTargetPath()
    {
        return app_path() . "/storage/packages/{$this->id}.{$this->version}.nupkg";
    }

    public function apply($package)
    {
        $package->package_id = $this->id;
        $package->version = $this->version;
        $package->is_prerelease = false; //@todo
        $package->title = $this->title;
        $package->authors = $this->authors;
        $package->owners = $this->owners;
        $package->icon_url = $this->iconUrl;
        $package->license_url = $this->licenseUrl;
        $package->project_url = $this->projectUrl;
        $package->require_license_acceptance = $this->requireLicenseAcceptance;
        $package->development_dependency = $this->developmentDependency == 'true'; //@todo
        $package->description = $this->description;
        $package->summary = $this->summary;
        $package->release_notes = $this->releaseNotes;
        $package->dependencies = ''; //@todo
        $package->copyright = $this->copyright;
        $package->tags = ''; //@todo
        $package->is_absolute_latest_version = true; //@todo
        $package->is_latest_version = true; //@todo
        $package->is_listed = true;
        $package->version_download_count = 0;
        $package->min_client_version = $this->minClientVersion; //@todo
        $package->language = $this->language;
    }

    /**
     * Creates an instance of NuSpec from an xml string.
     *
     * @param $xml
     * @return null|NuSpec
     */
    public static function fromXML($xml)
    {
        $nuspec = new SimpleXMLElement($xml);

        if ($nuspec === null || $nuspec->metadata === null)
            return null;

        $spec = new NuSpec;

        $spec->id = (string)$nuspec->metadata->id;
        $spec->version = (string)$nuspec->metadata->version;
        $spec->title = (string)$nuspec->metadata->title;
        $spec->authors = (string)$nuspec->metadata->authors;
        $spec->owners = (string)$nuspec->metadata->owners;
        $spec->licenseUrl = (string)$nuspec->metadata->licenseUrl;
        $spec->projectUrl = (string)$nuspec->metadata->projectUrl;
        $spec->iconUrl = (string)$nuspec->metadata->iconUrl;
        $spec->requireLicenseAcceptance = (string)$nuspec->metadata->requireLicenseAcceptance;
        $spec->developmentDependency = (string)$nuspec->metadata->developmentDependency;
        $spec->description = (string)$nuspec->metadata->description;
        $spec->summary = (string)$nuspec->metadata->summary;
        $spec->releaseNotes = (string)$nuspec->metadata->releaseNotes;
        $spec->copyright = (string)$nuspec->metadata->copyright;
//        $spec->minClientVersion = $nuspec->metadata->minClientVersion;
        $spec->language = $nuspec->metadata->language;

        // dependencies processor
        $dependenciesElement = $nuspec->metadata->dependencies;

        if ($dependenciesElement) {
            $dependencies = [];

            // process v1 types
            $v1Deps = is_array($dependenciesElement->dependency)
                ? $dependenciesElement->dependency
                : [$dependenciesElement->dependency];

            foreach ($v1Deps as $dep) {
                if (!isset($dep['id'])) continue;
                array_push($dependencies, [
                    'id' => (string)$dep['id'],
                    'targetFramework' => null,
                    'version' => isset($dep['version'])
                        ? (string)$dep['version']
                        : null
                ]);
            }

            $v2DepGroups = is_array($dependenciesElement->group)
                ? $dependenciesElement->group
                : [$dependenciesElement->group];

            foreach ($v2DepGroups as $depGroup) {
                $targetFramework = isset($dep['targetFramework'])
                    ? (string)$dep['targetFramework']
                    : null;

                $v2Deps = is_array($depGroup->dependency)
                    ? $depGroup->dependency
                    : [$depGroup->dependency];

                foreach ($v2Deps as $dep) {
                    if (!isset($dep['id'])) continue;
                    array_push($dependencies, [
                        'id' => (string)$dep['id'],
                        'targetFramework' => $targetFramework,
                        'version' => isset($dep['version'])
                            ? (string)$dep['version']
                            : null
                    ]);
                }
            }

            $dependenciesString = implode('|', array_map(function($dependency){
                return "{$dependency['id']}:{$dependency['version']}:{$dependency['targetFramework']}";
            }, $dependencies));

            $spec->dependencies = $dependenciesString;
        }

        return empty($spec->id) || empty($spec->version) ? null : $spec;
    }

    /**
     * Creates an instance of NuSpec from a .nuspec file.
     *
     * @param $filename string Path to .nuspec file.
     * @return null|NuSpec The function returns the created instance of NuSpec.
     */
    public static function fromNuSpec($filename)
    {
        $nuSpecContents = file_get_contents($filename);

        return $nuSpecContents === false ? null : self::fromXML($nuSpecContents);
    }

    /**
     * Creates an instance of NuSpec from a NuPkg instance.
     *
     * @param $nuPkg NuPkg Instance of NuPkg on which to base the NuSpec instance.
     * @return null|NuSpec The function returns the created instance of NuSpec.
     */
    public static function fromNuPkg($nuPkg)
    {
        $nuSpecContents = $nuPkg->getNuSpecFile();

        return $nuSpecContents === false ? null : self::fromXML($nuSpecContents);
    }
}