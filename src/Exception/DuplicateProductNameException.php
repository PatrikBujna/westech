<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

use function sprintf;

/**
 * Raised when a product cannot be stored because its name is already taken
 * (violates the unique name constraint).
 */
final class DuplicateProductNameException extends RuntimeException
{
    /**
     * Create the exception for a specific product name.
     *
     * @param string $name
     * @return self
     */
    public static function forName(string $name): self
    {
        return new self(sprintf('A product with the name "%s" already exists.', $name));
    }
}
