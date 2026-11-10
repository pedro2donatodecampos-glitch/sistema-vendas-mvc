<?php
declare(strict_types=1);

namespace App\Services\Payment;

final class CashPayment implements PaymentMethod
{
    public function name(): string
    {
        return 'Dinheiro';
    }

    public function receiptNote(): string
    {
        return 'Pagamento recebido em espécie no caixa.';
    }
}
