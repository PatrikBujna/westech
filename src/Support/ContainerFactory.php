<?php

declare(strict_types=1);

namespace App\Support;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Builds and compiles the application's dependency-injection container.
 */
final class ContainerFactory
{
    /**
     * Create a compiled container from the given configuration.
     *
     * @param array $config
     * @param string $servicesPath
     * @return ContainerBuilder
     */
    public static function create(array $config, string $servicesPath): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('app.config', $config);

        /** @var callable $register */
        $register = require $servicesPath;
        $register($container);

        $container->compile();

        return $container;
    }
}
