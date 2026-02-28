<?php

session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

use Bramus\Router\Router;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$router = new Router();
$router->setBasePath('/');

$setupRoutes = require_once __DIR__ . '/../App/Config/routes.php';
$setupRoutes($router);

$router->run();


