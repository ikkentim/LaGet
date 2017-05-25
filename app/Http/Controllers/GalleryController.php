<?php

namespace Laget\Http\Controllers;

use Illuminate\View\View;
use Laget\NugetPackage;

class GalleryController extends Controller
{
    public function home()
    {
        return view('gallery.home')
            ->with('uniquePackages', NugetPackage::distinct('package_id')
                ->count('package_id'))
            ->with('totalDownloads', NugetPackage::sum('version_download_count'))
            ->with('totalPackages', NugetPackage::count());
    }

    /**
     * @return View
     */
    public function index()
    {
        $filter = request('by', 'most');

        switch ($filter) {
            case 'most':
                $packages = NugetPackage::where('is_absolute_latest_version', true)->orderBy('download_count', 'desc')->paginate(30);
                break;
            case 'least':
                $packages = NugetPackage::where('is_absolute_latest_version', true)->orderBy('download_count', 'asc')->paginate(30);
                break;
            case 'title':
                $packages = NugetPackage::where('is_absolute_latest_version', true)->orderBy('title')->paginate(30);
                break;
            default:
                $packages = NugetPackage::where('is_absolute_latest_version', true)->orderBy('download_count', 'desc')->paginate(30);
                break;
        }
        return view('gallery.index')->with('packages', $packages);
    }

    public function show($package)
    {
        return view('gallery.show')
            ->with('package', $package)
            ->with('versions', $package->versions());
    }
}
