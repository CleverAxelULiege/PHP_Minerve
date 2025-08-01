<?php

use App\Routing\Routes;
use App\Routing\Router;

require_once(__DIR__ . "/../autoloader.php");

$router = new Router();
$GLOBALS["router"] = $router;
Routes::register($router);
$router->dispatch();
