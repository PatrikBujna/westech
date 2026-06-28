<?php

declare(strict_types=1);

namespace App\Service\Source;

use App\Factory\ProductFactory;
use App\Support\AppConfig;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Fetches sample products from the DummyJSON API.
 */
final readonly class DummyJsonProductSource implements ProductSourceInterface
{
    /**
     * @param HttpClientInterface $client
     * @param AppConfig $config
     * @param ProductFactory $factory
     */
    public function __construct(
        private HttpClientInterface $client,
        private AppConfig $config,
        private ProductFactory $factory,
    ) {
    }

    /**
     * @inheritDoc
     * @throws TransportExceptionInterface When a network error occurs or an unsupported option is passed.
     * @throws DecodingExceptionInterface When the response body cannot be decoded to an array.
     * @throws RedirectionExceptionInterface On a 3xx once the configured max redirects is reached.
     * @throws ClientExceptionInterface On a 4xx response.
     * @throws ServerExceptionInterface On a 5xx response.
     */
    public function fetch(): array
    {
        $baseUrl = rtrim((string) $this->config->get('product_source.dummyjson_base_url', 'https://dummyjson.com'), '/');
        $limit = (int) $this->config->get('product_source.dummyjson_limit', 10);

        $payload = $this->client->request('GET', $baseUrl . '/products', [
            'query' => ['limit' => $limit]
        ])->toArray();

        $products = [];
        foreach ($payload['products'] ?? [] as $item) {
            $products[] = $this->factory->fromDummyJson($item);
        }

        return $products;
    }
}
