<?php

declare(strict_types=1);

namespace App\Response;

use App\Http\Entity\Product;
use App\Support\Money;

/**
 * Shapes Product objects into the arrays we return as JSON, including the
 * computed price_with_vat field.
 */
final class ProductPresenter
{
    /**
     * Present a single product, including the computed gross price.
     *
     * @param Product $product
     * @return array
     */
    public function present(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'brand' => $product->brand,
            'category' => $product->category,
            'price_excl_vat' => $product->priceExclVat,
            'vat_rate' => $product->vatRate,
            'price_with_vat' => $this->grossPrice($product->priceExclVat, $product->vatRate),
            'created_at' => $product->createdAt,
            'updated_at' => $product->updatedAt,
        ];
    }

    /**
     * Present a list of products.
     *
     * @param Product[] $products
     * @return array
     */
    public function presentMany(array $products): array
    {
        $result = [];
        foreach ($products as $product) {
            $result[] = $this->present($product);
        }

        return $result;
    }

    /**
     * Compute the gross price (net + VAT) rounded to 2 decimals.
     *
     * @param string $net
     * @param string $vatRate
     * @return string
     */
    private function grossPrice(string $net, string $vatRate): string
    {
        return Money::gross($net, $vatRate);
    }
}
