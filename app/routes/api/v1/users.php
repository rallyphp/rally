<?php
$app->post('/', function () use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $db = db();

    $email = strtolower(trim($app->request->post('email')));
    $password = trim($app->request->post('password'));
    $firstName = trim($app->request->post('first-name'));
    $lastName = trim($app->request->post('last-name'));

    if ($email === '') {
        $app->halt(400);
    }

    if ($password === '') {
        $app->halt(400);
    }

    if ($firstName === '') {
        $app->halt(400);
    }

    if ($lastName === '') {
        $app->halt(400);
    }

    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    if ($email === false) {
        $app->halt(400);
    }

    $password = password_hash($password, PASSWORD_DEFAULT);

    if ($password === false) {
        $app->halt(500);
    }

    $query = <<<SQL
INSERT INTO users (
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

    $st = $db->query($query, [
        ':v0' => $email,
        ':v1' => $password,
        ':v2' => $firstName,
        ':v3' => $lastName
    ]);

    $id = $db->lastInsertId();

    $response = $app->response();
    $response->setStatus(201);
    $response->headers->set('Location', $app->urlFor('user', [
        'id' => $encoder->encode($id)
    ]));
});

$app->get('/', function () use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $db = db();
});

$app->get('/:id', function () use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $id = $encoder->decode($id);
    $db = db();
})->name('user');

$app->patch('/:id', function () use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $id = $encoder->decode($id);
    $db = db();
});

$app->delete('/:id', function () use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $id = $encoder->decode($id);
    $db = db();
});
