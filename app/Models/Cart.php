<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Model do Carrinho: guarda na sessão do navegador os produtos escolhidos
 * (id do produto => quantidade) até a venda ser finalizada.
 */
final class Cart
{
    private const SESSION_KEY = 'cart';

    /** @return array<int, int> */
    public function items(): array
    {
        return $_SESSION[self::SESSION_KEY] ?? [];
    }

    public function quantityOf(int $productId): int
    {
        return (int) ($this->items()[$productId] ?? 0);
    }

    public function has(int $productId): bool
    {
        return isset($this->items()[$productId]);
    }

    public function add(int $productId, int $quantity): void
    {
        $this->set($productId, $this->quantityOf($productId) + $quantity);
    }

    public function set(int $productId, int $quantity): void
    {
        $_SESSION[self::SESSION_KEY][$productId] = $quantity;
    }

    public function remove(int $productId): void
    {
        unset($_SESSION[self::SESSION_KEY][$productId]);
    }

    public function clear(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    public function isEmpty(): bool
    {
        return $this->items() === [];
    }
}
