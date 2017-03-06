angular.module('cb.content').controller('GalleryModalController', ['$scope','$timeout','$window','$filter','$modal','$modalInstance','GlobalLoaderService','AlertService','MediaManagerFactory', function ($scope,$timeout,$window,$filter,$modal,$modalInstance,GlobalLoaderService,AlertService,MediaManagerFactory) {

    console.log('GalleryModalController Init');

    // files
    $scope.files = [];

    /**
     * Media Manager
     *
     * @type {{}}
     */
    $scope.mediaManager = {};
    $scope.mediaManager.currentPath = '';
    $scope.mediaManager.paths = [];
    $scope.mediaManager.isDoing = false;
    $scope.mediaManager.isEmpty = true;

    // list dir handle from folder click
    $scope.mediaManager.ls = function (path) {
        if (path == '/' ) {
            queryFiles('/');
        } else {
            queryFiles(path);
        }
    };

    // list dir handle from breadcrumbs click
    $scope.mediaManager.lsBc = function (index) {

        var copiedPaths = angular.copy($scope.mediaManager.paths);

        copiedPaths.splice(index + 1, copiedPaths.length);

        copiedPaths.shift();

        queryFiles('/'+copiedPaths.join('/'));
    };

    // modal control
    // -----------------------------

    // the selected files
    $scope.selectedFiles = [];

    // handles modal ok button and pass the selected images
    $scope.ok = function () {
        $modalInstance.close($scope.selectedFiles);
    };

    // handles modal cancel button, nothing here, just cancels the modal
    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };

    /**
     * Uploader
     *
     * @type {{}}
     */
    $scope.uploader = {};
    $scope.uploader.files = [];

    // watch the files values so if it changes we can trigger upload
    $scope.$watch('uploader.files', function () {
        upload($scope.uploader.files, {path: $scope.mediaManager.currentPath});
    });

    // just a helper on view
    $scope.getLastSegment = function (path) {

        var separators = ["/","\\\\"];

        return path.split(new RegExp(separators.join('|'), 'g')).pop();
    };

    // get size name. Eg. ( name_large.jpg -> large )
    $scope.getSizeName = function(path) {
        var sizeName = 'Original';
        var fileName = $scope.getLastSegment(path);

        if( isImage(fileName) ) {
            var f = fileName.split('_');
            if(f.length >= 2) {
                sizeName = f[f.length - 1].split('.')[0];
            }
        }

        return sizeName;

        function isImage(src) {
            return (/(.*)\.(?:jpe?g|gif|png)$/i).test(src);
        }
    };

    // uploads the files
    function upload (files, path) {
        if (files && files.length) {
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                MediaManagerFactory.upload(file, path).progress(function (evt) {
                    var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                    console.log('progress: ' + progressPercentage + '% ' + evt.config.file.name);
                }).success(function (data, status, headers, config) {
                    queryFiles($scope.mediaManager.currentPath);
                });
            }
        }
    }

    // query files in a given dir
    function queryFiles (path) {
        MediaManagerFactory.ls({path:path}).then(function (success) {
            $scope.files = success.data.data;
            $scope.mediaManager.currentPath = $scope.files.current_path;
            $scope.mediaManager.paths = $scope.files.paths;
            $scope.mediaManager.isEmpty = $scope.files.is_empty;
        }, function (error) {
            GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(4000);
        });
    }
    queryFiles('/');
}]);