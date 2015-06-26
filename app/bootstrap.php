<?php
require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('UTC');

$dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();

function db() {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8',
        getenv('DB_HOST'),
        getenv('DB_NAME')
    );

    return new Rally\Database($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
}

session_start();
