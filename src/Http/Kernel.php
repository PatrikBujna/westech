<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\ErrorHandler;
use App\Middleware\AuthenticationMiddleware;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Throwable;

/**
 * Front controller: authenticate, route the request to a controller action, and
 * turn anything that gets thrown into a JSON error response.
 */
final readonly class Kernel
{
    /**
     * @param ContainerInterface $container
     * @param RouteCollection $routes
     * @param AuthenticationMiddleware $authenticator
     * @param ErrorHandler $errorHandler
     */
    public function __construct(
        private ContainerInterface $container,
        private RouteCollection $routes,
        private AuthenticationMiddleware $authenticator,
        private ErrorHandler $errorHandler,
    ) {
    }

    /**
     * Handle an incoming request and produce a response.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        try {
            $this->authenticator->authenticate($request);

            $context = new RequestContext();
            $context->fromRequest($request);

            $parameters = new UrlMatcher($this->routes, $context)->match($request->getPathInfo());
            $request->attributes->add($parameters);

            /** @var array $controllerRef */
            $controllerRef = $parameters['_controller'];
            [$class, $method] = $controllerRef;

            $controller = $this->container->get($class);

            /** @var Response $response */
            $response = $controller->{$method}($request);

            return $response;
        } catch (Throwable $exception) {
            return $this->errorHandler->handle($exception);
        }
    }
}
