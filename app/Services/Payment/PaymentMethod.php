<?php
declare(strict_types=1);

namespace App\Services\Payment;

/**
 * Contrato comum a todas as formas de pagamento.
 * Quem usa um pagamento não precisa saber qual classe concreta está por trás.
 */
interface PaymentMethod
{
    /** Nome gravado na venda e exibido nas telas. */
    public function name(): string;

    /** Texto curto exibido no comprovante. */
    public function receiptNote(): string;
}
