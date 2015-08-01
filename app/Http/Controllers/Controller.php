<?php

namespace Laget\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\App;
use Laget\Http\Requests\Request;
use Laget\User;

abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;
}
