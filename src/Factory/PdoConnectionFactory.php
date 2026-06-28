<?php

declare(strict_types=1);

namespace App\Factory;

use App\Support\AppConfig;
use PDO;

use function sprintf;

/**
 * Creates a configured PDO connection to the MySQL database.
 */
final readonly class PdoConnectionFactory
{
    /**
     * @param AppConfig $config
     */
    public function __construct(private AppConfig $config)
    {
    }

    /**
     * Open a PDO connection
     *
     * @return PDO
     */
    public function create(): PDO
    {
        $host = (string) $this->config->get('database.host', '127.0.0.1');
        $name = (string) $this->config->get('database.name', '');
        $user = (string) $this->config->get('database.user', '');
        $password = (string) $this->config->get('database.password', '');

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $name);

        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ]);
    }
}
