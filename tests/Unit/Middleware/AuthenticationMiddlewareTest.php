<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Exception\UnauthorizedException;
use App\Middleware\AuthenticationMiddleware;
use App\Support\AppConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Unit tests for AuthenticationMiddleware
 */
final class AuthenticationMiddlewareTest extends TestCase
{
    /**
     * A request carrying the configured Bearer token is accepted.
     *
     * @return void
     */
    public function testValidTokenPasses(): void
    {
        $this->middleware('secret')->authenticate($this->request('Bearer secret'));

        $this->addToAssertionCount(1);
    }

    /**
     * A request without an Authorization header is rejected.
     *
     * @return void
     */
    public function testMissingHeaderIsRejected(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->middleware('secret')->authenticate($this->request(null));
    }

    /**
     * A request with the wrong token is rejected.
     *
     * @return void
     */
    public function testWrongTokenIsRejected(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->middleware('secret')->authenticate($this->request('Bearer wrong'));
    }

    /**
     * With no token configured the middleware fails closed and rejects everything.
     *
     * @return void
     */
    public function testEmptyConfiguredTokenFailsClosed(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->middleware('')->authenticate($this->request('Bearer anything'));
    }

    /**
     * Build the middleware with a configured token.
     *
     * @param string $token
     * @return AuthenticationMiddleware
     */
    private function middleware(string $token): AuthenticationMiddleware
    {
        return new AuthenticationMiddleware(new AppConfig(['auth' => ['token' => $token]]));
    }

    /**
     * Build a request with an optional Authorization header.
     *
     * @param string|null $authorization
     * @return Request
     */
    private function request(?string $authorization): Request
    {
        $request = new Request();

        if ($authorization !== null) {
            $request->headers->set('Authorization', $authorization);
        }

        return $request;
    }
}
