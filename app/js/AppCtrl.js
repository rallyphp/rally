var AppCtrl = function($scope, $mdSidenav, $location, AuthService) {
  $scope.toggleSidenav = function(menuId) {
    $mdSidenav(menuId).toggle();
  };

  $scope.go = function(path) {
    $location.path(path);
  };

  $scope.isSelected = function() {
    console.log(arguments);
  };

  $scope.logout = function() {
    AuthService.logout().then(function() {
      $location.path('/login');
    }, function() {
      alert('logout failed!');
    });
  };
};

module.exports = AppCtrl;
