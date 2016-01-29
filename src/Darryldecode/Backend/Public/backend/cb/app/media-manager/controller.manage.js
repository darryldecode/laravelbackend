angular.module('cb.mediaManager').controller('MediaManagerController', ['$scope','$timeout','$window','$filter','$modal','GlobalLoaderService','AlertService','MediaManagerFactory', function ($scope,$timeout,$window,$filter,$modal,GlobalLoaderService,AlertService,MediaManagerFactory) {

    console.log('MediaManagerController Init');

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

    // create dir
    $scope.mediaManager.mkDir = function () {

        var modalInstance = $modal.open({
            templateUrl: BASE_URL + '/darryldecode/backend/cb/app/media-manager/modals/mkdir.html',
            controller: function ($scope, $modalInstance) {
                $scope.dirName = '';
                $scope.ok = function () {
                    $modalInstance.close($scope.dirName);
                };
                $scope.cancel = function () {
                    $modalInstance.dismiss('cancel');
                };
            },
            size: 'sm'
        });

        modalInstance.result.then(function (dirName) {
            MediaManagerFactory.mkDir({
                path: $scope.mediaManager.currentPath,
                dirName: dirName
            }).then(function (success) {
                queryFiles($scope.mediaManager.currentPath);
            }, function (error) {
                GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(4000);
            })
        });
    };

    // remove file
    $scope.mediaManager.rm = function (path) {
        AlertService.showConfirm("Are you sure to delete this file?", function () {
            MediaManagerFactory.rm({paths:path}).then(function (success) {
                queryFiles($scope.mediaManager.currentPath);
            }, function (error) {
                GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(4000);
            });
        });
    };

    // remove file
    $scope.mediaManager.rmrf = function (path) {
        AlertService.showConfirm("Are you sure to delete this folder?", function () {
            MediaManagerFactory.rmrf({paths:path}).then(function (success) {
                queryFiles($scope.mediaManager.currentPath);
            }, function (error) {
                GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(4000);
            });
        });
    };

    // rename or move
    $scope.mediaManager.mv = function (path) {

        var modalInstance = $modal.open({
            templateUrl: BASE_URL + '/darryldecode/backend/cb/app/media-manager/modals/rename.html',
            controller: function ($scope, $modalInstance) {
                $scope.newName = '';
                $scope.ok = function () {
                    $modalInstance.close($scope.newName);
                };
                $scope.cancel = function () {
                    $modalInstance.dismiss('cancel');
                };
            },
            size: 'sm'
        });

        modalInstance.result.then(function (newName) {
            MediaManagerFactory.mv({
                path: path,
                newPath: ($scope.mediaManager.currentPath=='/') ? newName : $scope.mediaManager.currentPath + '/' + newName
            }).then(function (success) {
                queryFiles($scope.mediaManager.currentPath);
            }, function (error) {
                GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(4000);
            });
        });
    };

    // show file link
    $scope.mediaManager.showLink = function (path) {
        $modal.open({
            templateUrl: BASE_URL + '/darryldecode/backend/cb/app/media-manager/modals/show-link.html',
            controller: function ($scope, $modalInstance) {
                $scope.path = STORAGE_URL + path;
                $scope.ok = function () {
                    $modalInstance.close();
                };
            },
            size: 'md'
        });
    };

    // download file
    $scope.mediaManager.download = function (path) {
        MediaManagerFactory.download(path);
    };

    /**
     * Uploader
     *
     * @type {{}}
     */
    $scope.uploader = {};
    $scope.uploader.files = [];

    $scope.$watch('uploader.files', function () {
        upload($scope.uploader.files, {path: $scope.mediaManager.currentPath});
    });

    // just a helper on view Eg. ( some_folder/another/file.txt -> file.txt )
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

    // upload
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