(function($, window) {

    var cb = {
        init: function () {

        }
    };

    var drawer = {

        gutter: 60,

        drawerWidth: 0,

        drawerElement: null,

        init: function () {
            this.drawerElement = $(".drawer");
            this.drawerWidth = $(window).width() - this.gutter; // minus padding
            this.drawerElement.width(this.drawerWidth);
            this.drawerElement.height($(window).height());
            this.drawerElement.css("overflow","auto");
        },
        show: function (drawerId) {

            this.drawerElement.width(this.drawerWidth);

            var drawer = $(".drawer."+drawerId);

            drawer.addClass("animated fadeInRight",function(){
                drawer.show();
            });
        },
        hide: function (drawerId) {
            var drawer = $(".drawer."+drawerId);
            drawer.hide();
        }
    };

    // initialize all
    function main () {
        cb.init();
        drawer.init();
        window.cb = {};
        window.cb.drawer = drawer;
    }
    main();

    // watch resize and re initialize
    $(window).resize(function () {
        main();
    });

})(jQuery, window);