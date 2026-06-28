<?php

declare(strict_types=1);

use App\Http\Controller\ProductController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Application routes
 *
 * @return RouteCollection
 */
$routes = new RouteCollection();

$routes->add('products.create', new Route(
    '/api/products',
    ['_controller' => [ProductController::class, 'create']],
    methods: ['POST'],
));

$routes->add('products.list', new Route(
    '/api/products',
    ['_controller' => [ProductController::class, 'list']],
    methods: ['GET'],
));

$routes->add('products.test', new Route(
    '/api/products/test',
    ['_controller' => [ProductController::class, 'testProducts']],
    methods: ['GET'],
));

$routes->add('products.update', new Route(
    '/api/products/{id}',
    ['_controller' => [ProductController::class, 'update']],
    requirements: ['id' => '\d+'],
    methods: ['PATCH'],
));

$routes->add('products.delete', new Route(
    '/api/products/{id}',
    ['_controller' => [ProductController::class, 'delete']],
    requirements: ['id' => '\d+'],
    methods: ['DELETE'],
));

return $routes;
