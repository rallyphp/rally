<?php
require __DIR__ . '/../app/bootstrap.php';

$app = new \Slim\Slim([
    'debug' => true
]);

$app->group('/api', function () use ($app) {
    require __DIR__ . '/../app/routes/api/api.php';
});

$app->run();
