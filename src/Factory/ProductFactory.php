<?php

declare(strict_types=1);

namespace App\Factory;

use App\Http\Entity\Product;
use App\Support\AppConfig;
use App\Support\Money;

/**
 * Creates Product entities
 */
final readonly class ProductFactory
{
    /**
     * @param AppConfig $config
     */
    public function __construct(private AppConfig $config)
    {
    }

    /**
     * Build a Product from an associative database row.
     *
     * @param array $row
     * @return Product
     */
    public function fromRow(array $row): Product
    {
        return new Product(
            id: isset($row['id']) ? (int) $row['id'] : null,
            name: (string) $row['name'],
            description: $row['description'] !== null ? (string) $row['description'] : null,
            brand: (string) $row['brand'],
            category: (string) $row['category'],
            priceExclVat: (string) $row['price_excl_vat'],
            vatRate: (string) $row['vat_rate'],
            createdAt: isset($row['created_at']) ? (string) $row['created_at'] : null,
            updatedAt: isset($row['updated_at']) ? (string) $row['updated_at'] : null,
        );
    }

    /**
     * Build a Product from a DummyJSON API item.
     *
     * @param array $item
     * @return Product
     */
    public function fromDummyJson(array $item): Product
    {
        $brand = (string) ($item['brand'] ?? '');

        return new Product(
            id: null,
            name: (string) ($item['title'] ?? ''),
            description: isset($item['description']) ? (string) $item['description'] : null,
            brand: $brand !== '' ? $brand : (string) $this->config->get('product.fallback_brand', 'Unknown'),
            category: (string) ($item['category'] ?? ''),
            priceExclVat: Money::normalize((string) ($item['price'] ?? 0)),
            vatRate: (string) $this->config->get('product.default_vat_rate', '23.00'),
        );
    }
}
