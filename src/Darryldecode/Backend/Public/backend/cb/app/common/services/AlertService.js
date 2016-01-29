angular.module('cb.services').service('AlertService',[function () {

    this.showAlert = function (message,fn) {

        var anonymousFn = fn || null;

        // if 3rd argument is null we will return only a alert with no callback
        if( anonymousFn === null ) return bootbox.alert(message, function(){});

        // else we will trigger the provided callback
        bootbox.alert(message, function () {
            fn();
        });

    };

    this.showConfirm = function (message, Fn) {
        return bootbox.confirm(message, function(result) {
            if( ! result) return;
            Fn();
        });
    };

    return this;

}]);