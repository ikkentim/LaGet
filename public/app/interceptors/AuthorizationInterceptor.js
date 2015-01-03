angular.module('LaGetDep').factory('AuthorizationInterceptor', function ($q, $rootScope) {
    return {
        responseError: function (rejection) {

            if (rejection.status == 401) {
                $rootScope.$broadcast('unauthorizedRequest');
            }

            return $q.reject(rejection);
        }
    };
});