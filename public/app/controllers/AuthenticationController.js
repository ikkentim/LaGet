angular.module('LaGetDep').controller('AuthenticationController', function ($scope, $state, $rootScope, AuthenticationService) {
    console.log('auth');
    $rootScope.pageTitle = 'Login or Register';

    $scope.doLogin = function(user)
    {
        AuthenticationService.attempt(user.email, user.password, function(user){
            console.log('Whoop!', user);
            $state.go('laget.home');
        }, function(){
            console.log('Whoops!');
        });
    };
});