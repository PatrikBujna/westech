<?php

declare(strict_types=1);

namespace App\Request;

use App\Support\AppConfig;
use Symfony\Component\HttpFoundation\Request;

/**
 * Translates raw HTTP requests into the typed inputs
 */
final class ProductRequestMapper
{
    /**
     * @param AppConfig $config
     */
    public function __construct(private readonly AppConfig $config)
    {
    }

    /**
     * Build the product listing query from the request, applying paging defaults.
     *
     * @param Request $request
     * @return ListProductsQuery
     */
    public function listQuery(Request $request): ListProductsQuery
    {
        return ListProductsQuery::fromRequest(
            $request,
            (int) $this->config->get('pagination.default_per_page', 20),
            (int) $this->config->get('pagination.max_per_page', 100),
        );
    }

    /**
     * Resolve the sample-product source name, falling back to the configured default.
     *
     * @param Request $request
     * @return string
     */
    public function source(Request $request): string
    {
        $default = (string) $this->config->get('product_source.default', 'local');

        return (string) $request->query->get('source', $default);
    }
}
