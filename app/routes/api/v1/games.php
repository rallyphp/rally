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
