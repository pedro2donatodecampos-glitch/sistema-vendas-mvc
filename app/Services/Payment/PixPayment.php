<?php
declare(strict_types=1);

namespace App\Services\Payment;

final class PixPayment implements PaymentMethod
{
    public function name(): string
    {
        return 'PIX';
    }

    public function receiptNote(): string
    {
        return 'Pagamento confirmado via PIX.';
    }
}
