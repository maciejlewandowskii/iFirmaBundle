<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Service;

use Generator;
use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateCashVatInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateCorrectiveInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateEuServicesInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateExportGoodsInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateExportServicesInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateForeignCurrencyInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateIossInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateOssInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateProFormaExportInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateProFormaInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateReceiptDocumentRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateShippingInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateWdtInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoiceListRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\SendInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Response\Invoice\InvoiceCreatedResponse;
use maciejlewandowskii\iFirmaApi\DTO\Response\Invoice\InvoiceListItemResponse;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use maciejlewandowskii\iFirmaApi\Enum\InvoiceFormat;
use maciejlewandowskii\iFirmaApi\Enum\KsefInvoiceType;
use maciejlewandowskii\iFirmaApi\Exception\InvoiceNotFoundException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

use function sprintf;

final class InvoiceService extends AbstractService
{
    /**
     * @throws ExceptionInterface
     */
    public function create(CreateInvoiceRequest $request): InvoiceCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/fakturakraj.json', AuthKeyType::Invoice, $this->toArray($request));

        return $this->hydrateCreated($data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function createCorrection(string $invoiceId, CreateCorrectiveInvoiceRequest $request): InvoiceCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post(
            sprintf('/fakturakraj/korekta/%s.json', urlencode($invoiceId)),
            AuthKeyType::Invoice,
            $this->toArray($request),
        );

        return $this->hydrateCreated($data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function createShipping(CreateShippingInvoiceRequest $request): InvoiceCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/fakturawysylka.json', AuthKeyType::Invoice, $this->toArray($request));

        return $this->hydrateCreated($data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function createFromReceipt(CreateInvoiceRequest $request): InvoiceCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/fakturaparagon.json', AuthKeyType::Invoice, $this->toArray($request));

        return $this->hydrateCreated($data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function createForeignCurrency(CreateForeignCurrencyInvoiceRequest $request): InvoiceCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/fakturawaluta.json', AuthKeyType::Invoice, $this->toArray($request));

        return $this->hydrateCreated($data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function createCashVat(CreateCashVatInvoiceRequest $request): InvoiceCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/fakturametodakasowa.json', AuthKeyType::Invoice, $this->toArray($request));

        return $this->hydrateCreated($data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function createWdt(CreateWdtInvoiceRequest $request): InvoiceCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/fakturawdt.json', AuthKeyType::Invoice, $this->toArray($request));

        return $this->hydrateCreated($data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function createExportGoods(CreateExportGoodsInvoiceRequest $request): InvoiceCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/fakturaeksporttowarow.json', AuthKeyType::Invoice, $this->toArray($request));

        return $this->hydrateCreated($data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function createEuServices(CreateEuServicesInvoiceRequest $request): InvoiceCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/fakturaeksportuslugue.json', AuthKeyType::Invoice, $this->toArray($request));

        return $this->hydrateCreated($data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function createExportServices(CreateExportServicesInvoiceRequest $request): InvoiceCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/fakturaeksportuslug.json', AuthKeyType::Invoice, $this->toArray($request));

        return $this->hydrateCreated($data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function createProForma(CreateProFormaInvoiceRequest $request): InvoiceCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/fakturaproformakraj.json', AuthKeyType::Invoice, $this->toArray($request));

        return $this->hydrateCreated($data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function createProFormaExport(CreateProFormaExportInvoiceRequest $request): InvoiceCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/fakturaproformaeksport.json', AuthKeyType::Invoice, $this->toArray($request));

        return $this->hydrateCreated($data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function createReceipt(CreateReceiptDocumentRequest $request): InvoiceCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/rachunekkraj.json', AuthKeyType::Invoice, $this->toArray($request));

        return $this->hydrateCreated($data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function createOss(CreateOssInvoiceRequest $request): InvoiceCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/fakturaoss.json', AuthKeyType::Invoice, $this->toArray($request));

        return $this->hydrateCreated($data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function createIoss(CreateIossInvoiceRequest $request): InvoiceCreatedResponse
    {
        $this->validate($request);

        $data = $this->client->post('/fakturaioss.json', AuthKeyType::Invoice, $this->toArray($request));

        return $this->hydrateCreated($data);
    }

    public function get(string $identifier, InvoiceFormat $format = InvoiceFormat::Json): ApiResponse
    {
        $path = sprintf('/fakturakraj/%s.%s', $identifier, $format->value);
        $data = $this->client->get($path, AuthKeyType::Invoice);

        if ($data->isEmpty()) {
            throw new InvoiceNotFoundException($identifier);
        }

        return $data;
    }

    public function getByType(KsefInvoiceType $type, string $identifier, InvoiceFormat $format = InvoiceFormat::Json): ApiResponse
    {
        $path = sprintf('/%s/%s.%s', $type->value, urlencode($identifier), $format->value);
        $data = $this->client->get($path, AuthKeyType::Invoice);

        if ($data->isEmpty()) {
            throw new InvoiceNotFoundException($identifier);
        }

        return $data;
    }

    public function getPdf(string $identifier, bool $duplicate = false): string
    {
        $path = sprintf('/fakturakraj/%s.pdf', $identifier);
        $queryParams = $duplicate ? ['typ' => 'dup'] : [];

        return $this->client->getRaw($path, AuthKeyType::Invoice, $queryParams);
    }

    public function getPdfByType(KsefInvoiceType $type, string $identifier, bool $duplicate = false): string
    {
        $path = sprintf('/%s/%s.pdf', $type->value, urlencode($identifier));
        $queryParams = $duplicate ? ['typ' => 'dup'] : [];

        return $this->client->getRaw($path, AuthKeyType::Invoice, $queryParams);
    }

    public function sendToKsef(KsefInvoiceType $invoiceType, string $invoiceId, ?string $sendDate = null): void
    {
        $path = sprintf('/%s/ksef/send/%s.json', $invoiceType->value, urlencode($invoiceId));

        $this->client->post($path, AuthKeyType::Invoice, ['DataWysylki' => $sendDate]);
    }

    /**
     * @throws ExceptionInterface
     */
    public function send(
        KsefInvoiceType $invoiceType,
        string $invoiceId,
        ?SendInvoiceRequest $request = null,
        bool $byEmail = false,
        bool $byPost = false,
    ): void {
        $path = sprintf('/%s/send/%s.json', $invoiceType->value, urlencode($invoiceId));
        $params = array_filter(['wyslijEfaktura' => $byEmail ?: null, 'wyslijPoczta' => $byPost ?: null]);
        $body = $request instanceof SendInvoiceRequest ? $this->toArray($request) : [];

        $this->client->post($path, AuthKeyType::Invoice, $body, $params);
    }

    /**
     * @return Generator<int, InvoiceListItemResponse>
     */
    public function list(InvoiceListRequest $request): Generator
    {
        $this->validate($request);

        $params = array_filter([
            'dataOd' => $request->dateFrom,
            'dataDo' => $request->dateTo,
            'kwotaOd' => $request->amountFrom,
            'kwotaDo' => $request->amountTo,
            'kontrahent' => $request->contractor,
            'nipKontrahenta' => $request->contractorTaxId,
            'typ' => $request->type,
            'strona' => $request->page,
            'iloscNaStronie' => $request->perPage,
            'status' => null !== $request->status
                ? implode(',', array_map(static fn ($s) => $s->value, $request->status))
                : null,
        ], static fn (float|string|int|null $v): bool => null !== $v);

        $data = $this->client->get('/faktury.json', AuthKeyType::Invoice, $params);

        foreach ($data->getResponseList('Wynik') as $item) {
            yield new InvoiceListItemResponse(
                contractorName: $item->getString('KontrahentNazwa'),
                contractorId: $item->getString('IdentyfikatorKontrahenta'),
                contractorTaxId: $item->getString('NIPKontrahenta'),
                issueDate: $item->getString('DataWystawienia'),
                fullNumber: $item->getString('PelnyNumer'),
                grossAmount: $item->getFloat('Brutto'),
                invoiceId: $item->getInt('FakturaId'),
                type: $item->getString('Rodzaj'),
                currency: $item->getString('Waluta'),
                amountPaid: $item->getFloat('Zaplacono'),
                paymentDeadline: $item->getNullableString('TerminPlatnosci'),
                isSent: $item->getBool('CzyWyslano'),
            );
        }
    }

    private function hydrateCreated(ApiResponse $data): InvoiceCreatedResponse
    {
        return new InvoiceCreatedResponse(
            code: $data->getInt('Kod'),
            message: $data->getString('Informacja'),
            identifier: $data->getString('Identyfikator'),
        );
    }
}
