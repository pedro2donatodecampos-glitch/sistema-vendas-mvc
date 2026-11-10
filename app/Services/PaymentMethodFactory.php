<?php
declare(strict_types=1);

namespace App\Services;

final class PaymentMethodFactory
{
    private const METHODS = ['Dinheiro', 'Cartão de crédito', 'Cartão de débito', 'PIX'];

    public static function create(string $method): string
    {
        if (!in_array($method, self::METHODS, true)) {
            throw new \DomainException('Selecione uma forma de pagamento válida.');
        }

        return $method;
    }

    public static function all(): array
    {
        return self::METHODS;
    }
}
