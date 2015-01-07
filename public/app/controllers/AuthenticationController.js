angular.module('LaGetDep').controller('AuthenticationController', function ($scope, $state, $rootScope, AuthenticationService, FlashMessageService) {
    $rootScope.pageTitle = 'Login or Register';

    $scope.doLogin = function (user) {
        if (!user.email || user.email.length == 0) {
            FlashMessageService.setMessage('Please enter your email address.');
            return;
        }
        if (!user.password || user.password.length == 0) {
            FlashMessageService.setMessage('Please enter your password.');
            return;
        }
        AuthenticationService.attempt(user.email, user.password, function (user) {
            $state.go('laget.home');
            FlashMessageService.setMessage('You have signed in!');

        }, function () {
            FlashMessageService.setMessage('Invalid credentials!');
        });
    };

    $scope.doRegister = function (user) {
        if (!user.email || user.email.length == 0) {
            FlashMessageService.setMessage('Please enter a valid email address.');
            return;
        }
        if (!user.password || user.password.length == 0) {
            FlashMessageService.setMessage('Please enter a password.');
            return;
        }
        if(user.password.length < 6) {
            FlashMessageService.setMessage('Your password should at least be 6 characters long.');
            return;
        }
        if (user.password != user.password_repeat) {
            FlashMessageService.setMessage('Your passwords don\'t match.');
            return;
        }
        AuthenticationService.register({email: user.email, password: user.password}, function () {
            FlashMessageService.setMessage('Successfully registered!');
            $state.go('laget.auth.login');
        }, function () {
            FlashMessageService.setMessage('Please fill out all fields with valid values -or- email already in use!');
        });
    };
});