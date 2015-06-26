<?php
$app->get('/', function () use ($app) {
    if (!isset($_SESSION['id'])) {
        $app->redirect('/login');
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8',
        getenv('DB_HOST'),
        getenv('DB_NAME')
    );

    $conn = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $query = <<<SQL
SELECT 1 rank,
  id,
  email,
  first_name,
  0 rating
FROM players
SQL;

    $st = $conn->prepare($query);
    $st->execute();
    $top10 = [];

    $encoder = new OpaqueEncoder('im a little teapot');

    foreach ($st as $row) {
        $top10[] = [
            'rank' => $row['rank'],
            'id' => $encoder->encode($row['id']),
            'avatarUrl' => sprintf(
                'https://secure.gravatar.com/avatar/%s?s=16&d=mm',
                md5($row['email'])
            ),
            'name' => $row['first_name'],
            'rating' => $row['rating']
        ];
    }

    $app->render('dashboard.html', [
        'user' => [
            'id' => $encoder->encode($_SESSION['id'])
        ],
        'name' => $_SESSION['firstName'],
        'avatarUrl' => sprintf(
            'https://secure.gravatar.com/avatar/%s?s=32&d=mm',
            md5($_SESSION['email'])
        ),
        'top10' => $top10
    ]);
});

$app->get('/login', function () use ($app) {
    $app->render('login.html');
});

$app->post('/login', function () use ($app) {
    $email = strtolower(trim($app->request->post('email')));
    $password = $app->request->post('password');

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8',
        getenv('DB_HOST'),
        getenv('DB_NAME')
    );

    $conn = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $query = <<<SQL
SELECT id,
  email,
  password,
  first_name,
  last_name
FROM players
WHERE email = :w0
SQL;

    $st = $conn->prepare($query);
    $st->execute([
        ':w0' => $email
    ]);

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

$app->post('/signup', function () use ($app) {
    $email = strtolower(trim($app->request->post('signup-email')));
    $password = trim($app->request->post('signup-password'));
    $firstName = trim($app->request->post('first-name'));
    $lastName = trim($app->request->post('last-name'));

    if ($email === '') {
        echo 'Nope.';
        return;
    }

    if ($password === '') {
        echo 'Nope.';
        return;
    }

    if ($firstName === '') {
        echo 'Nope.';
        return;
    }

    if ($lastName === '') {
        echo 'Nope.';
        return;
    }

    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    if ($email === false) {
        echo 'Nope.';
        return;
    }

    $password = password_hash($password, PASSWORD_DEFAULT);

    if ($password === false) {
        echo 'Nope.';
        return;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8',
        getenv('DB_HOST'),
        getenv('DB_NAME')
    );

    $conn = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $query = <<<SQL
INSERT INTO players (
  email,
  password,
  first_name,
  last_name
) VALUES (
  :v0,
  :v1,
  :v2,
  :v3
)
SQL;

    $st = $conn->prepare($query);
    $st->execute([
        ':v0' => $email,
        ':v1' => $password,
        ':v2' => $firstName,
        ':v3' => $lastName
    ]);

    $id = $conn->lastInsertId();

    $_SESSION['id'] = $id;
    $_SESSION['email'] = $email;
    $_SESSION['firstName'] = $firstName;
    $_SESSION['lastName'] = $lastName;

    $app->redirect('/');
});

$app->get('/logout', function () use ($app) {
    $_SESSION = [];
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

    session_destroy();

    $app->redirect('/login');
});

$app->get('/settings', function () use ($app) {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8',
        getenv('DB_HOST'),
        getenv('DB_NAME')
    );

    $conn = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $query = <<<SQL
SELECT *
FROM palettes
SQL;

    $st = $conn->prepare($query);
    $st->execute();
    $colors = [];

    foreach ($st as $row) {
        $colors[] = $row;
    }

    $encoder = new OpaqueEncoder('im a little teapot');

    $app->render('players/settings/settings.html', [
        'avatarUrl' => sprintf(
            'https://secure.gravatar.com/avatar/%s?s=32&d=mm',
            md5($_SESSION['email'])
        ),
        'name' => $_SESSION['firstName'],
        'colors' => $colors,
        'avatarUrlBig' => sprintf(
            'https://secure.gravatar.com/avatar/%s?s=128&d=mm',
            md5($_SESSION['email'])
        ),
        'user' => [
            'id' => $encoder->encode($_SESSION['email']),
            'email' => $_SESSION['email'],
            'firstName' => $_SESSION['firstName'],
            'lastName' => $_SESSION['lastName']
        ]
    ]);
});

$app->get('/matches', function () use ($app) {
    $app->render('matches.html');
});

$app->get('/players/:slug', function ($slug) use ($app) {
    $encoder = new OpaqueEncoder('im a little teapot');
    $id = $encoder->decode($slug);

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8',
        getenv('DB_HOST'),
        getenv('DB_NAME')
    );

    $conn = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $query = <<<SQL
SELECT *
FROM players
WHERE id = :w0
SQL;

    $st = $conn->prepare($query);
    $st->execute([
        ':w0' => $id
    ]);

    $row = $st->fetch();

    $app->render('profile.html', [
        'player' => [
            'id' => $encoder->encode($id),
            'firstName' => $row['first_name'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'avatarUrlBig' => sprintf(
                'https://secure.gravatar.com/avatar/%s?s=256&d=mm',
                md5($row['email'])
            )
        ]
    ]);
});

$app->get('/teams/create', function () use ($app) {
    $app->render('teams/create.html');
});
