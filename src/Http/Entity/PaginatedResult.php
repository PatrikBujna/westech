<?php

declare(strict_types=1);

namespace App\Http\Entity;

use function ceil;

/**
 * One page of products plus the totals needed to build the pagination meta.
 */
final readonly class PaginatedResult
{
    /**
     * @param array $items
     * @param int $total
     * @param int $page
     * @param int $perPage
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
    ) {
    }

    /**
     * Total number of pages for the current page size.
     *
     * @return int
     */
    public function totalPages(): int
    {
        return $this->perPage > 0 ? (int) ceil($this->total / $this->perPage) : 0;
    }

    /**
     * Pagination metadata for the JSON response envelope.
     *
     * @return array
     */
    public function meta(): array
    {
        return [
            'page'        => $this->page,
            'per_page'    => $this->perPage,
            'total'       => $this->total,
            'total_pages' => $this->totalPages(),
        ];
    }
}
