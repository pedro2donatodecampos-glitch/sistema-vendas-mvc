<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use PDO;

/**
 * Service Layer: regras de negócio da venda (finalizar e cancelar) dentro de transações.
 */
final class SaleService
{
    public function __construct(private PDO $db)
    {
    }

    public function checkout(array $cart, ?int $customerId, string $paymentMethod): int
    {
        if ($cart === []) {
            throw new \DomainException('Adicione ao menos um produto ao carrinho.');
        }

        $method = PaymentMethodFactory::create($paymentMethod);
        $this->db->beginTransaction();

        try {
            $customer = null;
            if ($customerId !== null) {
                $customerStatement = $this->db->prepare('SELECT id FROM customers WHERE id = ?');
                $customerStatement->execute([$customerId]);
                $customer = $customerStatement->fetchColumn();
                if (!$customer) {
                    throw new \DomainException('O cliente selecionado não está cadastrado.');
                }
            }

            $total = 0.0;
            $items = [];
            $productStatement = $this->db->prepare('SELECT * FROM products WHERE id = ?');
            foreach ($cart as $productId => $quantity) {
                $productStatement->execute([(int) $productId]);
                $product = $productStatement->fetch();
                if (!$product || (int) $quantity < 1 || (int) $product['stock'] < (int) $quantity) {
                    throw new \DomainException('Estoque insuficiente ou produto indisponível: ' . ($product['name'] ?? 'produto'));
                }

                if (Product::isExpired($product['expires_at'] ?? null)) {
                    throw new \DomainException('Produto vencido não pode ser vendido: ' . $product['name']);
                }

                $quantity = (int) $quantity;
                $items[] = [
                    'product_id' => (int) $product['id'],
                    'name' => $product['name'],
                    'quantity' => $quantity,
                    'price' => (float) $product['unit_price'],
                ];
                $total += (float) $product['unit_price'] * $quantity;
            }

            $sale = $this->db->prepare(
                'INSERT INTO sales (customer_id, total, payment_method) VALUES (?, ?, ?)'
            );
            $sale->execute([$customer, $total, $method->name()]);
            $saleId = (int) $this->db->lastInsertId();
            $itemInsert = $this->db->prepare(
                'INSERT INTO sale_items (sale_id, product_id, product_name, quantity, unit_price) VALUES (?, ?, ?, ?, ?)'
            );
            $stockUpdate = $this->db->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');

            foreach ($items as $item) {
                $itemInsert->execute([$saleId, $item['product_id'], $item['name'], $item['quantity'], $item['price']]);
                $stockUpdate->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                if ($stockUpdate->rowCount() !== 1) {
                    throw new \DomainException('Estoque alterado durante a venda. Confira o carrinho e tente novamente.');
                }
            }

            $this->db->commit();
            return $saleId;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function cancel(int $saleId): void
    {
        $this->db->beginTransaction();
        try {
            $saleStatement = $this->db->prepare('SELECT status FROM sales WHERE id = ?');
            $saleStatement->execute([$saleId]);
            if ($saleStatement->fetchColumn() !== 'completed') {
                throw new \DomainException('Venda não encontrada ou já cancelada.');
            }

            $itemsStatement = $this->db->prepare('SELECT product_id, quantity FROM sale_items WHERE sale_id = ?');
            $itemsStatement->execute([$saleId]);
            $restoreStock = $this->db->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
            foreach ($itemsStatement->fetchAll() as $item) {
                if ($item['product_id'] !== null) {
                    $restoreStock->execute([$item['quantity'], $item['product_id']]);
                }
            }

            $cancel = $this->db->prepare('UPDATE sales SET status = "cancelled" WHERE id = ? AND status = "completed"');
            $cancel->execute([$saleId]);
            if ($cancel->rowCount() !== 1) {
                throw new \DomainException('Venda não encontrada ou já cancelada.');
            }
            $this->db->commit();
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }
}
