<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Padrão Repository: consultas de vendas usadas no carrinho e nos relatórios.
 */
final class SaleRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function search(array $filters): array
    {
        $sql = 'SELECT s.*, c.name AS customer_name
                FROM sales s LEFT JOIN customers c ON c.id = s.customer_id
                WHERE 1 = 1';
        $params = [];

        if (!empty($filters['from'])) {
            $sql .= ' AND date(s.created_at) >= date(:from)';
            $params[':from'] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $sql .= ' AND date(s.created_at) <= date(:to)';
            $params[':to'] = $filters['to'];
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND s.status = :status';
            $params[':status'] = $filters['status'];
        }

        $sql .= ' ORDER BY s.created_at DESC, s.id DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public function findWithItems(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT s.*, c.name AS customer_name
             FROM sales s LEFT JOIN customers c ON c.id = s.customer_id WHERE s.id = ?'
        );
        $statement->execute([$id]);
        $sale = $statement->fetch();
        if (!$sale) {
            return null;
        }

        $items = $this->db->prepare('SELECT * FROM sale_items WHERE sale_id = ? ORDER BY id');
        $items->execute([$id]);
        $sale['items'] = $items->fetchAll();
        return $sale;
    }

    public function totals(array $filters): array
    {
        $sql = 'SELECT COUNT(*) AS count, COALESCE(SUM(total), 0) AS revenue
                FROM sales WHERE status = "completed"';
        $params = [];
        if (!empty($filters['from'])) {
            $sql .= ' AND date(created_at) >= date(:from)';
            $params[':from'] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $sql .= ' AND date(created_at) <= date(:to)';
            $params[':to'] = $filters['to'];
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->fetch();
    }

    /** Exclui do histórico somente vendas já canceladas (o estoque já foi devolvido). */
    public function deleteCancelled(int $id): bool
    {
        $statement = $this->db->prepare('DELETE FROM sales WHERE id = ? AND status = "cancelled"');
        $statement->execute([$id]);
        return $statement->rowCount() === 1;
    }
}
