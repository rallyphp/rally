var LoginCtrl = function($scope, $location, AuthService) {
  $scope.login = function() {
    AuthService.login($scope.email, $scope.password).then(function(res) {
      console.log(res);
      $location.path('/');
    }, function() {
      alert('login failed!');
    });
  };
};

module.exports = LoginCtrl;
