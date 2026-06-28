<?php

declare(strict_types=1);

namespace App\Service\Source;

use App\Http\Entity\Product;
use App\Support\AppConfig;
use JsonException;
use RuntimeException;

/**
 * Reads sample products from a local JSON file shipped with the app.
 */
final readonly class LocalDatasetSource implements ProductSourceInterface
{
    /**
     * @param AppConfig $config
     */
    public function __construct(private AppConfig $config)
    {
    }

    /**
     * @inheritDoc
     * @throws RuntimeException When the dataset file is missing.
     * @throws JsonException When the dataset file contains invalid JSON.
     */
    public function fetch(): array
    {
        $path = (string) $this->config->get('product_source.local_dataset');

        if (!is_file($path)) {
            throw new RuntimeException(sprintf('Local product dataset not found at "%s".', $path));
        }

        $items = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        $products = [];
        foreach ($items as $item) {
            $products[] = new Product(
                id: null,
                name: (string) ($item['name'] ?? ''),
                description: isset($item['description']) ? (string) $item['description'] : null,
                brand: (string) ($item['brand'] ?? ''),
                category: (string) ($item['category'] ?? ''),
                priceExclVat: (string) ($item['price_excl_vat'] ?? ''),
                vatRate: (string) ($item['vat_rate'] ?? ''),
            );
        }

        return $products;
    }
}
