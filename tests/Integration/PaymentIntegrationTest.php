<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Integration;

use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoiceContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoicePositionRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Payment\RegisterPaymentRequest;
use maciejlewandowskii\iFirmaApi\Enum\CalculationBasis;
use maciejlewandowskii\iFirmaApi\Enum\FormatDateSale;
use maciejlewandowskii\iFirmaApi\Enum\PaymentMethod;
use maciejlewandowskii\iFirmaApi\Enum\RecipientSignatureType;
use maciejlewandowskii\iFirmaApi\Enum\VatRate;
use maciejlewandowskii\iFirmaApi\Enum\VatRateType;
use PHPUnit\Framework\Attributes\Group;

use function sprintf;

#[Group('integration')]
final class PaymentIntegrationTest extends IntegrationTestCase
{
    private string $accountingDate;

    protected function setUp(): void
    {
        parent::setUp();

        $month = $this->api()->accountingMonthService->get();
        $this->accountingDate = sprintf('%04d-%02d-01', $month->year, $month->month);
    }

    private function createTestInvoice(string $contractorName): string
    {
        $response = $this->api()->invoiceService->create(new CreateInvoiceRequest(
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
            ),
            positions: [
                new InvoicePositionRequest(
                    name: 'Usługa testowa',
                    unit: 'szt',
                    vatRateType: VatRateType::Percentage,
                    quantity: 1.0,
                    unitPrice: 100.00,
                    vatRate: VatRate::TwentyThree,
                ),
            ],
        ));

        $this->assertSame(0, $response->code);

        return $response->identifier;
    }

    public function testRegisterFullPaymentOnInvoice(): void
    {
        $invoiceId = $this->createTestInvoice('Payment Test Client ' . time());

        $paymentResponse = $this->api()->paymentService->register(new RegisterPaymentRequest(
            invoiceType: 'prz_faktura_kraj',
            invoiceNumber: $invoiceId,
            amount: 123.00,
            date: $this->accountingDate,
        ));

        $this->assertSame(0, $paymentResponse->code);
        $this->assertNotEmpty($paymentResponse->message);
    }

    public function testRegisterPartialPayment(): void
    {
        $invoiceId = $this->createTestInvoice('Partial Payment Test ' . time());

        $paymentResponse = $this->api()->paymentService->register(new RegisterPaymentRequest(
            invoiceType: 'prz_faktura_kraj',
            invoiceNumber: $invoiceId,
            amount: 100.00,
            date: $this->accountingDate,
        ));

        $this->assertSame(0, $paymentResponse->code);
    }
}
