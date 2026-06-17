<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Integration;

use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoiceContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoiceListRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoicePositionRequest;
use maciejlewandowskii\iFirmaApi\Enum\CalculationBasis;
use maciejlewandowskii\iFirmaApi\Enum\FormatDateSale;
use maciejlewandowskii\iFirmaApi\Enum\GtuCode;
use maciejlewandowskii\iFirmaApi\Enum\InvoiceStatus;
use maciejlewandowskii\iFirmaApi\Enum\PaymentMethod;
use maciejlewandowskii\iFirmaApi\Enum\RecipientSignatureType;
use maciejlewandowskii\iFirmaApi\Enum\VatRate;
use maciejlewandowskii\iFirmaApi\Enum\VatRateType;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;

use function sprintf;

#[Group('integration')]
final class InvoiceIntegrationTest extends IntegrationTestCase
{
    private string $accountingDate;

    protected function setUp(): void
    {
        parent::setUp();

        // iFirma requires sale/issue date to match the active accounting month.
        $month = $this->api()->accountingMonthService->get();
        $this->accountingDate = sprintf('%04d-%02d-01', $month->year, $month->month);
    }

    private function endOfMonth(string $date): string
    {
        // Day 0 of the next month == last day of the current month
        $ts = mktime(0, 0, 0, (int) mb_substr($date, 5, 2) + 1, 0, (int) mb_substr($date, 0, 4));

        return false !== $ts ? date('Y-m-d', $ts) : $date;
    }

    private function buildMinimalInvoiceRequest(string $contractorName): CreateInvoiceRequest
    {
        return new CreateInvoiceRequest(
            calculationBasis: CalculationBasis::Net,
            issueDate: $this->accountingDate,
            saleDate: $this->accountingDate,
            saleDateFormat: FormatDateSale::Daily,
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            contractor: new InvoiceContractorRequest(
                name: $contractorName,
                postalCode: '00-001',
                city: 'Warszawa',
                taxId: '5252344078',
            ),
            positions: [
                new InvoicePositionRequest(
                    name: 'Usługa programistyczna',
                    unit: 'godz',
                    vatRateType: VatRateType::Percentage,
                    quantity: 1.0,
                    unitPrice: 100.00,
                    vatRate: VatRate::TwentyThree,
                    gtu: GtuCode::Gtu12,
                ),
            ],
            paymentDeadline: $this->addDays($this->accountingDate, 14),
            notes: 'Faktura testowa — integration test',
        );
    }

    public function testCreateInvoice(): string
    {
        $response = $this->api()->invoiceService->create(
            $this->buildMinimalInvoiceRequest('Integration Test Client ' . time()),
        );

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->identifier);

        return $response->identifier;
    }

    #[Depends('testCreateInvoice')]
    public function testGetInvoiceAsJson(string $invoiceId): string
    {
        $data = $this->api()->invoiceService->get($invoiceId);

        $this->assertFalse($data->isEmpty());

        return $invoiceId;
    }

    #[Depends('testGetInvoiceAsJson')]
    public function testGetInvoiceAsPdf(string $invoiceId): void
    {
        $pdf = $this->api()->invoiceService->getPdf($invoiceId);

        $this->assertIsString($pdf);
        $this->assertNotEmpty($pdf);
    }

    public function testListInvoicesReturnsGenerator(): void
    {
        $month = $this->api()->accountingMonthService->get();
        $dateFrom = sprintf('%04d-%02d-01', $month->year, $month->month);
        $dateTo = $this->endOfMonth($dateFrom);

        $generator = $this->api()->invoiceService->list(new InvoiceListRequest(
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            perPage: 5,
        ));

        $this->assertIsIterable($generator);

        foreach ($generator as $item) {
            $this->assertNotEmpty($item->fullNumber);
            $this->assertIsFloat($item->grossAmount);
        }
    }

    public function testListInvoicesWithStatusFilterReturnsIterable(): void
    {
        $month = $this->api()->accountingMonthService->get();
        $dateFrom = sprintf('%04d-%02d-01', $month->year, $month->month);

        $invoices = iterator_to_array(
            $this->api()->invoiceService->list(new InvoiceListRequest(
                dateFrom: $dateFrom,
                status: [InvoiceStatus::Unpaid],
                perPage: 3,
            )),
        );

        // May return zero results — just assert no exception was thrown.
        $this->assertIsArray($invoices);
    }

    public function testCreateInvoiceWithMultiplePositions(): void
    {
        $request = new CreateInvoiceRequest(
            calculationBasis: CalculationBasis::Net,
            issueDate: $this->accountingDate,
            saleDate: $this->accountingDate,
            saleDateFormat: FormatDateSale::Daily,
            paymentMethod: PaymentMethod::Transfer,
            recipientSignatureType: RecipientSignatureType::WithoutSignatures,
            contractor: new InvoiceContractorRequest(
                name: 'Multi-position Test Client ' . time(),
                postalCode: '30-001',
                city: 'Kraków',
            ),
            positions: [
                new InvoicePositionRequest(
                    name: 'Konsultacja',
                    unit: 'godz',
                    vatRateType: VatRateType::Percentage,
                    quantity: 2.0,
                    unitPrice: 200.00,
                    vatRate: VatRate::TwentyThree,
                ),
                new InvoicePositionRequest(
                    name: 'Dokumentacja',
                    unit: 'szt',
                    vatRateType: VatRateType::Percentage,
                    quantity: 1.0,
                    unitPrice: 50.00,
                    vatRate: VatRate::TwentyThree,
                    discount: 10.0,
                ),
            ],
            paymentDeadline: $this->addDays($this->accountingDate, 30),
        );

        $response = $this->api()->invoiceService->create($request);

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->identifier);
    }
}
