<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Concern;

use DateTimeImmutable;
use DateTimeInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\CreateVatPurchaseRequest;
use maciejlewandowskii\iFirmaApi\DTO\Response\Expense\ExpenseCreatedResponse;
use maciejlewandowskii\iFirmaApi\iFirmaApi;

trait VatExpenseActionsTrait
{
    abstract public function toCreateVatPurchaseRequest(): CreateVatPurchaseRequest;

    abstract public function getIFirmaId(): ?string;

    abstract public function setIFirmaId(string $iFirmaId): void;

    abstract public function setIFirmaSyncedAt(DateTimeInterface $syncedAt): void;

    abstract public function getIFirmaStateHash(): ?string;

    abstract public function setIFirmaStateHash(?string $hash): void;

    public function createVatPurchaseOniFirma(iFirmaApi $api): ExpenseCreatedResponse
    {
        $request = $this->toCreateVatPurchaseRequest();
        $response = $api->expenseService->createVatPurchase($request);
        $this->afterExpenseSync($response);

        return $response;
    }

    public function createActivityCostOniFirma(iFirmaApi $api): ExpenseCreatedResponse
    {
        $request = $this->toCreateVatPurchaseRequest();
        $response = $api->expenseService->createActivityCost($request);
        $this->afterExpenseSync($response);

        return $response;
    }

    public function createPhoneInternetCostOniFirma(iFirmaApi $api): ExpenseCreatedResponse
    {
        $request = $this->toCreateVatPurchaseRequest();
        $response = $api->expenseService->createPhoneInternetCost($request);
        $this->afterExpenseSync($response);

        return $response;
    }

    public function isSyncStale(): bool
    {
        if (null === $this->getIFirmaId()) {
            return true;
        }

        return $this->getIFirmaStateHash() !== $this->computeIFirmaVatExpenseHash();
    }

    private function afterExpenseSync(ExpenseCreatedResponse $response): void
    {
        if (null !== $response->id) {
            $this->setIFirmaId($response->id);
        }

        $this->setIFirmaSyncedAt(new DateTimeImmutable());
        $this->setIFirmaStateHash($this->computeIFirmaVatExpenseHash());
    }

    private function computeIFirmaVatExpenseHash(): string
    {
        return md5(serialize($this->toCreateVatPurchaseRequest()));
    }
}
