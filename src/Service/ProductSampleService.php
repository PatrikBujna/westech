<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Source\ProductSourceInterface;
use InvalidArgumentException;

/**
 * Returns sample products from a named source. Used by GET /api/products/test
 */
final readonly class ProductSampleService
{
    /**
     * @param array<string, ProductSourceInterface> $sources
     */
    public function __construct(private array $sources)
    {
    }

    /**
     * Fetch sample products from the named source.
     *
     * @param string $source
     * @return array
     * @throws InvalidArgumentException When the source name is not configured.
     */
    public function fromSource(string $source): array
    {
        $resolved = $this->sources[$source]
            ?? throw new InvalidArgumentException(sprintf('Unknown product source "%s".', $source));

        return $resolved->fetch();
    }
}
