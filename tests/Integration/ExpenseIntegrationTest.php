<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Integration;

use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\CreateOtherCostRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\CreateVatPurchaseRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\ExpenseContractorRequest;
use maciejlewandowskii\iFirmaApi\Enum\ExpenseDocumentType;
use maciejlewandowskii\iFirmaApi\Enum\KSeFDesignation;
use maciejlewandowskii\iFirmaApi\Enum\SaleType;
use maciejlewandowskii\iFirmaApi\Exception\ApiException;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
final class ExpenseIntegrationTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireExpenseKey();
    }

    public function testCreateVatPurchase(): void
    {
        try {
            $response = $this->api()->expenseService->createVatPurchase(new CreateVatPurchaseRequest(
                invoiceNumber: 'FV/TEST/' . time(),
                kSeFDesignation: KSeFDesignation::Off,
                issueDate: date('Y-m-d'),
                saleType: SaleType::Taxable,
                expenseName: 'Zakup sprzętu testowego',
                netAmount23: 1000.00,
                netAmount08: 0.00,
                netAmount05: 0.00,
                netAmount00: 0.00,
                netAmountExempt: 0.00,
                vatAmount23: 230.00,
                vatAmount08: 0.00,
                vatAmount05: 0.00,
                contractor: new ExpenseContractorRequest(
                    name: 'Dostawca Test Sp. z o.o.',
                    postalCode: '00-001',
                    city: 'Warszawa',
                    taxId: '5252344078',
                ),
            ));
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->message);
    }

    public function testCreateActivityCost(): void
    {
        try {
            $response = $this->api()->expenseService->createActivityCost(new CreateVatPurchaseRequest(
                invoiceNumber: 'KOSZT/TEST/' . time(),
                kSeFDesignation: KSeFDesignation::Off,
                issueDate: date('Y-m-d'),
                saleType: SaleType::Taxable,
                expenseName: 'Koszt działalności testowej',
                netAmount23: 500.00,
                netAmount08: 0.00,
                netAmount05: 0.00,
                netAmount00: 0.00,
                netAmountExempt: 0.00,
                vatAmount23: 115.00,
                vatAmount08: 0.00,
                vatAmount05: 0.00,
            ));
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $response->code);
    }

    public function testCreateOtherCost(): void
    {
        try {
            $response = $this->api()->expenseService->createOtherCost(new CreateOtherCostRequest(
                documentType: ExpenseDocumentType::Receipt,
                documentNumber: 'RACH-TEST-' . time(),
                issueDate: date('Y-m-d'),
                paymentDeadline: date('Y-m-d', strtotime('+14 days')),
                expenseName: 'Rachunek testowy',
                amount: 50.00,
                contractor: new ExpenseContractorRequest(
                    name: 'Dostawca Test Sp. z o.o.',
                    postalCode: '00-001',
                    city: 'Warszawa',
                ),
            ));
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }

        $this->assertSame(0, $response->code);
    }
}
