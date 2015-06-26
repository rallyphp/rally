//var app = require('angular').module('rally', [require('angular-route')]);
var app = require('angular').module('Rally', [
  require('angular-route'),
  require('angular-resource'),
  require('angular-material')
]);

app.config(function($routeProvider, $mdThemingProvider) {
  $routeProvider
    .when('/', {
      controller: 'DashboardCtrl',
      templateUrl: 'partials/dashboard.html'
    })
    .when('/login', {
      controller: 'LoginCtrl',
      templateUrl: 'partials/login.html'
    })
    .when('/players', {
      controller: 'PlayersCtrl',
      templateUrl: 'partials/players.html'
    })
    .when('/settings', {
      controller: 'SettingsCtrl',
      templateUrl: 'partials/settings.html'
    })
    .when('/teams', {
      controller: 'TeamsCtrl',
      templateUrl: 'partials/teams.html'
    })
    .when('/matches', {
      controller: 'MatchesCtrl',
      templateUrl: 'partials/matches.html'
    })
    .when('/tournaments', {
      controller: 'TournamentsCtrl',
      templateUrl: 'partials/tournaments.html'
    })
    .otherwise('/');

  //$locationProvider.html5Mode(true);

  $mdThemingProvider.theme('default')
    .primaryPalette('blue')
    .accentPalette('orange');
});

app.factory('AuthService', require('./login/AuthService'));

app.controller('AppCtrl', ['$scope', '$mdSidenav', '$location', 'AuthService', require('./AppCtrl')]);

app.controller('LoginCtrl', ['$scope', '$location', 'AuthService', require('./login/LoginCtrl')]);

app.controller('DashboardCtrl', ['$scope', require('./DashboardCtrl')]);
app.controller('FeedCtrl', ['$scope', require('./FeedCtrl')]);
app.controller('Top10Ctrl', ['$scope', '$http', require('./Top10Ctrl')]);

app.controller('PlayersCtrl', ['$scope', '$resource', require('./PlayersCtrl')]);
app.controller('SettingsCtrl', ['$scope', '$resource', require('./SettingsCtrl')]);
app.controller('TeamsCtrl', ['$scope', '$resource', require('./TeamsCtrl')]);
app.controller('MatchesCtrl', ['$scope', '$resource', require('./MatchesCtrl')]);
app.controller('TournamentsCtrl', ['$scope', '$resource', require('./TournamentsCtrl')]);

app.directive('gravatar', function() {
  return {
    restrict: 'AE',
    replace: true,
    scope: {
      name: '@',
      emailHash: '@',
      size: '@'
    },
    template: '<img ng-src="https://secure.gravatar.com/avatar/{{ emailHash }}?s={{ size }}&d=mm" alt="{{ name }}">'
  };
});
