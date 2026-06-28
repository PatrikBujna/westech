<?php

declare(strict_types=1);

/**
 * Create products table
 */
return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE products (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name            VARCHAR(255)   COLLATE utf8mb4_0900_as_cs NOT NULL,
                description     TEXT           NULL,
                brand           VARCHAR(255)   NOT NULL,
                category        VARCHAR(255)   NOT NULL,
                price_excl_vat  DECIMAL(10,2)  NOT NULL,
                vat_rate        DECIMAL(5,2)   NOT NULL,
                created_at      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
                updated_at      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_products_name (name),
                INDEX idx_products_category_brand (category, brand),
                INDEX idx_products_brand (brand)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);
    }
};
