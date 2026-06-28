<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Exception\BadRequestException;
use App\Exception\DuplicateProductNameException;
use App\Exception\PayloadTooLargeException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use InvalidArgumentException;
use App\Service\ProductService;
use App\Request\JsonRequestParser;
use App\Request\ProductRequestMapper;
use App\Response\JsonResponseFactory;
use App\Response\ProductPresenter;
use App\Service\ProductSampleService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP endpoints for managing products
 */
final readonly class ProductController
{
    /**
     * @param ProductService $service
     * @param JsonRequestParser $parser
     * @param ProductRequestMapper $requests
     * @param JsonResponseFactory $responses
     * @param ProductPresenter $presenter
     * @param ProductSampleService $samples
     */
    public function __construct(
        private ProductService $service,
        private JsonRequestParser $parser,
        private ProductRequestMapper $requests,
        private JsonResponseFactory $responses,
        private ProductPresenter $presenter,
        private ProductSampleService $samples,
    ) {
    }

    /**
     * Create a product (POST /api/products).
     *
     * @param Request $request
     * @return Response 201 with the created product.
     * @throws BadRequestException When the body is not valid JSON (from the parser).
     * @throws PayloadTooLargeException When the body exceeds the configured size limit.
     * @throws ValidationException When the product data is invalid.
     * @throws DuplicateProductNameException When the name is already taken.
     */
    public function create(Request $request): Response
    {
        $product = $this->service->create($this->parser->parse($request));

        return $this->responses->success(
            $this->presenter->present($product),
            Response::HTTP_CREATED,
            headers: ['Location' => '/api/products/' . $product->id],
        );
    }

    /**
     * Update a product (PATCH /api/products/{id}).
     *
     * @param Request $request
     * @return Response 200 with the updated product.
     * @throws BadRequestException When the body is not valid JSON (from the parser).
     * @throws PayloadTooLargeException When the body exceeds the configured size limit.
     * @throws ValidationException When the product data is invalid.
     * @throws ProductNotFoundException When no product has the given id.
     * @throws DuplicateProductNameException When the name collides with another product.
     */
    public function update(Request $request): Response
    {
        $product = $this->service->update($request->attributes->getInt('id'), $this->parser->parse($request));

        return $this->responses->success($this->presenter->present($product));
    }

    /**
     * Delete a product (DELETE /api/products/{id}).
     *
     * @param Request $request
     * @return Response 204 No Content.
     * @throws ProductNotFoundException When no product has the given id.
     */
    public function delete(Request $request): Response
    {
        $this->service->delete($request->attributes->getInt('id'));

        return $this->responses->noContent();
    }

    /**
     * List products with pagination and filters (GET /api/products).
     *
     * @param Request $request
     * @return Response 200 with products and pagination metadata.
     */
    public function list(Request $request): Response
    {
        $result = $this->service->list($this->requests->listQuery($request));

        return $this->responses->success(
            $this->presenter->presentMany($result->items),
            Response::HTTP_OK,
            meta: $result->meta(),
        );
    }

    /**
     * Return sample products (POST /api/products).
     *
     * @param Request $request
     * @return Response
     * @throws InvalidArgumentException When the requested ?source is not configured.
     */
    public function testProducts(Request $request): Response
    {
        $products = $this->samples->fromSource($this->requests->source($request));

        return $this->responses->success(
            $this->presenter->presentMany($products),
            Response::HTTP_OK,
            meta: ['count' => count($products)],
        );
    }
}
