<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Concern;

use DateTimeImmutable;
use DateTimeInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\CreateOtherCostRequest;
use maciejlewandowskii\iFirmaApi\DTO\Response\Expense\ExpenseCreatedResponse;
use maciejlewandowskii\iFirmaApi\iFirmaApi;

trait OtherExpenseActionsTrait
{
    abstract public function toCreateOtherCostRequest(): CreateOtherCostRequest;

    abstract public function getIFirmaId(): ?string;

    abstract public function setIFirmaId(string $iFirmaId): void;

    abstract public function setIFirmaSyncedAt(DateTimeInterface $syncedAt): void;

    abstract public function getIFirmaStateHash(): ?string;

    abstract public function setIFirmaStateHash(?string $hash): void;

    public function createOtherCostOniFirma(iFirmaApi $api): ExpenseCreatedResponse
    {
        $request = $this->toCreateOtherCostRequest();
        $response = $api->expenseService->createOtherCost($request);

        if (null !== $response->id) {
            $this->setIFirmaId($response->id);
        }

        $this->setIFirmaSyncedAt(new DateTimeImmutable());
        $this->setIFirmaStateHash($this->computeIFirmaOtherExpenseHash());

        return $response;
    }

    public function isSyncStale(): bool
    {
        if (null === $this->getIFirmaId()) {
            return true;
        }

        return $this->getIFirmaStateHash() !== $this->computeIFirmaOtherExpenseHash();
    }

    private function computeIFirmaOtherExpenseHash(): string
    {
        return md5(serialize($this->toCreateOtherCostRequest()));
    }
}
