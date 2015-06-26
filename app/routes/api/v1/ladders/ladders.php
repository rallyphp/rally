<?php
$app->group('/singles', function () use ($app) {
    require __DIR__ . '/singles.php';
});

$app->group('/doubles', function () use ($app) {
    require __DIR__ . '/doubles.php';
});
