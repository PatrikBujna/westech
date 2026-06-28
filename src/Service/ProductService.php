<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\DuplicateProductNameException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Http\Entity\PaginatedResult;
use App\Http\Entity\Product;
use App\Repository\ProductRepositoryInterface;
use App\Request\ListProductsQuery;

/**
 * Product CRUD. Runs validation and then leaves the actual storage to the repository.
 */
final readonly class ProductService
{
    /**
     * @param ProductRepositoryInterface $repository
     * @param ProductValidator $validator
     */
    public function __construct(
        private ProductRepositoryInterface $repository,
        private ProductValidator $validator,
    ) {
    }

    /**
     * Create a new product from request data.
     *
     * @param array $data
     * @return Product The saved product.
     * @throws ValidationException When the data is invalid.
     * @throws DuplicateProductNameException When the name is taken.
     */
    public function create(array $data): Product
    {
        $this->validator->validateForCreate($data);

        return $this->repository->insert($data);
    }

    /**
     * Update an existing product with the provided (partial) data.
     *
     * @param int $id
     * @param array $data
     * @return Product The saved product.
     * @throws ValidationException When the data is invalid.
     * @throws ProductNotFoundException When no product has the given id.
     * @throws DuplicateProductNameException When the name collides with another product.
     */
    public function update(int $id, array $data): Product
    {
        $this->validator->validateForUpdate($data);

        return $this->repository->updateById($id, $data) ?? throw ProductNotFoundException::withId($id);
    }

    /**
     * Delete a product by id.
     *
     * @param int $id
     * @return void
     * @throws ProductNotFoundException When no product has the given id.
     */
    public function delete(int $id): void
    {
        if (!$this->repository->delete($id)) {
            throw ProductNotFoundException::withId($id);
        }
    }

    /**
     * List products with pagination and optional category/brand filters.
     *
     * @param ListProductsQuery $query
     * @return PaginatedResult
     */
    public function list(ListProductsQuery $query): PaginatedResult
    {
        return $this->repository->paginate($query->category, $query->brand, $query->page, $query->perPage);
    }
}
