<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

use function sprintf;

/**
 * Raised when a product referenced by id does not exist.
 */
final class ProductNotFoundException extends RuntimeException
{
    /**
     * Create the exception for a specific product id.
     *
     * @param int $id
     * @return self
     */
    public static function withId(int $id): self
    {
        return new self(sprintf('Product #%d was not found.', $id));
    }
}
