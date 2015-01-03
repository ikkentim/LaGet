var apiBase = '/laget/api/v1/';

angular.module('LaGet', ['LaGetDep']);

angular.module('LaGet').config(function (RestangularProvider, $httpProvider) {
    RestangularProvider.setBaseUrl(apiBase);

    $httpProvider.interceptors.push('AuthorizationInterceptor');
});

angular.module('LaGetDep', [
    'ui.router',
    'restangular',
]);