<?php
$app->post('/', function () use ($app) {
    $db = db();
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
WHERE id = :w0
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
});

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
