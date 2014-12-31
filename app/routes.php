<?php
// Web Interface
Route::get('/', ['as' => 'home', 'uses' => 'HomeController@home']);

// NuGet
Route::put('/upload',               ['as' => 'nuget.upload', 'uses' => 'NuGetAPIV2Controller@upload']);

// NuGet.api.v2
Route::get('/api/v2',               ['as' => 'nuget.api.v2', 'uses' => 'NuGetAPIV2Controller@index']);
Route::get('/api/v2/$metadata',     ['as' => 'nuget.api.v2.metadata', 'uses' => 'NuGetAPIV2Controller@metadata']);
Route::get('/api/v2/Packages()',    ['as' => 'nuget.api.v2.packages', 'uses' => 'NuGetAPIV2Controller@packages']);
Route::get('/api/v2/Packages',      ['as' => 'nuget.api.v2.packages', 'uses' => 'NuGetAPIV2Controller@packages']);

Route::get('/api/v2/Search()/{action}',
                                    ['as' => 'nuget.api.v2.search', 'uses' => 'NuGetAPIV2Controller@search']);
Route::get('/api/v2/Search()',      ['as' => 'nuget.api.v2.search', 'uses' => 'NuGetAPIV2Controller@packages']);
Route::get('/api/v2/Search',        ['as' => 'nuget.api.v2.search', 'uses' => 'NuGetAPIV2Controller@packages']);
Route::get('/api/v2/Packages/$metadata',
                                    ['as' => 'nuget.api.v2.packages.metadata', 'uses' => 'NuGetAPIV2Controller@metadata']);//???
Route::get('/api/v2/Packages(Id=\'{id}\',Version=\'{version}\')',
                                    ['as' => 'nuget.api.v2.package', 'uses' => 'NuGetAPIV2Controller@package']);

//http://www.nuget.org/api/v2/Packages(Id='Newtonsoft.Json',Version='6.0.7')
// Misc
Route::get('test', function(){
    return AtomFeed::Generate();
});