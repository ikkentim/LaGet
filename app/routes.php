<?php
// Web Interface
Route::get('/', ['as' => 'home', 'uses' => function () {
    return View::make('frontend');
}]);


Route::get('laget/api/v1', ['as' => 'laget.api.v1']);

Route::group(array('prefix' => 'laget/api'), function () {
    Route::group(array('prefix' => 'v1'), function () {
        Route::resource('users/auth', 'Tappleby\AuthToken\AuthTokenController', ['only' => ['index', 'store', 'destroy']]);
        Route::resource('users', 'UsersController', ['only' => ['store']]);
    });
});
Route::group(array('prefix' => 'laget/api', 'before' => 'auth.token'), function () {
    Route::group(array('prefix' => 'v1'), function () {
        //api things...
    });
});

// NuGet
Route::group(array('before' => 'nuget.api'), function () {
    Route::get('/download/{id}/{version}', ['as' => 'nuget.download', 'uses' => 'NuGetDownloadController@download']);
    Route::put('upload', ['as' => 'nuget.upload', 'uses' => 'NuGetUploadController@upload']);

    // V2
    Route::get('/api/v2', ['as' => 'nuget.api.v2', 'uses' => 'NuGetApiV2Controller@index']);

    Route::get('/api/v2/$metadata', ['as' => 'nuget.api.v2.metadata', 'uses' => 'NuGetApiV2Controller@metadata']);

    Route::get('/api/v2/Packages()', ['as' => 'nuget.api.v2.packages', 'uses' => 'NuGetApiV2Controller@packages']);
    Route::get('/api/v2/Packages', ['as' => 'nuget.api.v2.packages', 'uses' => 'NuGetApiV2Controller@packages']);

    Route::get('/api/v2/Search()/{action}', ['as' => 'nuget.api.v2.search.action', 'uses' => 'NuGetApiV2Controller@search']);
    Route::get('/api/v2/Search()', ['as' => 'nuget.api.v2.search', 'uses' => 'NuGetApiV2Controller@searchNoAction']);
    Route::get('/api/v2/Search', ['as' => 'nuget.api.v2.search', 'uses' => 'NuGetApiV2Controller@searchNoAction']);

    Route::get('/api/v2/FindPackagesById()', ['as' => 'nuget.api.v2.findById', 'uses' => 'NuGetApiV2Controller@packages']);

    Route::get('/api/v2/Packages(Id=\'{id}\',Version=\'{version}\')', ['as' => 'nuget.api.v2.package', 'uses' => 'NuGetApiV2Controller@package']);
});

App::error(function (AuthTokenNotAuthorizedException $exception) {
    if (Request::ajax()) {
        return Response::json(array('error' => $exception->getMessage()), $exception->getCode());
    }

    return Response::make($exception->getMessage(), $exception->getCode());
});