<?php

declare(strict_types=1);

namespace Tests\Unit\Response;

use App\Http\Entity\Product;
use App\Response\ProductPresenter;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ProductPresenter
 */
final class ProductPresenterTest extends TestCase
{
    /**
     * The presented payload includes the price incl. VAT, computed from the
     * net price and VAT rate.
     *
     * @return void
     */
    public function testPresentIncludesComputedGrossPrice(): void
    {
        $presenter = new ProductPresenter();
        $product = new Product(1, 'Widget', 'desc', 'Acme', 'Tools', '100.00', '23.00', '2026-01-01', '2026-01-02');

        $output = $presenter->present($product);

        self::assertSame(1, $output['id']);
        self::assertSame('100.00', $output['price_excl_vat']);
        self::assertSame('23.00', $output['vat_rate']);
        self::assertSame('123.00', $output['price_with_vat']);
    }
}
