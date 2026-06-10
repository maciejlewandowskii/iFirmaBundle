<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Service;

use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoiceContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoiceListRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoicePositionRequest;
use maciejlewandowskii\iFirmaApi\Enum\CalculationBasis;
use maciejlewandowskii\iFirmaApi\Enum\FormatDateSale;
use maciejlewandowskii\iFirmaApi\Enum\InvoiceFormat;
use maciejlewandowskii\iFirmaApi\Enum\InvoiceStatus;
use maciejlewandowskii\iFirmaApi\Enum\PaymentMethod;
use maciejlewandowskii\iFirmaApi\Enum\RecipientSignatureType;
use maciejlewandowskii\iFirmaApi\Enum\VatRateType;
use maciejlewandowskii\iFirmaApi\Exception\InvoiceNotFoundException;
use maciejlewandowskii\iFirmaApi\Service\InvoiceService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class InvoiceServiceTest extends TestCase
{
    private MockObject&iFirmaClientInterface $client;

    private InvoiceService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(iFirmaClientInterface::class);
        $this->service = new InvoiceService(
            $this->client,
            Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator(),
        );
    }

    private function makeInvoiceRequest(): CreateInvoiceRequest
    {
        return new CreateInvoiceRequest(
            calculationBasis: CalculationBasis::Net,
            issueDate: '2024-03-01',
            saleDate: '2024-03-01',
            saleDateFormat: FormatDateSale::Daily,
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            contractor: new InvoiceContractorRequest(
                name: 'Test Company',
                postalCode: '00-001',
                city: 'Warszawa',
            ),
            positions: [
                new InvoicePositionRequest(
                    name: 'Service',
                    unit: 'szt',
                    vatRateType: VatRateType::Percentage,
                    quantity: 1.0,
                    unitPrice: 100.0,
                ),
            ],
        );
    }

    public function testCreatePostsToCorrectEndpointAndReturnsResponse(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/fakturakraj.json')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Identyfikator' => 'FV/2024/1']));

        $result = $this->service->create($this->makeInvoiceRequest());

        $this->assertSame(0, $result->code);
        $this->assertSame('FV/2024/1', $result->identifier);
        $this->assertSame('OK', $result->message);
    }

    public function testGetReturnsApiResponseForExistingInvoice(): void
    {
        $this->client->method('get')
            ->willReturn(new ApiResponse(['Identyfikator' => 'FV/2024/1', 'Brutto' => 123.0]));

        $result = $this->service->get('FV/2024/1');

        $this->assertFalse($result->isEmpty());
        $this->assertSame('FV/2024/1', $result->getString('Identyfikator'));
    }

    public function testGetUsesXmlFormatInPath(): void
    {
        $this->client->expects($this->once())
            ->method('get')
            ->with($this->stringContains('.xml'))
            ->willReturn(new ApiResponse(['data' => 'xml-content']));

        $this->service->get('FV/2024/1', InvoiceFormat::Xml);
    }

    public function testGetThrowsInvoiceNotFoundWhenResponseEmpty(): void
    {
        $this->expectException(InvoiceNotFoundException::class);

        $this->client->method('get')->willReturn(new ApiResponse([]));

        $this->service->get('NONEXISTENT');
    }

    public function testGetPdfCallsGetRawWithPdfExtension(): void
    {
        $this->client->expects($this->once())
            ->method('getRaw')
            ->with($this->stringContains('.pdf'))
            ->willReturn('%PDF-1.4 fake');

        $result = $this->service->getPdf('FV/2024/1');

        $this->assertStringStartsWith('%PDF', $result);
    }

    public function testGetPdfWithDuplicateFlagAddsQueryParam(): void
    {
        $this->client->expects($this->once())
            ->method('getRaw')
            ->with($this->anything(), $this->anything(), ['typ' => 'dup'])
            ->willReturn('%PDF-1.4');

        $this->service->getPdf('FV/2024/1', duplicate: true);
    }

    public function testListYieldsInvoiceListItemResponses(): void
    {
        $this->client->method('get')
            ->willReturn(new ApiResponse([
                'Wynik' => [
                    [
                        'KontrahentNazwa' => 'ACME',
                        'IdentyfikatorKontrahenta' => 'CTR-1',
                        'NIPKontrahenta' => '5252344078',
                        'DataWystawienia' => '2024-03-01',
                        'PelnyNumer' => 'FV/2024/1',
                        'Brutto' => 123.0,
                        'FakturaId' => 42,
                        'Rodzaj' => 'FKR',
                        'Waluta' => 'PLN',
                        'Zaplacono' => 0.0,
                        'CzyWyslano' => false,
                    ],
                ],
            ]));

        $items = iterator_to_array($this->service->list(new InvoiceListRequest(dateFrom: '2024-03-01')));

        $this->assertCount(1, $items);
        $this->assertSame('ACME', $items[0]->contractorName);
        $this->assertSame(123.0, $items[0]->grossAmount);
        $this->assertSame(42, $items[0]->invoiceId);
        $this->assertFalse($items[0]->isSent);
    }

    public function testListWithStatusFilterPassesStatusAsQueryParam(): void
    {
        $capturedParams = [];

        $this->client->expects($this->once())
            ->method('get')
            ->with('/faktury.json', $this->anything(), $this->callback(
                static function (array $params) use (&$capturedParams): true {
                    $capturedParams = $params;

                    return true;
                },
            ))
            ->willReturn(new ApiResponse(['Wynik' => []]));

        iterator_to_array($this->service->list(new InvoiceListRequest(
            dateFrom: '2024-01-01',
            status: [InvoiceStatus::Unpaid, InvoiceStatus::Overdue],
        )));

        $this->assertIsString($capturedParams['status']);
        $this->assertStringContainsString('nieoplacone', $capturedParams['status']);
        $this->assertStringContainsString('przeterminowane', $capturedParams['status']);
    }

    public function testListReturnsEmptyWhenNoResults(): void
    {
        $this->client->method('get')->willReturn(new ApiResponse(['Wynik' => []]));

        $items = iterator_to_array($this->service->list(new InvoiceListRequest(dateFrom: '2024-01-01')));

        $this->assertCount(0, $items);
    }
}
