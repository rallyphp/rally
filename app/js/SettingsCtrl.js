var SettingsCtrl = function($scope, $resource) {
  var Player = $resource('/api/v1/players/:id');

  $scope.player = Player.get({id: '61be04f1'});
};

module.exports = SettingsCtrl;
