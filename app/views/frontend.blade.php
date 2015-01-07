<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ Config::get('laget.name') }}</title>

    <title>Punktlich</title>

    <meta name="viewport" content="width=device-width, minimum-scale=1.0, initial-scale=1.0, user-scalable=no">

    <!-- web app settings -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ Config::get('laget.shortname') }}">

    <!-- web components -->
    <script src="/bower_components/webcomponentsjs/webcomponents.js"></script>

    <link rel="import" href="/bower_components/polymer/polymer.html">
    <link rel="import" href="/bower_components/core-toolbar/core-toolbar.html">
    <link rel="import" href="/bower_components/core-scroll-header-panel/core-scroll-header-panel.html">

    <link rel="import" href="/bower_components/paper-shadow/paper-shadow.html">
    <link rel="import" href="/bower_components/paper-icon-button/paper-icon-button.html">
    <link rel="import" href="/bower_components/paper-input/paper-input.html">
    <link rel="import" href="/bower_components/paper-button/paper-button.html">


    <!-- Angular dependencies -->
    <script src="/bower_components/angular/angular.js" type="text/javascript"></script>
    <script src="/bower_components/angular-route/angular-route.js" type="text/javascript"></script>
    {{--<script src="/bower_components/angular-resource/angular-resource.js" type="text/javascript"></script>--}}
    <script src="/bower_components/angular-ui-router/release/angular-ui-router.js" type="text/javascript"></script>
    <script src="/bower_components/restangular/dist/restangular.js" type="text/javascript"></script>
    <script src="/bower_components/underscore/underscore.js" type="text/javascript"></script>

    <!-- Main -->
    <link href="/css/main.css" rel="stylesheet">
    <script src="/app/app.js" type="text/javascript"></script>
    <script src="/app/routes.js" type="text/javascript"></script>

    <!-- Models -->
    <script src="/app/models/UserModel.js" type="text/javascript"></script>

    <!-- Controllers -->
    <script src="/app/controllers/MainController.js" type="text/javascript"></script>
    <script src="/app/controllers/HomeController.js" type="text/javascript"></script>
    <script src="/app/controllers/AuthenticationController.js" type="text/javascript"></script>

    <!-- Services -->
    <script src="/app/services/AuthenticationService.js" type="text/javascript"></script>
    <script src="/app/interceptors/AuthorizationInterceptor.js" type="text/javascript"></script>

    <base href="/">
</head>
<body ng-app="LaGet" ng-controller="MainController" fullbleed vertical layout>

<core-scroll-header-panel flex>
    <core-toolbar>
        <paper-icon-button ng-show="getParentState()" ng-click="goto(getParentState())" icon="chevron-left" title="back" role="button"></paper-icon-button>
        <div flex>{{'{'.'{'}} pageTitle {{'}'.'}'}}</div>

        <div ng-show="!!authorizedUser">
            {{'{'.'{'}} authorizedUser.email {{'}'.'}'}}
            <paper-icon-button ng-click="goto('laget.auth.logout')" icon="exit-to-app" title="back" role="button"></paper-icon-button>
        </div>
        <div ng-show="!authorizedUser">
            login|register
        </div>
    </core-toolbar>

    <div vertical layout ui-view flex class="content"></div>
</core-scroll-header-panel>



<paper-toast text="U'r so toasted"></paper-toast>


</body>
</html>