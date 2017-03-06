angular.module('cb.directives',[])

    .directive('filePreview', ['STORAGE_URL',function(STORAGE_URL) {

        return {

            // required to make it work as an element
            restrict: 'E',
            template: '<div class="file-preview"></div>',
            replace: true,

            // observe and manipulate the DOM
            link: function($scope, element, attrs) {

                if( isImage(attrs.fileSource) ) {

                    var imgSrc = STORAGE_URL + attrs.fileSource;

                    var img = angular.element('<img>', {
                        src : imgSrc,
                        width : attrs.size || 50,
                        height : attrs.size || 50
                    });

                    img.css("border","3px solid #ddd");
                    img.addClass("img-circle");

                    element.append(img);

                } else {
                    element.append('<i class="fa fa-file-o fa-thumb-icon"></i>');
                }

                function isImage(src) {
                    return (/(.*)\.(?:jpe?g|gif|png)$/i).test(src);
                }
            }

        }

    }])

    .directive('cb', [function() {

        return {

            // required to make it work as an element
            restrict: 'A',

            // observe and manipulate the DOM
            link: function($scope, element, attrs) {

                var cbType = attrs.cbType;

                angular.element(element).on('click',function (e) {

                    e.preventDefault();

                    // do not enable fancy box if none of this memitypes matches
                    if( ! (/(.*)\.(?:jpe?g|gif|png)$/i).test(cbType) ) return;

                    angular.element(element).fancybox({
                        helpers : {
                            title : {
                                type : 'inside'
                            }
                        }
                    });

                });
            }
        }
    }]);