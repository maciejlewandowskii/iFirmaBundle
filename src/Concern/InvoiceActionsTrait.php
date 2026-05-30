<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Concern;

use DateTimeImmutable;
use DateTimeInterface;
use LogicException;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\SendInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Response\Invoice\InvoiceCreatedResponse;
use maciejlewandowskii\iFirmaApi\Enum\KsefInvoiceType;
use maciejlewandowskii\iFirmaApi\iFirmaApi;

trait InvoiceActionsTrait
{
    abstract public function toCreateInvoiceRequest(): CreateInvoiceRequest;

    abstract public function getIFirmaId(): ?string;

    abstract public function setIFirmaId(string $iFirmaId): void;

    abstract public function setIFirmaSyncedAt(DateTimeInterface $syncedAt): void;

    abstract public function getIFirmaStateHash(): ?string;

    abstract public function setIFirmaStateHash(?string $hash): void;

    public function createOniFirma(iFirmaApi $api): InvoiceCreatedResponse
    {
        $request = $this->toCreateInvoiceRequest();
        $response = $api->invoiceService->create($request);
        $this->setIFirmaId($response->identifier);
        $this->setIFirmaSyncedAt(new DateTimeImmutable());
        $this->setIFirmaStateHash($this->computeIFirmaInvoiceHash());

        return $response;
    }

    public function getPdfFromiFirma(iFirmaApi $api, bool $duplicate = false): string
    {
        return $api->invoiceService->getPdf($this->requireIFirmaId(), $duplicate);
    }

    public function sendToKsefOniFirma(
        iFirmaApi $api,
        KsefInvoiceType $type = KsefInvoiceType::Domestic,
        ?string $sendDate = null,
    ): void {
        $api->invoiceService->sendToKsef($type, $this->requireIFirmaId(), $sendDate);
    }

    public function sendByEmailOniFirma(
        iFirmaApi $api,
        KsefInvoiceType $type = KsefInvoiceType::Domestic,
        ?SendInvoiceRequest $request = null,
    ): void {
        $api->invoiceService->send($type, $this->requireIFirmaId(), $request, byEmail: true);
    }

    public function sendByPostOniFirma(
        iFirmaApi $api,
        KsefInvoiceType $type = KsefInvoiceType::Domestic,
        ?SendInvoiceRequest $request = null,
    ): void {
        $api->invoiceService->send($type, $this->requireIFirmaId(), $request, byPost: true);
    }

    public function isSyncStale(): bool
    {
        if (null === $this->getIFirmaId()) {
            return true;
        }

        return $this->getIFirmaStateHash() !== $this->computeIFirmaInvoiceHash();
    }

    private function computeIFirmaInvoiceHash(): string
    {
        return md5(serialize($this->toCreateInvoiceRequest()));
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
