<?php
$app->post('/', function () use ($app) {
    $db = db();
});

$app->get('/:id', function ($id) use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $id = $encoder->decode($id);
    $db = db();
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

$app->get('/:id/games', function ($id) use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $id = $encoder->decode($id);
    $db = db();

    $query = <<<SQL
SELECT *
FROM games
WHERE match_id = :w0
SQL;

    $st = $db->query($query, [
        ':w0' => $id
    ]);

    $games = [];
});

$app->get('/:id/games/:gameNum', function ($id, $gameNum) use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $id = $encoder->decode($id);
    $db = db();
});

$app->get('/:id/games/:gameNum/points', function ($id, $gameNum) use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $id = $encoder->decode($id);
    $db = db();
});
