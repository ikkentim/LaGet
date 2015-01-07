angular.module('LaGet', ['LaGetDep']);

angular.module('LaGet').config(function (RestangularProvider, $httpProvider) {
    RestangularProvider.setBaseUrl(laget_urls.api_laget);
});

angular.module('LaGetDep', [
    'ui.router',
    'restangular',
]);