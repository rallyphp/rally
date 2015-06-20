<?php
require __DIR__ . '/../app/bootstrap.php';

$app = new \Slim\Slim([
    'debug' => true,
    'templates.path' => __DIR__ . '/../app/resources',
    'view' => new \Slim\Views\Twig()
]);

$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware());

$view = $app->view();
$view->parserOptions = [
    'debug' => true,
    'cache' => __DIR__ . '/../var/cache'
];

//$view->parserExtension = [new \Slim\Views\TwigExtension()];

require __DIR__ . '/../app/routes/api/api.php';

$app->run();
