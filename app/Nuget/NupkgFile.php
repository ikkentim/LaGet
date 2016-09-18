<?php namespace Laget\Nuget;

use Chumper\Zipper\Zipper;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Laget\NugetPackage;

class NupkgFile {
    private $filename;
    private $isFileInStorage;

    public function __construct($filename, $isFileInStorage = false)
    {
        $this->filename = $filename;
        $this->isFileInStorage = $isFileInStorage;
    }

    private function getContents()
    {
        return $this->isFileInStorage ? Storage::get($this->filename) : file_get_contents($this->filename);
    }

    public function getSize()
    {
        return $this->isFileInStorage ? Storage::size($this->filename) : filesize($this->filename);
    }

    public function store($filename)
    {
        Storage::put($filename, $this->getContents());
        $this->filename = $filename;
    }

    public function getHash($algorithm)
    {
        return base64_encode(hash(strtolower($algorithm), file_get_contents($this->filename), true));
    }

    /**
     * Creates an instance of Nuspec from this NupkgFile instance.
     *
     * @return null|NuspecFile The function returns the created instance of Nuspec.
     */
    public function getNuspec()
    {
        return NuspecFile::fromNupkgFile($this);
    }

    /**
     * Gets the contents of the first *.nuspec file inside this .nupkg file.
     *
     * @return bool|string  The function returns the read data or false on failure.
     */
    public function getNuspecFileContent()
    {
        // List files in .nupkg file
        $zipper = new Zipper;
        $fileList = $zipper->zip($this->filename)
            ->listFiles();

        // List files in .nupkg with .nuspec extension
        $nuspecFiles = array_filter($fileList, function ($item)
        {
            return substr($item, -7) === '.nuspec';
        });

        // If no .nuspec files exist, return false
        if (count($nuspecFiles) == 0)
        {
            $zipper->close();

            return false;
        }

        // Return contents of zip file
        $contents = $zipper->getFileContent(array_shift($nuspecFiles));
        $zipper->close();

        return $contents;
    }

    public function savePackage($uploader)
    {
        if ($uploader === null)
        {
            return false;
        }

        // read specs
        $nuspec = $this->getNuspec();

        if ($nuspec === null)
        {
            return false;
        }

        $hash_algorithm = Config::get('laget.hash_algorithm');
        $hash_algorithm = strtoupper($hash_algorithm);

        // save or update
        $package = NugetPackage::where('package_id', $nuspec->id)
            ->where('version', $nuspec->version)
            ->first();

        if ($package === null)
        {
            $package = new NugetPackage();
        }

        // Apply specs to package revision
        $nuspec->apply($package);

        $package->is_absolute_latest_version = true;
        $package->is_listed = true;
        $package->is_prerelease = str_contains(strtolower($nuspec->version), ['alpha', 'beta', 'rc', 'prerelease']);
        $package->is_latest_version = !$package->is_prerelease;

        // Hash
        $package->hash = $this->getHash($hash_algorithm);
        $package->hash_algorithm = $hash_algorithm;
        $package->size = $this->getSize();

        // Set uploader
        $package->user_id = $uploader->id;

        // Move file
        $targetPath = $nuspec->getPackageTargetPath();
        $contents = file_get_contents($this->filename);
        Storage::put($targetPath, $contents);

        $this->filename = $targetPath;

        // notify older versions
        $absolute_latest_package = NugetPackage::where('package_id', $nuspec->id)
            ->where('is_absolute_latest_version', true)
            ->where('version', '!=', $package->version)
            ->first();

        if ($absolute_latest_package != null)
        {
            $absolute_latest_package->is_absolute_latest_version = false;
            $absolute_latest_package->save();

            $package->download_count = $absolute_latest_package->download_count;
        }
        else
        {
            $package->is_latest_version = true;
        }

        if (!$package->is_prerelease)
        {
            $latest_package = NugetPackage::where('package_id', $nuspec->id)
                ->where('is_latest_version', true)
                ->where('version', '!=', $package->version)
                ->first();

            if ($latest_package != null)
            {
                $latest_package->is_latest_version = false;
                $latest_package->save();
            }
        }

        $package->save();

        return $package;
    }
}