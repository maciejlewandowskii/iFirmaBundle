<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Service;

use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CorrectiveInvoicePositionRequest;
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
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\ForeignInvoicePositionRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoiceContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoicePositionRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\OssInvoicePositionRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\ReceiptDocumentPositionRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\SendInvoiceRequest;
use maciejlewandowskii\iFirmaApi\Enum\CalculationBasis;
use maciejlewandowskii\iFirmaApi\Enum\CashVatSettlementType;
use maciejlewandowskii\iFirmaApi\Enum\CorrectiveReasonType;
use maciejlewandowskii\iFirmaApi\Enum\Currency;
use maciejlewandowskii\iFirmaApi\Enum\ForeignInvoiceSaleType;
use maciejlewandowskii\iFirmaApi\Enum\FormatDateSale;
use maciejlewandowskii\iFirmaApi\Enum\InvoiceFormat;
use maciejlewandowskii\iFirmaApi\Enum\InvoiceLanguage;
use maciejlewandowskii\iFirmaApi\Enum\KsefInvoiceType;
use maciejlewandowskii\iFirmaApi\Enum\OssVatRateType;
use maciejlewandowskii\iFirmaApi\Enum\PaymentMethod;
use maciejlewandowskii\iFirmaApi\Enum\ProFormaInvoiceType;
use maciejlewandowskii\iFirmaApi\Enum\RecipientSignatureType;
use maciejlewandowskii\iFirmaApi\Enum\VatRateType;
use maciejlewandowskii\iFirmaApi\Exception\InvoiceNotFoundException;
use maciejlewandowskii\iFirmaApi\Service\InvoiceService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class InvoiceServiceExtendedTest extends TestCase
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

    private function ok(): ApiResponse
    {
        return new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Identyfikator' => 'ID1']);
    }

    private function pos(): InvoicePositionRequest
    {
        return new InvoicePositionRequest(
            name: 'Item',
            unit: 'szt',
            vatRateType: VatRateType::Percentage,
            quantity: 1.0,
            unitPrice: 100.0,
        );
    }

    private function foreignPos(): ForeignInvoicePositionRequest
    {
        return new ForeignInvoicePositionRequest(
            name: 'Item',
            foreignName: 'Product',
            unit: 'szt',
            foreignUnit: 'pcs',
            vatRateType: VatRateType::Percentage,
            quantity: 1.0,
            unitPrice: 100.0,
        );
    }

    private function ossPos(): OssInvoicePositionRequest
    {
        return new OssInvoicePositionRequest(
            name: 'Item',
            unit: 'szt',
            unitPrice: 100.0,
            quantity: 1.0,
            vatRate: 0.23,
            vatRateType: OssVatRateType::Standard,
        );
    }

    private function receiptPos(): ReceiptDocumentPositionRequest
    {
        return new ReceiptDocumentPositionRequest(name: 'Service', unit: 'szt', quantity: 1.0, unitPrice: 50.0);
    }

    private function correctivePos(): CorrectiveInvoicePositionRequest
    {
        return new CorrectiveInvoicePositionRequest(
            name: 'Corrected Item',
            unit: 'szt',
            vatRateType: VatRateType::Percentage,
            quantity: 0.0,
            unitPrice: 100.0,
        );
    }

    private function contractor(): InvoiceContractorRequest
    {
        return new InvoiceContractorRequest(name: 'ACME', postalCode: '00-001', city: 'Warszawa');
    }

    // ── Corrective ──────────────────────────────────────────────────────────

    public function testCreateCorrectionCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with($this->stringContains('/fakturakraj/korekta/FV%2F2024%2F1.json'))
            ->willReturn($this->ok());

        $result = $this->service->createCorrection('FV/2024/1', new CreateCorrectiveInvoiceRequest(
            correctionReason: CorrectiveReasonType::Mistake,
            issueDate: '2024-03-01',
            positions: [$this->correctivePos()],
        ));

        $this->assertSame('ID1', $result->identifier);
    }

    // ── Shipping ─────────────────────────────────────────────────────────────

    public function testCreateShippingCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/fakturawysylka.json')
            ->willReturn($this->ok());

        $result = $this->service->createShipping(new CreateShippingInvoiceRequest(
            calculationBasis: CalculationBasis::Net,
            issueDate: '2024-03-01',
            paymentReceivedDate: '2024-03-01',
            saleDate: '2024-03-01',
            saleDateFormat: FormatDateSale::Daily,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            positions: [$this->pos()],
        ));

        $this->assertSame(0, $result->code);
    }

    // ── From Receipt ─────────────────────────────────────────────────────────

    public function testCreateFromReceiptCallsParagonEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/fakturaparagon.json')
            ->willReturn($this->ok());

        $this->service->createFromReceipt(new CreateInvoiceRequest(
            calculationBasis: CalculationBasis::Net,
            issueDate: '2024-03-01',
            saleDate: '2024-03-01',
            saleDateFormat: FormatDateSale::Daily,
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            contractor: $this->contractor(),
            positions: [$this->pos()],
        ));
    }

    // ── Foreign Currency ─────────────────────────────────────────────────────

    public function testCreateForeignCurrencyCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/fakturawaluta.json')
            ->willReturn($this->ok());

        $this->service->createForeignCurrency(new CreateForeignCurrencyInvoiceRequest(
            calculationBasis: CalculationBasis::Net,
            issueDate: '2024-03-01',
            saleDate: '2024-03-01',
            saleDateFormat: FormatDateSale::Daily,
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            saleType: ForeignInvoiceSaleType::Domestic,
            currency: Currency::EUR,
            positions: [$this->foreignPos()],
        ));
    }

    // ── Cash VAT ─────────────────────────────────────────────────────────────

    public function testCreateCashVatCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/fakturametodakasowa.json')
            ->willReturn($this->ok());

        $this->service->createCashVat(new CreateCashVatInvoiceRequest(
            settlementType: CashVatSettlementType::CashAccounting,
            calculationBasis: CalculationBasis::Net,
            issueDate: '2024-03-01',
            saleDate: '2024-03-01',
            saleDateFormat: FormatDateSale::Daily,
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            currency: Currency::PLN,
            positions: [$this->pos()],
        ));
    }

    // ── WDT ──────────────────────────────────────────────────────────────────

    public function testCreateWdtCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/fakturawdt.json')
            ->willReturn($this->ok());

        $this->service->createWdt(new CreateWdtInvoiceRequest(
            issueDate: '2024-03-01',
            saleDate: '2024-03-01',
            saleDateFormat: FormatDateSale::Daily,
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            currency: Currency::EUR,
            language: InvoiceLanguage::English,
            positions: [$this->foreignPos()],
        ));
    }

    // ── Export Goods ─────────────────────────────────────────────────────────

    public function testCreateExportGoodsCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/fakturaeksporttowarow.json')
            ->willReturn($this->ok());

        $this->service->createExportGoods(new CreateExportGoodsInvoiceRequest(
            issueDate: '2024-03-01',
            saleDate: '2024-03-01',
            saleDateFormat: FormatDateSale::Daily,
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            currency: Currency::PLN,
            language: InvoiceLanguage::English,
            positions: [$this->foreignPos()],
        ));
    }

    // ── EU Services ──────────────────────────────────────────────────────────

    public function testCreateEuServicesCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/fakturaeksportuslugue.json')
            ->willReturn($this->ok());

        $this->service->createEuServices(new CreateEuServicesInvoiceRequest(
            serviceName: 'Consulting',
            issueDate: '2024-03-01',
            saleDate: '2024-03-01',
            saleDateFormat: FormatDateSale::Daily,
            taxObligationDate: '2024-03-01',
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            currency: Currency::EUR,
            language: InvoiceLanguage::English,
            positions: [$this->foreignPos()],
        ));
    }

    // ── Export Services ──────────────────────────────────────────────────────

    public function testCreateExportServicesCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/fakturaeksportuslug.json')
            ->willReturn($this->ok());

        $this->service->createExportServices(new CreateExportServicesInvoiceRequest(
            serviceName: 'IT Services',
            serviceUnderArt28b: true,
            issueDate: '2024-03-01',
            saleDate: '2024-03-01',
            saleDateFormat: FormatDateSale::Daily,
            taxObligationDate: '2024-03-01',
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            currency: Currency::EUR,
            language: InvoiceLanguage::English,
            positions: [$this->foreignPos()],
        ));
    }

    // ── Pro Forma ─────────────────────────────────────────────────────────────

    public function testCreateProFormaCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/fakturaproformakraj.json')
            ->willReturn($this->ok());

        $this->service->createProForma(new CreateProFormaInvoiceRequest(
            calculationBasis: CalculationBasis::Net,
            invoiceType: ProFormaInvoiceType::Sale,
            issueDate: '2024-03-01',
            orderNumber: 'ORD/2024/1',
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            positions: [$this->pos()],
        ));
    }

    // ── Pro Forma Export ─────────────────────────────────────────────────────

    public function testCreateProFormaExportCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/fakturaproformaeksport.json')
            ->willReturn($this->ok());

        $this->service->createProFormaExport(new CreateProFormaExportInvoiceRequest(
            issueDate: '2024-03-01',
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            positions: [$this->foreignPos()],
        ));
    }

    // ── Receipt Document ─────────────────────────────────────────────────────

    public function testCreateReceiptCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/rachunekkraj.json')
            ->willReturn($this->ok());

        $this->service->createReceipt(new CreateReceiptDocumentRequest(
            issueDate: '2024-03-01',
            saleDate: '2024-03-01',
            saleDateFormat: FormatDateSale::Daily,
            paymentMethod: PaymentMethod::Transfer,
            positions: [$this->receiptPos()],
        ));
    }

    // ── OSS ──────────────────────────────────────────────────────────────────

    public function testCreateOssCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/fakturaoss.json')
            ->willReturn($this->ok());

        $this->service->createOss(new CreateOssInvoiceRequest(
            issueDate: '2024-03-01',
            saleDate: '2024-03-01',
            saleDateFormat: FormatDateSale::Daily,
            deliveryCountry: 'DE',
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            positions: [$this->ossPos()],
        ));
    }

    // ── IOSS ─────────────────────────────────────────────────────────────────

    public function testCreateIossCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/fakturaioss.json')
            ->willReturn($this->ok());

        $this->service->createIoss(new CreateIossInvoiceRequest(
            issueDate: '2024-03-01',
            saleDate: '2024-03-01',
            saleDateFormat: FormatDateSale::Daily,
            deliveryCountry: 'FR',
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            positions: [$this->ossPos()],
        ));
    }

    // ── getByType ────────────────────────────────────────────────────────────

    public function testGetByTypeBuildsPathFromTypeAndIdentifier(): void
    {
        $this->client->expects($this->once())
            ->method('get')
            ->with('/fakturawdt/42.json')
            ->willReturn(new ApiResponse(['data' => 'wdt']));

        $result = $this->service->getByType(KsefInvoiceType::Wdt, '42');

        $this->assertFalse($result->isEmpty());
    }

    public function testGetByTypeThrowsNotFoundWhenEmpty(): void
    {
        $this->expectException(InvoiceNotFoundException::class);

        $this->client->method('get')->willReturn(new ApiResponse([]));

        $this->service->getByType(KsefInvoiceType::Domestic, 'UNKNOWN');
    }

    public function testGetByTypeUsesFormatInPath(): void
    {
        $this->client->expects($this->once())
            ->method('get')
            ->with($this->stringContains('.xml'))
            ->willReturn(new ApiResponse(['x' => 1]));

        $this->service->getByType(KsefInvoiceType::Domestic, '1', InvoiceFormat::Xml);
    }

    // ── getPdfByType ─────────────────────────────────────────────────────────

    public function testGetPdfByTypeBuildsCorrectPath(): void
    {
        $this->client->expects($this->once())
            ->method('getRaw')
            ->with('/fakturawysylka/42.pdf', $this->anything(), [])
            ->willReturn('%PDF-1.4');

        $result = $this->service->getPdfByType(KsefInvoiceType::MailOrder, '42');

        $this->assertStringStartsWith('%PDF', $result);
    }

    public function testGetPdfByTypeAddsDuplicateQueryParam(): void
    {
        $this->client->expects($this->once())
            ->method('getRaw')
            ->with($this->anything(), $this->anything(), ['typ' => 'dup'])
            ->willReturn('%PDF-1.4');

        $this->service->getPdfByType(KsefInvoiceType::ExportGoods, '7', duplicate: true);
    }

    // ── sendToKsef ───────────────────────────────────────────────────────────

    public function testSendToKsefBuildsCorrectPath(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/fakturakraj/ksef/send/FV%2F1.json')
            ->willReturn($this->ok());

        $this->service->sendToKsef(KsefInvoiceType::Domestic, 'FV/1');
    }

    public function testSendToKsefPassesSendDate(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->anything(), ['DataWysylki' => '2024-06-01'])
            ->willReturn($this->ok());

        $this->service->sendToKsef(KsefInvoiceType::Domestic, '1', '2024-06-01');
    }

    // ── send ─────────────────────────────────────────────────────────────────

    public function testSendBuildsCorrectPathWithEmailQueryParam(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with(
                '/fakturakraj/send/42.json',
                $this->anything(),
                $this->anything(),
                $this->callback(static fn (array $p): bool => isset($p['wyslijEfaktura'])),
            )
            ->willReturn($this->ok());

        $this->service->send(KsefInvoiceType::Domestic, '42', byEmail: true);
    }

    public function testSendWithRequestBodyPassesFields(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with(
                '/fakturawysylka/send/7.json',
                $this->anything(),
                $this->callback(static fn (array $body): bool => isset($body['Tekst'])),
                $this->anything(),
            )
            ->willReturn($this->ok());

        $this->service->send(
            KsefInvoiceType::MailOrder,
            '7',
            new SendInvoiceRequest(message: 'Hello'),
            byPost: true,
        );
    }
}
