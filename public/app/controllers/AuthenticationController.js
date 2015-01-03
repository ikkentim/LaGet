angular.module('LaGetDep').controller('AuthenticationController', function ($scope, $rootScope, AuthenticationService) {
    console.log('auth');
    $rootScope.pageTitle = 'Login or Register';

    $scope.doLogin = function(user)
    {
        AuthenticationService.attempt(user.email, user.password, function(user){
            console.log('Whoop!', user);
        }, function(){
            console.log('Whoops!');
        });
    };
});