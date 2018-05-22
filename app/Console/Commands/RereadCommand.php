<?php

namespace Laget\Console\Commands;

use Illuminate\Console\Command;
use Laget\NugetPackage;
use Laget\Nuget\NupkgFile;
use Laget\Nuget\NuspecFile;

class RereadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reread';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reread all packages';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $packages = NugetPackage::all();

        foreach ($packages as $package)
        {
            $this->info('package ' . $package->package_id . ' v' . $package->version);

            $path = $package->getNupkgPath();

            $nupkg = new NupkgFile($path);
            $nuspec = $nupkg->getNuspec();

            $change = false;

            if($package->description != $nuspec->description)
            {
                $this->info('New package description!'); 
                $change = true;
            }

            if($package->summary != $nuspec->summary)
            {
                $this->info('New package summary!'); 
                $change = true;
            }

            if($package->release_notes != $nuspec->releaseNotes)
            {
                $this->info('New package release_notes!'); 
                $change = true;
            }

            $package->description = $nuspec->description;
            $package->summary = $nuspec->summary;
            $package->release_notes = $nuspec->releaseNotes;

            if($change)
            {
                $package->save();
            }
        }
    }
}
