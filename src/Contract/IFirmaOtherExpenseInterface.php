<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Contract;

use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\CreateOtherCostRequest;
use maciejlewandowskii\iFirmaApi\DTO\Response\Expense\ExpenseCreatedResponse;
use maciejlewandowskii\iFirmaApi\iFirmaApi;

interface IFirmaOtherExpenseInterface extends IFirmaEntityInterface
{
    public function toCreateOtherCostRequest(): CreateOtherCostRequest;

    public function isSyncStale(): bool;

    public function createOtherCostOniFirma(iFirmaApi $api): ExpenseCreatedResponse;
}
