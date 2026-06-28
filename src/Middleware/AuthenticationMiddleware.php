<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exception\UnauthorizedException;
use App\Support\AppConfig;
use Symfony\Component\HttpFoundation\Request;

/**
 * Checks the Bearer token on incoming requests
 */
final readonly class AuthenticationMiddleware
{
    /**
     * @param AppConfig $config
     */
    public function __construct(private AppConfig $config)
    {
    }

    /**
     * Verify the request carries the configured Bearer token.
     *
     * @param Request $request
     * @return void
     * @throws UnauthorizedException When the token is missing, wrong, or unconfigured.
     */
    public function authenticate(Request $request): void
    {
        $expected = (string) $this->config->get('auth.token', '');
        $provided = $this->bearerToken($request);

        if ($expected === '' || $provided === null) {
            throw new UnauthorizedException();
        }

        if (!hash_equals(hash('sha256', $expected), hash('sha256', $provided))) {
            throw new UnauthorizedException();
        }
    }

    /**
     * Extract the Bearer token from the Authorization header.
     *
     * @param Request $request
     * @return string|null
     */
    private function bearerToken(Request $request): ?string
    {
        $header = (string) $request->headers->get('Authorization', '');

        if (preg_match('/^Bearer\s+(\S+)$/', $header, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }
}
