angular.module('LaGetDep').config(function ($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.
        otherwise('/');

    $stateProvider.
        /*
         * No-Auth
         */
        state('laget', {
        }).
        state('laget.home', {
            url: '/',
            views: {
                '@': {
                    templateUrl: '/app/templates/home.html',
                    controller: 'HomeController'
                }
            }
        }).
        state('laget.auth', {
            views: {
                '@': {
                    templateUrl: '/app/templates/auth.html',
                    controller: 'AuthenticationController'
                }
            },
            data: {
                authenticationRequired: false,
                authenticationProhibited: true
            }
        }).
        state('laget.auth.login', {
            url: '/login',
            views: {
                '@laget.auth': {
                    templateUrl: '/app/templates/login.html'
                }
            }
        }).
        state('laget.auth.register', {
            url: '/register',
            views: {
                '@laget.auth': {
                    templateUrl: '/app/templates/register.html'
                }
            }
        });
});