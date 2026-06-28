<?php

declare(strict_types=1);

namespace App\Http;

use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Exception\DuplicateProductNameException;
use App\Exception\BadRequestException;
use App\Exception\PayloadTooLargeException;
use App\Exception\UnauthorizedException;
use App\Response\JsonResponseFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use InvalidArgumentException;
use Throwable;

/**
 * Maps thrown exceptions onto JSON error responses. Anything unexpected falls
 * through to a generic 500 so we don't leak internals back to the client.
 */
final readonly class ErrorHandler
{
    /**
     * @param JsonResponseFactory $responses
     */
    public function __construct(
        private JsonResponseFactory $responses,
    ) {
    }

    /**
     * Map an exception to a JSON error response.
     *
     * @param Throwable $exception
     * @return Response
     */
    public function handle(Throwable $exception): Response
    {
        [$status, $code, $message, $details] = $this->map($exception);

        return $this->responses->error($status, $code, $message, $details);
    }

    /**
     * Resolve [status, code, message, details] for an exception.
     *
     * @param Throwable $exception
     * @return array
     */
    private function map(Throwable $exception): array
    {
        return match (true) {
            $exception instanceof UnauthorizedException =>
                [Response::HTTP_UNAUTHORIZED, 'unauthorized', $exception->getMessage(), []],
            $exception instanceof ValidationException =>
                [Response::HTTP_UNPROCESSABLE_ENTITY, 'validation_failed', $exception->getMessage(), $exception->errors()],
            $exception instanceof DuplicateProductNameException =>
                [Response::HTTP_UNPROCESSABLE_ENTITY, 'duplicate_name', $exception->getMessage(), []],
            $exception instanceof ProductNotFoundException =>
                [Response::HTTP_NOT_FOUND, 'not_found', $exception->getMessage(), []],
            $exception instanceof ResourceNotFoundException =>
                [Response::HTTP_NOT_FOUND, 'not_found', 'Resource not found.', []],
            $exception instanceof MethodNotAllowedException =>
                [Response::HTTP_METHOD_NOT_ALLOWED, 'method_not_allowed', 'Method not allowed.', []],
            $exception instanceof PayloadTooLargeException =>
                [Response::HTTP_REQUEST_ENTITY_TOO_LARGE, 'payload_too_large', $exception->getMessage(), []],
            $exception instanceof BadRequestException,
            $exception instanceof InvalidArgumentException =>
                [Response::HTTP_BAD_REQUEST, 'bad_request', $exception->getMessage(), []],
            default =>
                [Response::HTTP_INTERNAL_SERVER_ERROR, 'internal_error', 'An unexpected error occurred.', []],
        };
    }
}
