<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Concern;

use DateTimeImmutable;
use DateTimeInterface;
use LogicException;
use maciejlewandowskii\iFirmaApi\DTO\Request\Contractor\CreateContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Contractor\UpdateContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Response\Contractor\ContractorCreatedResponse;
use maciejlewandowskii\iFirmaApi\iFirmaApi;

trait ContractorActionsTrait
{
    abstract public function toCreateContractorRequest(): CreateContractorRequest;

    abstract public function toUpdateContractorRequest(): UpdateContractorRequest;

    abstract public function getIFirmaId(): ?string;

    abstract public function setIFirmaId(string $iFirmaId): void;

    abstract public function setIFirmaSyncedAt(DateTimeInterface $syncedAt): void;

    abstract public function getIFirmaStateHash(): ?string;

    abstract public function setIFirmaStateHash(?string $hash): void;

    public function createOniFirma(iFirmaApi $api): ContractorCreatedResponse
    {
        $request = $this->toCreateContractorRequest();
        $response = $api->contractorService->create($request);
        $this->setIFirmaId($response->id);
        $this->setIFirmaSyncedAt(new DateTimeImmutable());
        $this->setIFirmaStateHash($this->computeIFirmaContractorHash());

        return $response;
    }

    public function updateOniFirma(iFirmaApi $api): ContractorCreatedResponse
    {
        $request = $this->toUpdateContractorRequest();
        $response = $api->contractorService->update($this->requireIFirmaId(), $request);
        $this->setIFirmaSyncedAt(new DateTimeImmutable());
        $this->setIFirmaStateHash($this->computeIFirmaContractorHash());

        return $response;
    }

    public function isSyncStale(): bool
    {
        if (null === $this->getIFirmaId()) {
            return true;
        }

        return $this->getIFirmaStateHash() !== $this->computeIFirmaContractorHash();
    }

    private function computeIFirmaContractorHash(): string
    {
        return md5(serialize($this->toCreateContractorRequest()));
    }

    private function requireIFirmaId(): string
    {
        $id = $this->getIFirmaId();

        if (null === $id) {
            throw new LogicException('Entity has not been synced to iFirma yet. Call createOniFirma() first.');
        }

        return $id;
    }
}
