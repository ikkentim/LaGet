angular.module('LaGetDep').factory('FlashMessageService', function ($http, UserModel) {
    return {
        setMessage: function(message, autoClose) {
            var el = document.querySelector('#flash-message-toast');

            if(!message) {
                el.hide();
                return;
            }

            el.text = message;
            el.duration =  autoClose || typeof autoClose === 'undefined' ? 5000 : 600000;
            el.show();
        }
    };
});