<?php
// Web Interface
Route::get('/', ['as' => 'home', 'uses' => 'HomeController@home']);

// NuGet
Route::group(array('before' => 'nuget.api'), function () {
    Route::put('/upload',                       ['as' => 'nuget.upload', 'uses' => 'NuGetUploadController@upload']);
    Route::get('/download/{id}/{version}',      ['as' => 'nuget.download', 'uses' => 'NuGetDownloadController@download']);

    // V1
    // @todo: V1

    // V2
    Route::get('/api/v2',                       ['as' => 'nuget.api.v2', 'uses' => 'NuGetApiV2Controller@index']);

    Route::get('/api/v2/$metadata',             ['as' => 'nuget.api.v2.metadata', 'uses' => 'NuGetApiV2Controller@metadata']);

    Route::get('/api/v2/Packages()',            ['as' => 'nuget.api.v2.packages', 'uses' => 'NuGetApiV2Controller@packages']);
    Route::get('/api/v2/Packages',              ['as' => 'nuget.api.v2.packages', 'uses' => 'NuGetApiV2Controller@packages']);

    Route::get('/api/v2/Search()/{action}',     ['as' => 'nuget.api.v2.search.action', 'uses' => 'NuGetApiV2Controller@search']);
    Route::get('/api/v2/Search()',              ['as' => 'nuget.api.v2.search', 'uses' => 'NuGetApiV2Controller@searchNoAction']);
    Route::get('/api/v2/Search',                ['as' => 'nuget.api.v2.search', 'uses' => 'NuGetApiV2Controller@searchNoAction']);

    Route::get('/api/v2/FindPackagesById()',    ['as' => 'nuget.api.v2.findById', 'uses' => 'NuGetApiV2Controller@packages']);

    Route::get('/api/v2/Packages(Id=\'{id}\',Version=\'{version}\')',       ['as' => 'nuget.api.v2.package', 'uses' => 'NuGetApiV2Controller@package']);
});