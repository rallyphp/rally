var PlayersCtrl = function($scope, $resource) {
  var Player = $resource('/api/v1/players');

  $scope.players = Player.query();
};

module.exports = PlayersCtrl;
