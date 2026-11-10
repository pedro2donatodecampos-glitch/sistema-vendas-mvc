<?php
declare(strict_types=1);

namespace App\Services\Payment;

final class CreditCardPayment implements PaymentMethod
{
    public function name(): string
    {
        return 'Cartão de crédito';
    }

    public function receiptNote(): string
    {
        return 'Pagamento aprovado na maquininha (crédito).';
    }
}
