<?php

declare(strict_types=1);

namespace App\Request;

use App\Exception\BadRequestException;
use App\Exception\PayloadTooLargeException;
use App\Support\AppConfig;
use JsonException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Parses and validates the JSON request body.
 */
final readonly class JsonRequestParser
{
    /**
     * @param AppConfig $config
     */
    public function __construct(private AppConfig $config)
    {
    }

    /**
     * Decode and validate the JSON request body.
     *
     * @param Request $request
     * @return array
     * @throws BadRequestException When the content type is not JSON or the body is not valid JSON.
     * @throws PayloadTooLargeException When the body exceeds the configured size limit.
     */
    public function parse(Request $request): array
    {
        if (!str_contains((string) $request->headers->get('Content-Type'), 'application/json')) {
            throw new BadRequestException('Content-Type must be application/json.');
        }

        $content = $request->getContent();

        if (strlen($content) > (int) $this->config->get('request.max_body_bytes', 262144)) {
            throw new PayloadTooLargeException('Request body is too large.');
        }

        if ($content === '') {
            return [];
        }

        try {
            $data = json_decode($content, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new BadRequestException('Request body is not valid JSON.');
        }

        if (!is_array($data)) {
            throw new BadRequestException('Request body must be a JSON object.');
        }

        return $data;
    }
}
