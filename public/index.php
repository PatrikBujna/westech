<?php

declare(strict_types=1);

use App\Http\ErrorHandler;
use App\Middleware\AuthenticationMiddleware;
use App\Http\Kernel;
use App\Support\ContainerFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

$config = require $root . '/config/config.php';
$container = ContainerFactory::create($config, $root . '/config/services.php');

/** @var RouteCollection $routes */
$routes = require $root . '/routes/api.php';

$kernel = new Kernel(
    $container,
    $routes,
    $container->get(AuthenticationMiddleware::class),
    $container->get(ErrorHandler::class),
);

$kernel->handle(Request::createFromGlobals())->send();
