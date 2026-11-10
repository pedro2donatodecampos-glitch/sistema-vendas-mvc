<?php
declare(strict_types=1);

namespace App\Services\Payment;

final class DebitCardPayment implements PaymentMethod
{
    public function name(): string
    {
        return 'Cartão de débito';
    }

    public function receiptNote(): string
    {
        return 'Pagamento aprovado na maquininha (débito).';
    }
}
