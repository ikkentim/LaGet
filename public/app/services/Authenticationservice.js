angular.module('LaGetDep').factory('AuthenticationService', function ($http, UserModel) {
    return {
        attempt: function (username, password, success, error) {
            $http.post(apiBase + 'users/auth', {
                username: username,
                password: password
            }).success(function (data) {
                localStorage.setItem('authentication-token', data.token);
                $http.defaults.headers.common['X-Auth-Token'] = data.token;

                if (success) success(data.user);

            }).error(function(data){
                if(error)error(data);
            });
        },
        setToken: function(token) {
            localStorage.setItem('authentication-token', token);
            $http.defaults.headers.common['X-Auth-Token'] = token;
        },
        resetToken: function() {
            localStorage.setItem('authentication-token', null);
            delete $http.defaults.headers.common['X-Auth-Token'];

        },
        get: function (success, error) {
            UserModel.one().customGET('auth').then(success, error);
        },
        has: function()
        {
            return localStorage.getItem('authentication-token');
        }
    };
});