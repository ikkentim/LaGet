angular.module('LaGetDep').controller('MainController', function ($scope, $rootScope, $state, AuthenticationService, $timeout) {
    $rootScope.config = laget_config;
    $rootScope.urls = laget_urls;
    $rootScope.authorizedUser = null;
    function checkAuth() {
        function checkStateAuthentication() {
            $rootScope.authorizedUser = null;

            if ($state.current.data && $state.current.data.authenticationRequired)
                $state.go('laget.auth.login');
        }
        function checkStateAntiAuthentication(user) {
            $rootScope.authorizedUser = user;
            if ($state.current.data && $state.current.data.authenticationProhibited)
                $state.go('laget.home');
        }


        if(AuthenticationService.has()){
            if(!$rootScope.authorizedUser) {
                AuthenticationService.setFromStorage();
                AuthenticationService.get(checkStateAntiAuthentication, checkStateAuthentication);
            }
            else {
                checkStateAntiAuthentication();
            }
        }
        else {
            checkStateAuthentication()
        }

    };

    $scope.$on('$stateChangeSuccess', checkAuth);
    $scope.$on('unauthorizedRequest', function () {
        AuthenticationService.resetToken();
        $scope.goto('laget.auth.login');
    });
    $scope.getParentState = function () {
        if($state.current.name.length <= 0) {return null;}

        var steps = 1;
        while(steps < $state.current.name.split('.').length) {
           var stateName = new Array( steps + 1 ).join( '^' );

            var state = $state.get(stateName);

            if(state == null) return $state.current.name != 'laget.home' ? 'laget.home' : null;

            if(state.url) return stateName;

            steps++;
        }

        return null;
    };

    $scope.goto = function (route, params) {
        $state.go(route, params);
    };
});