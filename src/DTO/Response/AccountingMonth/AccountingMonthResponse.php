<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Response\AccountingMonth;

final readonly class AccountingMonthResponse
{
    public function __construct(
        public int $month,
        public int $year,
    ) {
    }
}
