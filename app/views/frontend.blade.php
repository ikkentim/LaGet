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
    <link rel="import" href="/bower_components/core-icons/core-icons.html">
    <link rel="import" href="/bower_components/core-toolbar/core-toolbar.html">
    <link rel="import" href="/bower_components/core-media-query/core-media-query.html">
    <link rel="import" href="/bower_components/core-menu/core-menu.html">
    <link rel="import" href="/bower_components/core-list/core-list.html">
    <link rel="import" href="/bower_components/core-item/core-item.html">
    <link rel="import" href="/bower_components/core-scaffold/core-scaffold.html">
    <link rel="import" href="/bower_components/core-pages/core-pages.html">
    <link rel="import" href="/bower_components/core-drawer-panel/core-drawer-panel.html">

    <link rel="import" href="/bower_components/paper-input/paper-input.html">
    <link rel="import" href="/bower_components/paper-button/paper-button.html">
    <link rel="import" href="/bower_components/paper-toast/paper-toast.html">
    <link rel="import" href="/bower_components/paper-icon-button/paper-icon-button.html">
    <link rel="import" href="/bower_components/paper-fab/paper-fab.html">
    <link rel="import" href="/bower_components/paper-icon-button/paper-icon-button.html">
    <link rel="import" href="/bower_components/paper-dropdown/paper-dropdown.html">
    <link rel="import" href="/bower_components/paper-dropdown-menu/paper-dropdown-menu.html">
    <link rel="import" href="/bower_components/paper-item/paper-item.html">
    <link rel="import" href="/bower_components/paper-dialog/paper-dialog.html">
    <link rel="import" href="/bower_components/paper-spinner/paper-spinner.html">

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

    <!-- Controllers -->
    <script src="/app/controllers/HomeController.js" type="text/javascript"></script>

    <!-- Services -->

    <base href="/">
</head>
<body ng-app="LaGet"  fullbleed vertical layout>
{{--ng-controller="MainController"--}}
<paper-toast text="U'r so toasted"></paper-toast>

<div fit vertical layout ui-view></div>
</body>
</html>