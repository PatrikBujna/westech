# Products REST API

HTTP REST API for managing products, built with **PHP 8.4** and a small set of
Symfony components — **no framework**. Runs in Docker (PHP 8.4 + Apache, MySQL 8.4).

## Requirements

- **Docker** + **Docker Compose** — that's all you need on the host. PHP, Composer
  and MySQL all run inside containers.

## Installation

1. **Get the code and create your `.env`:**

   ```bash
   cp .env.example .env
   ```

2. **Set a real auth token** in `.env` (`APP_AUTH_TOKEN`). Generate a strong one:

   ```bash
   php -r "echo bin2hex(random_bytes(32)).PHP_EOL;"
   ```

   While you're in `.env`, also change the default MySQL passwords
   (`MYSQL_ROOT_PASSWORD`, `MYSQL_PASSWORD`) from `changeme`.

3. **Build and start the containers:**

   ```bash
   docker compose up -d --build
   ```

   On first boot the PHP container's entrypoint runs `composer install`
   automatically (the project is bind-mounted, so dependencies are installed at
   runtime and then cached in `vendor/` on the host).

4. **Run the database migrations:**

   ```bash
   docker compose exec php php migrate.php
   ```

The API is then available at **<http://localhost:8080>** (the host port is
configurable via `PHP_PORT` in `.env`).

## Postman collection

A ready-to-use Postman collection and environment live in the [`.postman/`](.postman/)
folder:

- `westech-products-api.postman_collection.json` — the request collection
- `westech-local.postman_environment.json` — the matching environment

Import both into Postman (**Import** → select the two files), then pick the
**WesTech** environment in the top-right selector.

The collection contains:

| Folder | Request | Endpoint |
| --- | --- | --- |
| Products | Create product | `POST /api/products` |
| Products | List products (filters + pagination) | `GET /api/products?category=…&brand=…&page=…&per_page=…` |
| Products | Update product | `PATCH /api/products/{{product_id}}` |
| Products | Delete product | `DELETE /api/products/{{product_id}}` |
| Sample / Test data | Get sample products — local dataset | `GET /api/products/test?source=local` |
| Sample / Test data | Get sample products — DummyJSON | `GET /api/products/test?source=dummyjson` |

Authentication is configured **once at the collection level** (Bearer token), so
every request inherits it automatically. It uses these variables:

- `base_url` — API base URL (defaults to `http://localhost:8080`)
- `auth_token` — must match `APP_AUTH_TOKEN` from your `.env`; set it in the
  **WesTech** environment so your real token isn't committed
- `product_id` — id used by the Update / Delete requests (defaults to `1`)

## Tests

Unit tests use **PHPUnit** and cover the most important parts of the app
(product service, validation, authentication, response presentation). The test
folder mirrors the `src/` layout under `tests/Unit/`.

Run them inside the PHP container:

```bash
docker compose exec php composer test
```

Or call PHPUnit directly:

```bash
docker compose exec php vendor/bin/phpunit
```
