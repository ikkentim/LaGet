<?php
class NuGetDownloadController extends ApiController {
    public function download($id, $version)
    {
        $package = NuGetPackageRevision::where('package_id', $id)
            ->where('version', $version)
            ->first();

        if($package === null)
            return Response::make('not found', 404);

        $package->version_download_count++;
        Log::notice('vc++ > ' . $package->version_download_count);
        $package->save();

        foreach(NuGetPackageRevision::where('package_id', $id)->get() as $vPackage)
        {
            $vPackage->download_count++;
            Log::notice('c++ > ' . $vPackage->download_count);
            $vPackage->save();
        }

        return Response::download($package->getNuPkgPath());
    }

}