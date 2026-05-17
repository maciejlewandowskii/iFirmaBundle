<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Invoice;

use maciejlewandowskii\iFirmaApi\Enum\InvoiceStatus;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class InvoiceListRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $dateFrom,

        #[Assert\Date]
        public ?string $dateTo = null,

        #[Assert\PositiveOrZero]
        public ?float $amountFrom = null,

        #[Assert\PositiveOrZero]
        public ?float $amountTo = null,

        #[Assert\Length(max: 15)]
        public ?string $contractor = null,

        #[Assert\Length(max: 13)]
        public ?string $contractorTaxId = null,

        public ?string $type = null,

        /** @var InvoiceStatus[]|null */
        public ?array $status = null,

        #[Assert\Positive]
        public int $page = 1,

        #[Assert\Positive]
        #[Assert\LessThanOrEqual(100)]
        public int $perPage = 20,
    ) {
    }
}
