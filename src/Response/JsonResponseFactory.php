<?php

declare(strict_types=1);

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Builds JSON responses with a common envelope and security headers.
 */
final class JsonResponseFactory
{
    private const HEADERS = [
        'X-Content-Type-Options' => 'nosniff',
        'Cache-Control' => 'no-store',
    ];

    /**
     * Build a success response.
     *
     * @param mixed $data
     * @param int $status
     * @param array $meta
     * @param array $headers
     * @return JsonResponse
     */
    public function success(mixed $data, int $status = 200, array $meta = [], array $headers = []): JsonResponse
    {
        $body = ['data' => $data];
        if ($meta !== []) {
            $body['meta'] = $meta;
        }

        return new JsonResponse($body, $status, self::HEADERS + $headers);
    }

    /**
     * Build an error response.
     *
     * @param int $status
     * @param string $code
     * @param string $message
     * @param array $details
     * @return JsonResponse
     */
    public function error(int $status, string $code, string $message, array $details = []): JsonResponse
    {
        $error = [
            'code' => $code, 
            'message' => $message
        ];
        if ($details !== []) {
            $error['details'] = $details;
        }

        return new JsonResponse(['error' => $error], $status, self::HEADERS);
    }

    /**
     * Build an empty 204 response.
     *
     * @return Response
     */
    public function noContent(): Response
    {
        return new Response('', Response::HTTP_NO_CONTENT, self::HEADERS);
    }
}
