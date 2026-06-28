<?php

declare(strict_types=1);

namespace App\Http\Entity;

final readonly class Product
{
    public const array WRITABLE_FIELDS = [
        'name', 
        'description', 
        'brand', 
        'category', 
        'price_excl_vat', 
        'vat_rate'
    ];

    /**
     * @param int|null $id
     * @param string $name
     * @param string|null $description
     * @param string $brand
     * @param string $category
     * @param string $priceExclVat
     * @param string $vatRate
     * @param string|null $createdAt
     * @param string|null $updatedAt
     */
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $description,
        public string $brand,
        public string $category,
        public string $priceExclVat,
        public string $vatRate,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }
}
