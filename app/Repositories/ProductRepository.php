<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use PDO;

/**
 * Padrão Repository: único lugar que conhece o SQL da tabela de produtos.
 */
final class ProductRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM products ORDER BY name')->fetchAll();
    }

    public function available(): array
    {
        return $this->db->query('SELECT * FROM products WHERE stock > 0 ORDER BY name')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM products WHERE id = ?');
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function codeExists(string $code, ?int $ignoreId = null): bool
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM products WHERE code = ? AND id != ?');
        $statement->execute([$code, $ignoreId ?? 0]);
        return (int) $statement->fetchColumn() > 0;
    }

    public function save(Product $product, ?string $image, ?int $id = null): void
    {
        $data = $product->toArray();
        $data['image'] = $image;
        if ($id === null) {
            $statement = $this->db->prepare(
                'INSERT INTO products (code, name, category, expires_at, stock, unit_price, package_price, image)
                 VALUES (:code, :name, :category, :expires_at, :stock, :unit_price, :package_price, :image)'
            );
        } else {
            $statement = $this->db->prepare(
                'UPDATE products SET code = :code, name = :name, category = :category, expires_at = :expires_at,
                 stock = :stock, unit_price = :unit_price, package_price = :package_price, image = :image WHERE id = :id'
            );
            $data['id'] = $id;
        }

        $statement->execute($data);
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM products WHERE id = ?');
        $statement->execute([$id]);
    }
}
