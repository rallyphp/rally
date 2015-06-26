<?php
$app->group('/api', function () use ($app) {
    require __DIR__ . '/api/api.php';
});

require __DIR__ . '/www/www.php';
