<?php

class NuGetDownloadController extends ApiController {
    public function download($id, $version)
    {
        $package = NuGetPackageRevision::where('package_id', $id)
            ->where('version', $version)
            ->orderBy('id', 'DESC')
            ->first();

        if($package === null)
        {
            return Response::make('not found', 404);
        }

        return Response::download($package->getNuPkgPath());
    }

}