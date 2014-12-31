<?php

class NuGetUploadController extends ApiController
{
    public function upload()
    {
        $user = $this->getApiUser();

        if ($user == null)
            return Response::make('invalid api key', 403);

        $file = RequestUtil::getUploadedFile('package');

        if ($file === false)
            return Response::make('invalid package', 400);

        $nuPkg = new NuPkg($file);
        $package = $nuPkg->savePackage($user);

        Log::notice('A package has been uploaded.', $package->toArray());
        return Response::make('OK');
    }
}