'use strict';

angular.module('vshell2.controllers', [])

    .controller('StatusCtrl', ['$scope', '$http', function ($scope, $http) {

        $scope.getStatus = function () {

            $scope.status = [];

            $http({ method: 'GET', url: '/vshell2/api/status' })
                .success(function(data, status, headers, config) {
                    $scope.status = data;
                    /*
                    hostsTotal
                    hostsProblemsTotal

                    hostsUpTotal
                    hostsDownTotal
                    hostsUnreachableTotal
                    hostsPending

                    servicesTotal
                    servicesProblemsTotal

                    servicesOkTotal
                    servicesWarningTotal
                    servicesUnknownTotal
                    servicesPendingTotal
                    servicesCriticalTotal
                    */
                }).
                error(function(data, status, headers, config) {
                    messages.error('failed to load Status information from the VShell2 API');
                });

        };

    }])

    .controller('HoststatusCtrl', ['$scope', '$http', function ($scope, $http) {

        $scope.getHoststatus = function () {

            $scope.hoststatus = [];

            $http({ method: 'GET', url: '/vshell2/api/hoststatus' })
                .success(function(data, status, headers, config) {
                    $scope.hoststatus = data;
                }).
                error(function(data, status, headers, config) {
                    messages.error('failed to load Host Status information from the VShell2 API');
                });

        };

    }])
