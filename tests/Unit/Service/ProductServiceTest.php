<?php

declare(strict_types=1);

namespace Tests\Unit\Service;

use App\Exception\DuplicateProductNameException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Http\Entity\PaginatedResult;
use App\Http\Entity\Product;
use App\Repository\ProductRepositoryInterface;
use App\Request\ListProductsQuery;
use App\Service\ProductService;
use App\Service\ProductValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ProductService
 */
final class ProductServiceTest extends TestCase
{
    private ProductRepositoryInterface&MockObject $repository;

    private ProductService $service;

    /**
     * Build the service with a mocked repository and a real validator.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->repository = $this->createMock(ProductRepositoryInterface::class);
        $this->service = new ProductService($this->repository, new ProductValidator());
    }

    /**
     * Create validates the payload and delegates persistence to the repository.
     *
     * @return void
     */
    public function testCreateValidatesAndDelegatesToRepository(): void
    {
        $stored = $this->product(1);
        $this->repository->expects(self::once())
            ->method('insert')
            ->with($this->data())
            ->willReturn($stored);

        self::assertSame($stored, $this->service->create($this->data()));
    }

    /**
     * Invalid create data throws and the repository is never touched.
     *
     * @return void
     */
    public function testCreateWithInvalidDataThrowsAndNeverInserts(): void
    {
        $this->repository->expects(self::never())->method('insert');
        $this->expectException(ValidationException::class);

        $this->service->create($this->data(['name' => '']));
    }

    /**
     * A duplicate-name failure from the repository propagates to the caller.
     *
     * @return void
     */
    public function testCreatePropagatesDuplicateName(): void
    {
        $this->repository->method('insert')
            ->willThrowException(DuplicateProductNameException::forName('Widget'));
        $this->expectException(DuplicateProductNameException::class);

        $this->service->create($this->data());
    }

    /**
     * Update validates the (partial) payload and delegates to the repository.
     *
     * @return void
     */
    public function testUpdateValidatesAndDelegatesToRepository(): void
    {
        $stored = $this->product(7);
        $this->repository->expects(self::once())
            ->method('updateById')
            ->with(7, ['price_excl_vat' => '12.50'])
            ->willReturn($stored);

        self::assertSame($stored, $this->service->update(7, ['price_excl_vat' => '12.50']));
    }

    /**
     * Updating an unknown id throws a not-found exception.
     *
     * @return void
     */
    public function testUpdateUnknownIdThrowsNotFound(): void
    {
        $this->repository->method('updateById')->willReturn(null);
        $this->expectException(ProductNotFoundException::class);

        $this->service->update(999, ['name' => 'New name']);
    }

    /**
     * Invalid update data throws before the repository is reached.
     *
     * @return void
     */
    public function testUpdateWithInvalidDataThrowsBeforeRepository(): void
    {
        $this->repository->expects(self::never())->method('updateById');
        $this->expectException(ValidationException::class);

        $this->service->update(1, ['vat_rate' => '200']);
    }

    /**
     * Delete succeeds when the repository reports a removed row.
     *
     * @return void
     */
    public function testDeleteSucceedsWhenRowRemoved(): void
    {
        $this->repository->expects(self::once())->method('delete')->with(5)->willReturn(true);

        $this->service->delete(5);

        $this->addToAssertionCount(1);
    }

    /**
     * Deleting an unknown id throws a not-found exception.
     *
     * @return void
     */
    public function testDeleteUnknownIdThrowsNotFound(): void
    {
        $this->repository->method('delete')->willReturn(false);
        $this->expectException(ProductNotFoundException::class);

        $this->service->delete(999);
    }

    /**
     * List forwards the filters and pagination arguments to the repository.
     *
     * @return void
     */
    public function testListDelegatesToRepository(): void
    {
        $result = new PaginatedResult([], 0, 2, 15);
        $this->repository->expects(self::once())
            ->method('paginate')
            ->with('Tools', 'Acme', 2, 15)
            ->willReturn($result);

        self::assertSame($result, $this->service->list(new ListProductsQuery(2, 15, 'Tools', 'Acme')));
    }

    /**
     * A fully valid create payload, with optional overrides.
     *
     * @param array $overrides
     * @return array
     */
    private function data(array $overrides = []): array
    {
        return array_merge([
            'name'           => 'Widget',
            'description'    => 'A useful widget',
            'brand'          => 'Acme',
            'category'       => 'Tools',
            'price_excl_vat' => '10.00',
            'vat_rate'       => '23.00',
        ], $overrides);
    }

    /**
     * Build a stored product fixture.
     *
     * @param int $id
     * @return Product
     */
    private function product(int $id): Product
    {
        return new Product($id, 'Widget', 'A useful widget', 'Acme', 'Tools', '10.00', '23.00');
    }
}
