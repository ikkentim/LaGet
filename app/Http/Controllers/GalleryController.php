<?php

namespace Laget\Http\Controllers;

use Illuminate\Http\Request;

use Laget\Http\Requests;
use Laget\Http\Controllers\Controller;
use Laget\NugetPackage;

class GalleryController extends Controller {
    public function home()
    {
        return view('gallery.home')
            ->with('uniquePackages', NugetPackage::distinct('package_id')
                ->count('package_id'))
            ->with('totalDownloads', NugetPackage::sum('download_count'))
            ->with('totalPackages', NugetPackage::count());
    }

    public function index()
    {
        $packages = NugetPackage::where('is_absolute_latest_version', true)
            ->orderBy('title')
            ->paginate(15);

        return view('gallery.index')
            ->with('packages', $packages);
    }

    public function show($package)
    {
        return view('gallery.show')
            ->with('package', $package)
            ->with('versions', $package->versions());
    }
}
