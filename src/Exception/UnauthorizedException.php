<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

/**
 * Raised when a request is missing a valid Bearer token. Maps to 401.
 */
final class UnauthorizedException extends RuntimeException
{
    /**
     * @param string $message
     */
    public function __construct(string $message = 'Authentication required.')
    {
        parent::__construct($message);
    }
}
