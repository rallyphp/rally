<?php
/*
$app->add(new Slim\Middleware\HttpBasicAuthentication([
    'path' => '',
    'realm' => 'Protected',
    'authenticator' => new Rally\Authenticator()
]));
*/
$app->post('/session', function () use ($app) {
    $email = strtolower(trim($app->request->post('email')));
    $password = $app->request->post('password');
    $db = db();

    $query = <<<SQL
SELECT id,
  email,
  password,
  first_name,
  last_name
FROM players
WHERE email = :w0
SQL;

    $st = $db->query($query, [':w0' => $email]);
    $row = $st->fetch();

    if (password_verify($password, $row['password'])) {
        $_SESSION['id'] = $row['id'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['firstName'] = $row['first_name'];
        $_SESSION['lastName'] = $row['last_name'];

        $app->redirect('/');
    } else {
        echo 'NOPE!';
    }
});

$app->post('/logout', function () use ($app) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );

    session_unset();
    session_destroy();

    $_SESSION = [];

    $response = $app->response();
    $response->setStatusCode(204);
});

$app->get('/session', function() use ($app) {
    $user = [
        'firstName' => $_SESSION['firstName'],
        'avatar' => sprintf(
            'https://secure.gravatar.com/avatar/%s?s=24&d=mm',
            md5($_SESSION['email'])
        )
    ];

    $response = $app->response();
    $response->headers->set('Content-Type', 'application/json');
    $response->setBody(json_encode($user));
});

$app->group('/players', function () use ($app) {
    require __DIR__ . '/players.php';
});

$app->group('/teams', function () use ($app) {
    require __DIR__ . '/teams.php';
});

$app->group('/matches', function () use ($app) {
    require __DIR__ . '/matches.php';
});

$app->group('/games', function () use ($app) {
    require __DIR__ . '/games.php';
});

$app->group('/ladders', function () use ($app) {
    require __DIR__ . '/ladders/ladders.php';
});
