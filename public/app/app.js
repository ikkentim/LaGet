angular.module('LaGet', ['LaGetDep']);

angular.module('LaGet').config(function ($httpProvider, $locationProvider, RestangularProvider) {
    $locationProvider.html5Mode(true);
    $locationProvider.hashPrefix('!');
    RestangularProvider.setBaseUrl('api/v1/');

    $httpProvider.defaults.xsrfCookieName = 'csrftoken';
    $httpProvider.defaults.xsrfHeaderName = 'X-CSRFToken';
});

angular.module('LaGetDep', [
    'ui.router',
    'restangular',
]);