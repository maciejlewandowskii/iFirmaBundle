<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Contract;

use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\SendInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Response\Invoice\InvoiceCreatedResponse;
use maciejlewandowskii\iFirmaApi\Enum\KsefInvoiceType;
use maciejlewandowskii\iFirmaApi\iFirmaApi;

interface IFirmaInvoiceInterface extends IFirmaEntityInterface
{
    public function toCreateInvoiceRequest(): CreateInvoiceRequest;

    public function isSyncStale(): bool;

    public function createOniFirma(iFirmaApi $api): InvoiceCreatedResponse;

    public function getPdfFromiFirma(iFirmaApi $api, bool $duplicate = false): string;

    public function sendToKsefOniFirma(
        iFirmaApi $api,
        KsefInvoiceType $type = KsefInvoiceType::Domestic,
        ?string $sendDate = null,
    ): void;

    public function sendByEmailOniFirma(
        iFirmaApi $api,
        KsefInvoiceType $type = KsefInvoiceType::Domestic,
        ?SendInvoiceRequest $request = null,
    ): void;

    public function sendByPostOniFirma(
        iFirmaApi $api,
        KsefInvoiceType $type = KsefInvoiceType::Domestic,
        ?SendInvoiceRequest $request = null,
    ): void;
}
