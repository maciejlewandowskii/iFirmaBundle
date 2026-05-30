<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Contract;

use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\CreateVatPurchaseRequest;
use maciejlewandowskii\iFirmaApi\DTO\Response\Expense\ExpenseCreatedResponse;
use maciejlewandowskii\iFirmaApi\iFirmaApi;

interface IFirmaVatExpenseInterface extends IFirmaEntityInterface
{
    public function toCreateVatPurchaseRequest(): CreateVatPurchaseRequest;

    public function isSyncStale(): bool;

    public function createVatPurchaseOniFirma(iFirmaApi $api): ExpenseCreatedResponse;

    public function createActivityCostOniFirma(iFirmaApi $api): ExpenseCreatedResponse;

    public function createPhoneInternetCostOniFirma(iFirmaApi $api): ExpenseCreatedResponse;
}
