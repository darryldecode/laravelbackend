angular.module('cb.services').service('GlobalLoaderService',[function () {

    /**
     * the HTML that will be created and appended to the document as the effect
     *
     * @param loaderText
     * @param loaderMode
     * @returns {string}
     * @param icon
     */
    this.effect = function (loaderText, loaderMode, icon) {
        return '<div id="globalEffect" class="alert alert-'+loaderMode+' text-center animated fadeInDown" style="font-size: 15px; padding: 10px; width: 260px; height: 65px; position: fixed; z-index: 99; bottom: 0; left: 0; margin-left: 10px; margin-bottom: 10px;">' +
            '<i class="' + icon + '"></i> ' + loaderText + '</div>';
    };

    /**
     * triggers to show the effect
     *
     * @param message
     * @param mode
     * @returns {*|void}
     */
    this.show = function (message,mode) {

        this.remove(); // make sure to remove the previous effect

        var loaderText = message || 'Loading..';
        var loaderMode = mode || 'success';

        var icon = '';
        switch (mode) {
            case 'info':
                icon = 'fa fa-info-circle';
                break;
            case 'warning':
                icon = 'fa fa-exclamation-circle';
                break;
            case 'danger':
                icon = 'fa fa-exclamation-triangle';
                break;
            case 'success':
                icon = 'fa fa-thumbs-up';
                break;
            default:
                icon = 'fa fa-info-circle';
                break
        }

        angular.element(document.getElementsByTagName('body')).append(this.effect(loaderText, loaderMode, icon));
        return this;

    };

    /**
     * triggers to hide the effect slowly
     *
     * @param delay (number of seconds you want to delay before hide will be triggered)
     * @returns {*}
     */
    this.hide = function (delay) {

        var delaySeconds = delay || 3000;

        angular.element("#globalEffect").removeClass('animated fadeInDown').delay(delaySeconds).fadeOut(3000,function(){ this.remove(); });
        return this;

    };

    /**
     * removes the appended fancy effect immediately
     *
     * @returns {*}
     */
    this.remove = function () {
        angular.element("#globalEffect").remove();
        return this;
    };

    return this;
}]);