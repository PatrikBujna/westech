<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * Query parameters for the product listing (page, per_page, filters).
 */
final readonly class ListProductsQuery
{
    /**
     * @param int $page
     * @param int $perPage
     * @param string|null $category
     * @param string|null $brand
     */
    public function __construct(
        public int $page,
        public int $perPage,
        public ?string $category,
        public ?string $brand,
    ) {
    }

    /**
     * Build the query from request parameters, clamping out-of-range values.
     *
     * @param Request $request
     * @param int $defaultPerPage
     * @param int $maxPerPage
     * @return self
     */
    public static function fromRequest(Request $request, int $defaultPerPage, int $maxPerPage): self
    {
        return new self(
            self::clamp($request->query->get('page'), 1, PHP_INT_MAX, 1),
            self::clamp($request->query->get('per_page'), 1, $maxPerPage, $defaultPerPage),
            self::filter($request->query->get('category')),
            self::filter($request->query->get('brand')),
        );
    }

    /**
     * Clamp a numeric query value to a range, falling back to a default.
     *
     * @param mixed $value
     * @param int $min
     * @param int $max
     * @param int $default
     * @return int
     */
    private static function clamp(mixed $value, int $min, int $max, int $default): int
    {
        if (!is_string($value) || !ctype_digit($value)) {
            return $default;
        }

        return max($min, min($max, (int) $value));
    }

    /**
     * Normalise a filter value, treating empty as no filter.
     *
     * @param mixed $value
     * @return string|null
     */
    private static function filter(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return $value === '' ? null : $value;
    }
}
