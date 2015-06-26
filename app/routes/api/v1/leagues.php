<?php
$app->post('/', function () use ($app) {
    $db = db();
});

$app->get('/', function () use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $db = db();
});

$app->get('/:id', function () use ($app) {
    $encoder = new OpaqueEncoder(getenv('ID_ENCODER_KEY'));
    $id = $encoder->decode($id);
    $db = db();
});

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
