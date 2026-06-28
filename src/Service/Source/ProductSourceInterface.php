<?php

declare(strict_types=1);

namespace App\Service\Source;

use App\Http\Entity\Product;

/**
 * A source of sample products 
 */
interface ProductSourceInterface
{
    /** @return Product[] */
    public function fetch(): array;
}
