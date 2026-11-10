<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Customer;
use PDO;

/**
 * Padrão Repository: único lugar que conhece o SQL da tabela de clientes.
 */
final class CustomerRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM customers ORDER BY name')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM customers WHERE id = ?');
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function save(Customer $customer, ?int $id = null): void
    {
        $data = $customer->toArray();
        if ($id === null) {
            $statement = $this->db->prepare(
                'INSERT INTO customers (name, email, phone, document) VALUES (:name, :email, :phone, :document)'
            );
        } else {
            $statement = $this->db->prepare(
                'UPDATE customers SET name = :name, email = :email, phone = :phone, document = :document WHERE id = :id'
            );
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
        }

        $statement->bindValue(':name', $data['name']);
        $statement->bindValue(':email', $data['email'] ?: null);
        $statement->bindValue(':phone', $data['phone'] ?: null);
        $statement->bindValue(':document', $data['document'] ?: null);
        $statement->execute();
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM customers WHERE id = ?');
        $statement->execute([$id]);
    }
}
