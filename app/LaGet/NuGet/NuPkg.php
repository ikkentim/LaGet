<?php namespace LaGet\NuGet;

class NuPkg
{
    public $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function getSize()
    {
        return filesize($this->filename);
    }

    public function getHash($algorithm)
    {
        return base64_encode(hash(strtolower($algorithm), file_get_contents($this->filename), true));
    }
    /**
     * Creates an instance of NuSpec from this NuPkg instance.
     *
     * @return null|NuSpec The function returns the created instance of NuSpec.
     */
    public function getNuSpec()
    {
        return NuSpec::fromNuPkg($this);
    }

    /**
     * Gets the contents of the first *.nuspec file inside this .nupkg file.
     * @return bool|string  The function returns the read data or false on failure.
     */
    public function getNuSpecFile()
    {
        // List files in .nupkg file
        $zipper = new \Chumper\Zipper\Zipper;
        $fileList = $zipper->zip($this->filename)
            ->listFiles();

        // List files in .nupkg with .nuspec extension
        $nuspecFiles = array_filter($fileList, function ($item) {
            return substr($item, -7) === '.nuspec';
        });

        // If no .nuspec files exist, return false
        if (count($nuspecFiles) == 0) {
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
        if($uploader === null)
            return false;

        // Read specs
        $nuSpec = $this->getNuSpec();

        if($nuSpec === null)
            return false;

        $hash_algorithm = strtoupper(Config::get('laget.hash_algorithm'));

        // save or update
        $package = NuGetPackageRevision::where('package_id', $nuSpec->id)
            ->where('version', $nuSpec->version)
            ->first();

        if($package === null)
            $package = new NuGetPackageRevision;

        // Apply specs to package revision
        $nuSpec->apply($package);

        // Hash
        $package->hash = $this->getHash($hash_algorithm);
        $package->hash_algorithm = $hash_algorithm;
        $package->size = $this->getSize();

        // Set uploader
        $package->user_id = $uploader->id;

        // Move file
        $targetPath = $nuSpec->getPackageTargetPath();
        rename($this->filename, $targetPath);
        $this->filename = $targetPath;

        $package->save();
        return $package;
    }
}