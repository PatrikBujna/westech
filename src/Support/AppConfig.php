<?php

declare(strict_types=1);

namespace App\Support;

use function array_key_exists;
use function is_array;

/**
 * Read-only access to the application configuration tree.
 */
final readonly class AppConfig
{
    /**
     * @param array $config
     */
    public function __construct(private array $config)
    {
    }

    /**
     * Get a configuration value by key, supporting dot notation for nested keys
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->config;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
