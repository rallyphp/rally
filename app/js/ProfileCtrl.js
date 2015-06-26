var ProfileCtrl = function($scope, $resource, $routeParams) {
  var Player = $resource('/api/v1/players/:id');

  $scope.player = Player.get({id: $routeParams.id});
};

module.exports = ProfileCtrl;
