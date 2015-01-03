angular.module('LaGetDep').factory('UserModel', function(Restangular){
    Restangular.extendModel('users', function(user){
        return user;
    });

    return Restangular.service('users');
});