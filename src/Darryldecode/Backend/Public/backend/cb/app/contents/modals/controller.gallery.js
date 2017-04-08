angular.module('cb.content').controller('GalleryModalController', ['$scope','$timeout','$window','$filter','$modal','$modalInstance','GlobalLoaderService','AlertService','_CSRF', function ($scope,$timeout,$window,$filter,$modal,$modalInstance,GlobalLoaderService,AlertService,_CSRF) {

    console.log('GalleryModalController Init');

    // files
    $scope.media = {

        init: function() {

            var opts = {
                url : '/backend/media/elFinder',
                customData: {'_token': _CSRF},
                getFileCallback: function(file) {
                    console.log('getFileCallback');
                    console.log(file);
                },
                handlers : {
                    select : function(event, elfinderInstance) {
                        var selected = event.data.selected;
                        if (selected.length) {

                            // empty first to make sure no duplicates
                            $scope.media.data.selectedFiles = [];

                            // iterate to all selected items and push it to selectedFiles
                            angular.forEach(selected,function(item) {
                                $scope.$apply(function() {
                                    $scope.media.data.selectedFiles.push({
                                        file: elfinderInstance.file(item),
                                        relative_path: elfinderInstance.path(item).replace('public\\','\\'), // remove public from path
                                        url: elfinderInstance.url(item).replace('/storage/public','/storage') // remove public from path
                                    });
                                });
                            });
                        }
                    }
                }
            };

            $timeout(function() {
                angular.element('#media-loader').hide();
                angular.element('#elfinder').elfinder(opts);
            },3000);
        },

        data: {
            selectedFiles: []
        },

        buttons: {
            ok: function () {
                $modalInstance.close($scope.media.data.selectedFiles);
            },
            cancel: function () {
                $modalInstance.dismiss('cancel');
            }
        }
    };

    $scope.media.init();
}]);