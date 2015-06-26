var Top10Ctrl = function($scope, $http) {
  $http.get('/api/v1/ladders/singles/top10')
    .then(function(res) {
      $scope.singles = res.data;
    }, function() {
      alert('error singles!');
    });

  $http.get('/api/v1/ladders/doubles/top10')
    .then(function(res) {
      $scope.doubles = res.data;
    }, function() {
      alert('error doubles!');
    });
};

module.exports = Top10Ctrl;
