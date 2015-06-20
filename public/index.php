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

require __DIR__ . '/../app/routes/www/www.php';

$app->get('/stats', function () use ($app) {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8',
        getenv('DB_HOST'),
        getenv('DB_NAME')
    );

    $conn = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $query = <<<SQL
SELECT 1 `rank`,
  id,
  email,
  CONCAT(first_name, ' ', last_name) `name`,
  0 `rating`
FROM players
SQL;

    $st = $conn->prepare($query);
    $st->execute();
    $players = [];

    $encoder = new OpaqueEncoder('im a little teapot');

    foreach ($st as $row) {
        $players[] = [
            'rank' => $row['rank'],
            'id' => $encoder->encode($row['id']),
            'avatarUrl' => sprintf(
                'https://secure.gravatar.com/avatar/%s?s=24&d=mm',
                md5($row['email'])
            ),
            'name' => $row['name'],
            'rating' => $row['rating']
        ];
    }

    session_start();

    $app->render('stats.html', [
        'name' => $_SESSION['firstName'],
        'avatarUrl' => sprintf(
            'https://secure.gravatar.com/avatar/%s?s=32&d=mm',
            md5($_SESSION['email'])
        ),
        'players' => $players
    ]);
});

$app->run();
