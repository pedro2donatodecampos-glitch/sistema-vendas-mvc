<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ProductRepository
{
    public function __construct(private PDO $db)
    {
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
}
