<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Response\Invoice;

final readonly class InvoiceListItemResponse
{
    public function __construct(
        public string $contractorName,
        public string $contractorId,
        public string $contractorTaxId,
        public string $issueDate,
        public string $fullNumber,
        public float $grossAmount,
        public int $invoiceId,
        public string $type,
        public string $currency,
        public float $amountPaid,
        public ?string $paymentDeadline,
        public bool $isSent,
    ) {
    }
}
