<?php

declare(strict_types=1);

use App\Service\ProductSampleService;
use App\Service\ProductService;
use App\Service\ProductValidator;
use App\Repository\ProductRepositoryInterface;
use App\Http\Controller\ProductController;
use App\Http\ErrorHandler;
use App\Middleware\AuthenticationMiddleware;
use App\Request\JsonRequestParser;
use App\Request\ProductRequestMapper;
use App\Response\JsonResponseFactory;
use App\Response\ProductPresenter;
use App\Service\Source\DummyJsonProductSource;
use App\Service\Source\LocalDatasetSource;
use App\Factory\PdoConnectionFactory;
use App\Repository\PdoProductRepository;
use App\Factory\ProductFactory;
use App\Support\AppConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Register the application's service definitions on the container.
 *
 * @return callable
 */
return static function (ContainerBuilder $container): void {
    /**
     * Register an autowired, public service.
     *
     * @param string $id
     * @param string|null $class
     */
    $wire = static fn (string $id, ?string $class = null) =>
        $container->register($id, $class ?? $id)->setAutowired(true)->setPublic(true);

    $container->register(AppConfig::class, AppConfig::class)
        ->setArgument('$config', '%app.config%')
        ->setPublic(true);

    $wire(PdoConnectionFactory::class);
    $container->register(PDO::class, PDO::class)
        ->setFactory([new Reference(PdoConnectionFactory::class), 'create'])
        ->setPublic(true);

    $container->register(HttpClientInterface::class, HttpClientInterface::class)
        ->setFactory([HttpClient::class, 'create'])
        ->setPublic(true);

    $wire(ProductFactory::class);
    $wire(ProductRepositoryInterface::class, PdoProductRepository::class);

    $wire(ProductValidator::class);
    $wire(ProductService::class);

    $wire(LocalDatasetSource::class);
    $wire(DummyJsonProductSource::class);

    $container->register(ProductSampleService::class, ProductSampleService::class)
        ->setArgument('$sources', [
            'local'     => new Reference(LocalDatasetSource::class),
            'dummyjson' => new Reference(DummyJsonProductSource::class),
        ])
        ->setPublic(true);

    $wire(JsonRequestParser::class);
    $wire(ProductRequestMapper::class);
    $wire(JsonResponseFactory::class);
    $wire(ProductPresenter::class);
    $wire(AuthenticationMiddleware::class);
    $wire(ErrorHandler::class);
    $wire(ProductController::class);
};
