<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\AccountingMonth;

use maciejlewandowskii\iFirmaApi\Enum\AccountingMonthDirection;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangeAccountingMonthRequest
{
    public function __construct(
        #[Assert\NotNull]
        public AccountingMonthDirection $direction,

        public bool $transferDataFromPreviousYear = false,
    ) {
    }
}
