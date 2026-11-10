<?php
declare(strict_types=1);

namespace App\Services;

use App\Services\Payment\CashPayment;
use App\Services\Payment\CreditCardPayment;
use App\Services\Payment\DebitCardPayment;
use App\Services\Payment\PaymentMethod;
use App\Services\Payment\PixPayment;

/**
 * Padrão Factory: recebe o nome escolhido na tela e devolve o objeto de pagamento correto.
 * Para criar uma nova forma de pagamento basta criar a classe e registrá-la em METHODS.
 */
final class PaymentMethodFactory
{
    /** @var array<string, class-string<PaymentMethod>> */
    private const METHODS = [
        'Dinheiro' => CashPayment::class,
        'Cartão de crédito' => CreditCardPayment::class,
        'Cartão de débito' => DebitCardPayment::class,
        'PIX' => PixPayment::class,
    ];

    public static function create(string $method): PaymentMethod
    {
        $class = self::METHODS[$method] ?? null;
        if ($class === null) {
            throw new \DomainException('Selecione uma forma de pagamento válida.');
        }

        return new $class();
    }

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(self::METHODS);
    }
}
