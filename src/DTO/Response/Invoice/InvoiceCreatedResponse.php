<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Response\Invoice;

final readonly class InvoiceCreatedResponse
{
    public function __construct(
        public int $code,
        public string $message,
        public string $identifier,
    ) {
    }
}
