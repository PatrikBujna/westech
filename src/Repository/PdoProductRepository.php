<?php

declare(strict_types=1);

namespace App\Repository;

use App\Exception\DuplicateProductNameException;
use App\Factory\ProductFactory;
use App\Http\Entity\PaginatedResult;
use App\Http\Entity\Product;
use App\Support\AppConfig;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

use function is_array;
use function sprintf;

/**
 * MySQL implementation of the product repository using PDO prepared statements.
 */
final readonly class PdoProductRepository implements ProductRepositoryInterface
{
    /**
     * @param PDO $pdo
     * @param ProductFactory $productFactory
     * @param AppConfig $config
     */
    public function __construct(
        private PDO $pdo,
        private ProductFactory $productFactory,
        private AppConfig $config,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws DuplicateProductNameException When another product already uses the given name.
     * @throws RuntimeException When the freshly inserted row cannot be read back.
     * @throws PDOException On any other database write failure.
     */
    public function insert(array $data): Product
    {
        $fields = $this->writeFields($data);
        $columns = array_keys($fields);

        $placeholders = [];
        foreach ($columns as $column) {
            $placeholders[] = ':' . $column;
        }

        $sql = sprintf(
            'INSERT INTO products (%s) VALUES (%s)',
            implode(', ', $columns),
            implode(', ', $placeholders),
        );

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($this->bindParams($fields));
        } catch (PDOException $e) {
            throw $this->mapWriteException($e, (string) ($data['name'] ?? ''));
        }

        return $this->requireById((int) $this->pdo->lastInsertId());
    }

    /**
     * @inheritDoc
     *
     * @throws DuplicateProductNameException When another product already uses the given name.
     * @throws PDOException On any other database write failure.
     */
    public function updateById(int $id, array $data): ?Product
    {
        $fields = $this->writeFields($data);

        if ($fields !== []) {
            $assignments = [];
            foreach (array_keys($fields) as $column) {
                $assignments[] = "{$column} = :{$column}";
            }

            try {
                $stmt = $this->pdo->prepare('UPDATE products SET ' . implode(', ', $assignments) . ' WHERE id = :id');
                $stmt->execute($this->bindParams($fields) + [':id' => $id]);
            } catch (PDOException $e) {
                throw $this->mapWriteException($e, (string) ($data['name'] ?? ''));
            }
        }

        return $this->findById($id);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM products WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?Product
    {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        return is_array($row) ? $this->productFactory->fromRow($row) : null;
    }

    /**
     * @inheritDoc
     */
    public function paginate(?string $category, ?string $brand, int $page, int $perPage): PaginatedResult
    {
        [$where, $binds] = $this->buildWhere($category, $brand);

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM products {$where}");
        $this->bindFilters($countStmt, $binds);
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $listStmt = $this->pdo->prepare("SELECT * FROM products {$where} ORDER BY id ASC LIMIT :limit OFFSET :offset");
        $this->bindFilters($listStmt, $binds);
        $listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $listStmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $listStmt->execute();

        $items = [];
        foreach ($listStmt->fetchAll() as $row) {
            $items[] = $this->productFactory->fromRow($row);
        }

        return new PaginatedResult($items, $total, $page, $perPage);
    }

    /**
     * Drop anything that isn't a writable product column
     *
     * @param array $data
     * @return array
     */
    private function writeFields(array $data): array
    {
        return array_intersect_key($data, array_flip(Product::WRITABLE_FIELDS));
    }

    /**
     * Build named bind parameters from whitelisted fields.
     *
     * @param array $fields
     * @return array
     */
    private function bindParams(array $fields): array
    {
        $params = [];

        foreach ($fields as $column => $value) {
            $params[':' . $column] = $value === null ? null : (string) $value;
        }

        return $params;
    }

    /**
     * Build the shared WHERE clause and its bind values for list and count queries.
     *
     * @param string|null $category
     * @param string|null $brand
     * @return array
     */
    private function buildWhere(?string $category, ?string $brand): array
    {
        $conditions = [];
        $binds = [];

        if ($category !== null && $category !== '') {
            $conditions[] = 'category = :category';
            $binds[':category'] = $category;
        }

        if ($brand !== null && $brand !== '') {
            $conditions[] = 'brand = :brand';
            $binds[':brand'] = $brand;
        }

        $where = $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions);

        return [$where, $binds];
    }

    /**
     * Bind the filter values onto a prepared statement.
     *
     * @param \PDOStatement $stmt
     * @param array $binds
     * @return void
     */
    private function bindFilters(\PDOStatement $stmt, array $binds): void
    {
        foreach ($binds as $placeholder => $value) {
            $stmt->bindValue($placeholder, $value, PDO::PARAM_STR);
        }
    }

    /**
     * Re-read a product
     *
     * @param int $id
     * @return Product
     * @throws RuntimeException When the row cannot be read back.
     */
    private function requireById(int $id): Product
    {
        $product = $this->findById($id);

        if ($product === null) {
            throw new RuntimeException(sprintf('Product #%d could not be read back after writing.', $id));
        }

        return $product;
    }

    /**
     * Translate a PDO write error into a domain exception when appropriate.
     *
     * @param PDOException $e
     * @param string $name
     * @return DuplicateProductNameException|PDOException Domain exception for a duplicate name, otherwise the original.
     */
    private function mapWriteException(PDOException $e, string $name): Throwable
    {
        if (($e->errorInfo[1] ?? null) === (int) $this->config->get('database.duplicate_entry_code', 1062)) {
            return DuplicateProductNameException::forName($name);
        }

        return $e;
    }
}
