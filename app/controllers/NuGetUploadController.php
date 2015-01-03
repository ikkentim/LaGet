<?php
use LaGet\RequestUtil;
use LaGet\NuGet\NuPkg;

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
        $nuPkg->savePackage($user);

        return Response::make('OK');
    }
}