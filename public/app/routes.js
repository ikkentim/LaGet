angular.module('LaGetDep').config(function ($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.
        otherwise('/');

    $stateProvider.
        state('LaGet', {
        }).
        /*
         * No-Auth
         */
        state('LaGet.home', {
            url: '/',
            views: {
                '@': {
                    templateUrl: '/app/templates/home.html',
                    controller: 'HomeController'
                }
            }/*,
             data: {
             authenticationRequired: false,
             authenticationProhibited: true
             }*/
        });
});