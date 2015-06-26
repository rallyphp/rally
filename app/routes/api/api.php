<?php
$app->group('/v1', function () use ($app) {
    require __DIR__ . '/v1/v1.php';
});
