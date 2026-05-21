<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Response\Payment;

final readonly class PaymentRegisteredResponse
{
    public function __construct(
        public int $code,
        public string $message,
        public ?string $id = null,
    ) {
    }
}
