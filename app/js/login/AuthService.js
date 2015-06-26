var AuthService = function($http) {
  var service = {};

  service.login = function(email, password) {
    return $http.post('/api/v1/login', {
      email: email,
      password: password
    }).then(function(res) {
      return res;
    }, function() {
      $q.reject('oops');
    });
  };

  service.logout = function() {
    return $http.post('/api/v1/logout').then(function(res) {
      return res;
    }, function() {
      $q.reject('oops');
    });
  };

  service.isLoggedIn = function() {
    return false;
  };

  return service;
};

module.exports = AuthService;
