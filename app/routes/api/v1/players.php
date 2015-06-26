<?php
$app->post('/', function () use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $db = db();

    $leagueId = $app->request->post('league-id');
    $firstName = trim($app->request->post('first-name'));
    $lastName = trim($app->request->post('last-name'));

    if ($firstName === '') {
        $app->halt(400);
    }

    if ($lastName === '') {
        $app->halt(400);
    }

    $query = <<<SQL
INSERT INTO players (
  league_id,
  first_name,
  last_name
) VALUES (
  :v0,
  :v1,
  :v2
)
SQL;

    $st = $db->query($query, [
        ':v0' => $leagueId,
        ':v1' => $firstName,
        ':v2' => $lastName
    ]);

    $id = $db->lastInsertId();

    $response = $app->response();
    $response->setStatus(201);
    $response->headers->set('Location', $app->urlFor('player', [
        'id' => $encoder->encode($id)
    ]));
});

$app->get('/', function () use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $db = db();

    $query = <<<SQL
SELECT id,
  first_name,
  last_name,
  email
FROM players
SQL;

    $st = $db->query($query);
    $players = [];

    foreach ($st as $row) {
        $players[] = [
            'id' => $encoder->encode($row['id']),
            'firstName' => $row['first_name'],
            'lastName' => $row['last_name'],
            'emailHash' => md5($row['email'])
        ];
    }

    $response = $app->response();
    $response->headers->set('Content-Type', 'application/json');
    $response->setBody(json_encode($players));
});

$app->get('/:id', function ($id) use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $id = $encoder->decode($id);
    $db = db();

    $query = <<<SQL
SELECT *
FROM players
LEFT JOIN users
  ON players.user_id = users.id
WHERE players.id = :w0
SQL;

    $st = $db->query($query, [
        ':w0' => $id
    ]);

    $row = $st->fetch();
    $player = [
        'id' => $encoder->encode($id),
        'email' => $row['email'],
        'emailHash' => md5($row['email']),
        'firstName' => $row['first_name'],
        'lastName' => $row['last_name']
    ];

    $response = $app->response();
    $response->headers->set('Content-Type', 'application/json');
    $response->setBody(json_encode($player));
})->name('player');

$app->patch('/:id', function ($id) use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $id = $encoder->decode($id);
    $db = db();
});

$app->delete('/:id', function ($id) use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $id = $encoder->decode($id);
    $db = db();
});
