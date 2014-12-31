<?php

class ApiController extends Controller {
    protected function getApiUser()
    {
        $apikey = Request::header('X-Nuget-Apikey');
        if (empty($apikey))
            return null;

        return User::fromApikey($apikey);
    }
}