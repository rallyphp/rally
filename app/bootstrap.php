<?php
require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('UTC');

$dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();
