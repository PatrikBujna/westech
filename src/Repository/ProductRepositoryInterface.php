<?php

declare(strict_types=1);

namespace App\Repository;

use App\Http\Entity\PaginatedResult;
use App\Http\Entity\Product;

/**
 * Storage contract for products
 */
interface ProductRepositoryInterface
{
    /**
     * Insert a new product from whitelisted field data and return it as stored.
     *
     * @param array $data
     * @return Product Stored product including its database id and timestamps.
     */
    public function insert(array $data): Product;

    /**
     * Update the given fields of a product and return it as stored.
     *
     * @param int $id
     * @param array $data
     * @return Product|null The stored product, or null when no product has the id.
     */
    public function updateById(int $id, array $data): ?Product;

    /**
     * Delete a product by id.
     *
     * @param int $id
     * @return bool True when a row was deleted, false when none matched.
     */
    public function delete(int $id): bool;

    /**
     * Find a product by id.
     *
     * @param int $id
     * @return Product|null The product, or null when not found.
     */
    public function findById(int $id): ?Product;

    /**
     * Return a paginated, optionally filtered list of products.
     *
     * @param string|null $category
     * @param string|null $brand
     * @param int $page
     * @param int $perPage
     * @return PaginatedResult
     */
    public function paginate(?string $category, ?string $brand, int $page, int $perPage): PaginatedResult;
}
