<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

/**
 * Raised when input data fails validation. Carries per-field error messages
 * for building a 422 response body.
 */
final class ValidationException extends RuntimeException
{
    /**
     * @param array $errors
     * @param string $message
     */
    public function __construct(
        private readonly array $errors,
        string $message = 'Validation failed.',
    ) {
        parent::__construct($message);
    }

    /**
     * Per-field validation error messages.
     *
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
