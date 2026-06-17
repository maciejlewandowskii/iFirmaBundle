<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Integration;

use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CorrectiveInvoicePositionRequest;
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
use maciejlewandowskii\iFirmaApi\Enum\CorrectiveReasonType;
use maciejlewandowskii\iFirmaApi\Enum\Currency;
use maciejlewandowskii\iFirmaApi\Enum\ForeignInvoiceSaleType;
use maciejlewandowskii\iFirmaApi\Enum\FormatDateSale;
use maciejlewandowskii\iFirmaApi\Enum\InvoiceLanguage;
use maciejlewandowskii\iFirmaApi\Enum\KsefInvoiceType;
use maciejlewandowskii\iFirmaApi\Enum\OssVatRateType;
use maciejlewandowskii\iFirmaApi\Enum\PaymentMethod;
use maciejlewandowskii\iFirmaApi\Enum\ProFormaInvoiceType;
use maciejlewandowskii\iFirmaApi\Enum\RecipientSignatureType;
use maciejlewandowskii\iFirmaApi\Enum\VatRate;
use maciejlewandowskii\iFirmaApi\Enum\VatRateType;
use maciejlewandowskii\iFirmaApi\Exception\ApiException;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;

use function sprintf;

#[Group('integration')]
final class InvoiceTypesIntegrationTest extends IntegrationTestCase
{
    private string $accountingDate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accountingDate = $this->accountingDate();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function plnPosition(): InvoicePositionRequest
    {
        return new InvoicePositionRequest(
            name: 'Usługa testowa',
            unit: 'szt',
            vatRateType: VatRateType::Percentage,
            quantity: 1.0,
            unitPrice: 100.0,
            vatRate: VatRate::TwentyThree,
        );
    }

    private function foreignPosition(): ForeignInvoicePositionRequest
    {
        return new ForeignInvoicePositionRequest(
            name: 'Test service',
            foreignName: 'Test service',
            unit: 'szt',
            foreignUnit: 'pcs',
            vatRateType: VatRateType::Percentage,
            quantity: 1.0,
            unitPrice: 100.0,
            vatRate: VatRate::Zero,
        );
    }

    private function foreignContractor(string $label = ''): InvoiceContractorRequest
    {
        return new InvoiceContractorRequest(
            name: 'Foreign Test GmbH ' . $label,
            postalCode: '10115',
            city: 'Berlin',
            country: 'Niemcy',
            countryCode: 'DE',
        );
    }

    private function euVatContractor(string $label = ''): InvoiceContractorRequest
    {
        return new InvoiceContractorRequest(
            name: 'EU VAT Test GmbH ' . $label,
            postalCode: '10115',
            city: 'Berlin',
            euPrefix: 'DE',
            taxId: '123456789',
            country: 'Niemcy',
            countryCode: 'DE',
        );
    }

    // ── Shipping invoice (fakturawysylka) ─────────────────────────────────────

    public function testCreateShippingInvoice(): string
    {
        try {
            $response = $this->api()->invoiceService->createShipping(new CreateShippingInvoiceRequest(
                calculationBasis: CalculationBasis::Net,
                issueDate: $this->accountingDate,
                paymentReceivedDate: $this->accountingDate,
                saleDate: $this->accountingDate,
                saleDateFormat: FormatDateSale::Daily,
                recipientSignatureType: RecipientSignatureType::WithoutSignatures,
                contractor: new InvoiceContractorRequest(
                    name: 'Shipping Test Client ' . time(),
                    postalCode: '00-001',
                    city: 'Warszawa',
                ),
                positions: [$this->plnPosition()],
                paymentMethod: PaymentMethod::Transfer,
                notes: 'Integration test — shipping invoice',
            ));
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->identifier);

        return $response->identifier;
    }

    #[Depends('testCreateShippingInvoice')]
    public function testGetShippingInvoiceByType(string $invoiceId): void
    {
        $data = $this->api()->invoiceService->getByType(KsefInvoiceType::MailOrder, $invoiceId);

        $this->assertFalse($data->isEmpty());
    }

    #[Depends('testCreateShippingInvoice')]
    public function testGetShippingInvoicePdfByType(string $invoiceId): void
    {
        $pdf = $this->api()->invoiceService->getPdfByType(KsefInvoiceType::MailOrder, $invoiceId);

        $this->assertNotEmpty($pdf);
    }

    // ── From receipt (fakturaparagon) ─────────────────────────────────────────

    public function testCreateFromReceiptInvoice(): void
    {
        try {
            $response = $this->api()->invoiceService->createFromReceipt(new CreateInvoiceRequest(
                calculationBasis: CalculationBasis::Net,
                issueDate: $this->accountingDate,
                saleDate: $this->accountingDate,
                saleDateFormat: FormatDateSale::Daily,
                paymentMethod: PaymentMethod::Cash,
                recipientSignatureType: RecipientSignatureType::WithoutSignatures,
                contractor: new InvoiceContractorRequest(
                    name: 'Paragon Test Client ' . time(),
                    postalCode: '00-001',
                    city: 'Warszawa',
                    taxId: '5252344078',
                ),
                positions: [$this->plnPosition()],
                notes: 'Integration test — invoice from receipt',
            ));
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->identifier);
    }

    // ── Foreign currency (fakturawaluta) ──────────────────────────────────────

    public function testCreateForeignCurrencyInvoice(): string
    {
        try {
            $response = $this->api()->invoiceService->createForeignCurrency(new CreateForeignCurrencyInvoiceRequest(
                calculationBasis: CalculationBasis::Net,
                issueDate: $this->accountingDate,
                saleDate: $this->accountingDate,
                saleDateFormat: FormatDateSale::Daily,
                paymentMethod: PaymentMethod::Transfer,
                recipientSignatureType: RecipientSignatureType::WithoutSignatures,
                saleType: ForeignInvoiceSaleType::Domestic,
                currency: Currency::EUR,
                contractor: $this->foreignContractor((string) time()),
                positions: [$this->foreignPosition()],
                language: InvoiceLanguage::English,
                notes: 'Integration test — foreign currency invoice',
            ));
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->identifier);

        return $response->identifier;
    }

    #[Depends('testCreateForeignCurrencyInvoice')]
    public function testGetForeignCurrencyInvoiceByType(string $invoiceId): void
    {
        $data = $this->api()->invoiceService->getByType(KsefInvoiceType::ForeignCurrency, $invoiceId);

        $this->assertFalse($data->isEmpty());
    }

    // ── WDT (fakturawdt) ──────────────────────────────────────────────────────

    public function testCreateWdtInvoice(): void
    {
        try {
            $response = $this->api()->invoiceService->createWdt(new CreateWdtInvoiceRequest(
                issueDate: $this->accountingDate,
                saleDate: $this->accountingDate,
                saleDateFormat: FormatDateSale::Daily,
                paymentMethod: PaymentMethod::Transfer,
                recipientSignatureType: RecipientSignatureType::WithoutSignatures,
                currency: Currency::EUR,
                language: InvoiceLanguage::English,
                contractor: $this->euVatContractor((string) time()),
                positions: [$this->foreignPosition()],
                notes: 'Integration test — WDT invoice',
            ));
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->identifier);
    }

    // ── Export goods (fakturaeksporttowarow) ──────────────────────────────────

    public function testCreateExportGoodsInvoice(): string
    {
        try {
            $response = $this->api()->invoiceService->createExportGoods(new CreateExportGoodsInvoiceRequest(
                issueDate: $this->accountingDate,
                saleDate: $this->accountingDate,
                saleDateFormat: FormatDateSale::Daily,
                paymentMethod: PaymentMethod::Transfer,
                recipientSignatureType: RecipientSignatureType::WithoutSignatures,
                currency: Currency::USD,
                language: InvoiceLanguage::English,
                contractor: $this->foreignContractor((string) time()),
                positions: [$this->foreignPosition()],
                notes: 'Integration test — export goods invoice',
            ));
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->identifier);

        return $response->identifier;
    }

    #[Depends('testCreateExportGoodsInvoice')]
    public function testGetExportGoodsInvoicePdf(string $invoiceId): void
    {
        $pdf = $this->api()->invoiceService->getPdfByType(KsefInvoiceType::ExportGoods, $invoiceId);

        $this->assertNotEmpty($pdf);
    }

    // ── EU services (fakturaeksportuslugue) ───────────────────────────────────

    public function testCreateEuServicesInvoice(): void
    {
        try {
            $response = $this->api()->invoiceService->createEuServices(new CreateEuServicesInvoiceRequest(
                serviceName: 'IT Consulting',
                issueDate: $this->accountingDate,
                saleDate: $this->accountingDate,
                saleDateFormat: FormatDateSale::Daily,
                taxObligationDate: $this->accountingDate,
                paymentMethod: PaymentMethod::Transfer,
                recipientSignatureType: RecipientSignatureType::WithoutSignatures,
                currency: Currency::EUR,
                language: InvoiceLanguage::English,
                contractor: $this->euVatContractor((string) time()),
                positions: [$this->foreignPosition()],
                notes: 'Integration test — EU services invoice',
            ));
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->identifier);
    }

    // ── Export services (fakturaeksportuslug) ─────────────────────────────────

    public function testCreateExportServicesInvoice(): void
    {
        try {
            $response = $this->api()->invoiceService->createExportServices(new CreateExportServicesInvoiceRequest(
                serviceName: 'Software Development',
                serviceUnderArt28b: false,
                issueDate: $this->accountingDate,
                saleDate: $this->accountingDate,
                saleDateFormat: FormatDateSale::Daily,
                taxObligationDate: $this->accountingDate,
                paymentMethod: PaymentMethod::Transfer,
                recipientSignatureType: RecipientSignatureType::WithoutSignatures,
                currency: Currency::USD,
                language: InvoiceLanguage::English,
                contractor: $this->foreignContractor((string) time()),
                positions: [$this->foreignPosition()],
            ));
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->identifier);
    }

    // ── Pro forma domestic (fakturaproformakraj) ──────────────────────────────

    public function testCreateProFormaInvoice(): string
    {
        try {
            $response = $this->api()->invoiceService->createProForma(new CreateProFormaInvoiceRequest(
                calculationBasis: CalculationBasis::Net,
                invoiceType: ProFormaInvoiceType::Sale,
                issueDate: $this->accountingDate,
                orderNumber: sprintf('ORD-%d', time()),
                paymentMethod: PaymentMethod::Transfer,
                recipientSignatureType: RecipientSignatureType::WithoutSignatures,
                contractor: new InvoiceContractorRequest(
                    name: 'ProForma Test Client ' . time(),
                    postalCode: '00-001',
                    city: 'Warszawa',
                    taxId: '5252344078',
                ),
                positions: [$this->plnPosition()],
                paymentDeadline: $this->addDays($this->accountingDate, 14),
            ));
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->identifier);

        return $response->identifier;
    }

    #[Depends('testCreateProFormaInvoice')]
    public function testGetProFormaByType(string $invoiceId): void
    {
        $data = $this->api()->invoiceService->getByType(KsefInvoiceType::Domestic, $invoiceId);

        $this->assertFalse($data->isEmpty());
    }

    // ── Pro forma export (fakturaproformaeksport) ─────────────────────────────

    public function testCreateProFormaExportInvoice(): void
    {
        try {
            $response = $this->api()->invoiceService->createProFormaExport(new CreateProFormaExportInvoiceRequest(
                issueDate: $this->accountingDate,
                paymentMethod: PaymentMethod::Transfer,
                recipientSignatureType: RecipientSignatureType::WithoutSignatures,
                contractor: $this->foreignContractor((string) time()),
                positions: [$this->foreignPosition()],
                language: InvoiceLanguage::English,
                currency: Currency::EUR,
            ));
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->identifier);
    }

    // ── Corrective invoice (fakturakraj/korekta) ──────────────────────────────

    public function testCreateCorrectiveInvoice(): void
    {
        // First create a domestic invoice to correct
        $original = $this->api()->invoiceService->create(new CreateInvoiceRequest(
            calculationBasis: CalculationBasis::Net,
            issueDate: $this->accountingDate,
            saleDate: $this->accountingDate,
            saleDateFormat: FormatDateSale::Daily,
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            contractor: new InvoiceContractorRequest(
                name: 'Correction Source Client ' . time(),
                postalCode: '00-001',
                city: 'Warszawa',
            ),
            positions: [
                new InvoicePositionRequest(
                    name: 'To be corrected',
                    unit: 'szt',
                    vatRateType: VatRateType::Percentage,
                    quantity: 2.0,
                    unitPrice: 50.0,
                    vatRate: VatRate::TwentyThree,
                ),
            ],
        ));

        $this->assertSame(0, $original->code);

        try {
            $corrective = $this->api()->invoiceService->createCorrection(
                $original->identifier,
                new CreateCorrectiveInvoiceRequest(
                    correctionReason: CorrectiveReasonType::Mistake,
                    issueDate: $this->accountingDate,
                    positions: [
                        new CorrectiveInvoicePositionRequest(
                            name: 'To be corrected',
                            unit: 'szt',
                            vatRateType: VatRateType::Percentage,
                            quantity: 0.0,
                            unitPrice: 50.0,
                            vatRate: VatRate::TwentyThree,
                        ),
                    ],
                ),
            );
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $corrective->code);
        $this->assertNotEmpty($corrective->identifier);
    }

    // ── Receipt document (rachunekkraj) ───────────────────────────────────────

    public function testCreateReceiptDocument(): void
    {
        try {
            $response = $this->api()->invoiceService->createReceipt(new CreateReceiptDocumentRequest(
                issueDate: $this->accountingDate,
                saleDate: $this->accountingDate,
                saleDateFormat: FormatDateSale::Daily,
                paymentMethod: PaymentMethod::Transfer,
                contractor: new InvoiceContractorRequest(
                    name: 'Receipt Test Client ' . time(),
                    postalCode: '00-001',
                    city: 'Warszawa',
                ),
                positions: [
                    new ReceiptDocumentPositionRequest(
                        name: 'Usługa testowa',
                        unit: 'godz',
                        quantity: 1.0,
                        unitPrice: 100.0,
                    ),
                ],
            ));
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->identifier);
    }

    // ── OSS invoice (fakturaoss) ──────────────────────────────────────────────

    public function testCreateOssInvoice(): void
    {
        try {
            $response = $this->api()->invoiceService->createOss(new CreateOssInvoiceRequest(
                issueDate: $this->accountingDate,
                saleDate: $this->accountingDate,
                saleDateFormat: FormatDateSale::Daily,
                deliveryCountry: 'DE',
                recipientSignatureType: RecipientSignatureType::WithoutSignatures,
                contractor: $this->foreignContractor((string) time()),
                positions: [
                    new OssInvoicePositionRequest(
                        name: 'Product',
                        unit: 'szt',
                        unitPrice: 100.0,
                        quantity: 1.0,
                        vatRate: 0.19,
                        vatRateType: OssVatRateType::Standard,
                        foreignName: 'Product',
                        foreignUnit: 'pcs',
                    ),
                ],
                language: InvoiceLanguage::English,
                currency: Currency::EUR,
            ));
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->identifier);
    }

    // ── IOSS invoice (fakturaioss) ────────────────────────────────────────────

    public function testCreateIossInvoice(): void
    {
        try {
            $response = $this->api()->invoiceService->createIoss(new CreateIossInvoiceRequest(
                issueDate: $this->accountingDate,
                saleDate: $this->accountingDate,
                saleDateFormat: FormatDateSale::Daily,
                deliveryCountry: 'FR',
                recipientSignatureType: RecipientSignatureType::WithoutSignatures,
                contractor: $this->foreignContractor((string) time()),
                positions: [
                    new OssInvoicePositionRequest(
                        name: 'Product',
                        unit: 'szt',
                        unitPrice: 50.0,
                        quantity: 2.0,
                        vatRate: 0.20,
                        vatRateType: OssVatRateType::Standard,
                        foreignName: 'Product',
                        foreignUnit: 'pcs',
                    ),
                ],
                language: InvoiceLanguage::English,
                currency: Currency::EUR,
                orderNumber: sprintf('ORD-%d', time()),
                parcelNumber: sprintf('PKG-%d', time()),
            ));
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->identifier);
    }

    // ── Send to KSeF ──────────────────────────────────────────────────────────

    public function testSendToKsefSkipsGracefullyIfNotConfigured(): void
    {
        // Create an invoice to attempt KSeF submission
        $invoice = $this->api()->invoiceService->create(new CreateInvoiceRequest(
            calculationBasis: CalculationBasis::Net,
            issueDate: $this->accountingDate,
            saleDate: $this->accountingDate,
            saleDateFormat: FormatDateSale::Daily,
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            contractor: new InvoiceContractorRequest(
                name: 'KSeF Test Client ' . time(),
                postalCode: '00-001',
                city: 'Warszawa',
            ),
            positions: [$this->plnPosition()],
        ));

        $this->assertSame(0, $invoice->code);

        try {
            $this->api()->invoiceService->sendToKsef(KsefInvoiceType::Domestic, $invoice->identifier);

            // If no exception — KSeF worked
            $this->addToAssertionCount(1);
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }
    }

    // ── Send by email / post ──────────────────────────────────────────────────

    public function testSendByEmailSkipsGracefullyIfNotConfigured(): void
    {
        $invoice = $this->api()->invoiceService->create(new CreateInvoiceRequest(
            calculationBasis: CalculationBasis::Net,
            issueDate: $this->accountingDate,
            saleDate: $this->accountingDate,
            saleDateFormat: FormatDateSale::Daily,
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            contractor: new InvoiceContractorRequest(
                name: 'Send Test Client ' . time(),
                postalCode: '00-001',
                city: 'Warszawa',
                email: 'test@example.com',
            ),
            positions: [$this->plnPosition()],
        ));

        $this->assertSame(0, $invoice->code);

        try {
            $this->api()->invoiceService->send(
                KsefInvoiceType::Domestic,
                $invoice->identifier,
                new SendInvoiceRequest(message: 'Integration test send'),
                byEmail: true,
            );

            $this->addToAssertionCount(1);
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }
    }
}
