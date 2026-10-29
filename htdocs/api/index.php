<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Request;
use App\Core\Router;

$request = Request::createFromGlobals();
$router = new Router();

require __DIR__ . '/../app/routes.php';

$router->dispatch($request);
