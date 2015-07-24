angular.module('cb.dashboard').controller('DashboardInfoController', ['$scope','$window','$timeout','DashboardFactory','AlertService', function ($scope,$window,$timeout,DashboardFactory,AlertService) {

    console.log('DashboardInfoController Init');

    $scope.releases = [];

    DashboardFactory.getReleases().then(function(success) {
        console.log(success);
    }, function(error) {
        console.log(error);
    });
}]);