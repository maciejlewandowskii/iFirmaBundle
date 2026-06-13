<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Concern;

use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\Concern\IFirmaEntityTrait;
use maciejlewandowskii\iFirmaApi\Concern\OtherExpenseActionsTrait;
use maciejlewandowskii\iFirmaApi\Concern\VatExpenseActionsTrait;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaOtherExpenseInterface;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaVatExpenseInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\CreateOtherCostRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\CreateVatPurchaseRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\ExpenseContractorRequest;
use maciejlewandowskii\iFirmaApi\Enum\ExpenseDocumentType;
use maciejlewandowskii\iFirmaApi\Enum\KSeFDesignation;
use maciejlewandowskii\iFirmaApi\Enum\SaleType;
use maciejlewandowskii\iFirmaApi\iFirmaApi;
use maciejlewandowskii\iFirmaApi\Service\AccountingMonthService;
use maciejlewandowskii\iFirmaApi\Service\ContractorService;
use maciejlewandowskii\iFirmaApi\Service\EmployeeService;
use maciejlewandowskii\iFirmaApi\Service\ExpenseService;
use maciejlewandowskii\iFirmaApi\Service\InvoiceService;
use maciejlewandowskii\iFirmaApi\Service\OrderService;
use maciejlewandowskii\iFirmaApi\Service\PaymentService;
use maciejlewandowskii\iFirmaApi\Service\VatDictionaryService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class ExpenseActionsTraitTest extends TestCase
{
    private MockObject&iFirmaClientInterface $client;

    private iFirmaApi $api;

    protected function setUp(): void
    {
        $this->client = $this->createMock(iFirmaClientInterface::class);
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $this->api = new iFirmaApi(
            new InvoiceService($this->client, $validator),
            new ContractorService($this->client, $validator),
            new ExpenseService($this->client, $validator),
            new PaymentService($this->client, $validator),
            new OrderService($this->client, $validator),
            new AccountingMonthService($this->client, $validator),
            new VatDictionaryService($this->client, $validator),
            new EmployeeService($this->client, $validator),
        );
    }

    private function makeVatExpenseEntity(): IFirmaVatExpenseInterface
    {
        return new class implements IFirmaVatExpenseInterface {
            use IFirmaEntityTrait;
            use VatExpenseActionsTrait;

            public function toCreateVatPurchaseRequest(): CreateVatPurchaseRequest
            {
                return new CreateVatPurchaseRequest(
                    invoiceNumber: 'FV/2024/1',
                    kSeFDesignation: KSeFDesignation::Off,
                    issueDate: '2024-03-01',
                    saleType: SaleType::Taxable,
                    expenseName: 'Test expense',
                    netAmount23: 100.0,
                    netAmount08: 0.0,
                    netAmount05: 0.0,
                    netAmount00: 0.0,
                    netAmountExempt: 0.0,
                    vatAmount23: 23.0,
                    vatAmount08: 0.0,
                    vatAmount05: 0.0,
                );
            }
        };
    }

    private function makeOtherExpenseEntity(): IFirmaOtherExpenseInterface
    {
        return new class implements IFirmaOtherExpenseInterface {
            use IFirmaEntityTrait;
            use OtherExpenseActionsTrait;

            public function toCreateOtherCostRequest(): CreateOtherCostRequest
            {
                return new CreateOtherCostRequest(
                    documentType: ExpenseDocumentType::Receipt,
                    documentNumber: 'R/2024/1',
                    issueDate: '2024-03-01',
                    paymentDeadline: '2024-03-15',
                    expenseName: 'Receipt cost',
                    amount: 50.0,
                    contractor: new ExpenseContractorRequest(
                        name: 'Vendor',
                        postalCode: '00-001',
                        city: 'Warszawa',
                    ),
                );
            }
        };
    }

    public function testCreateVatPurchaseOniFirmaSetsId(): void
    {
        $this->client->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'EXP-1']));

        $entity = $this->makeVatExpenseEntity();
        $response = $entity->createVatPurchaseOniFirma($this->api);

        $this->assertSame('EXP-1', $response->id);
        $this->assertSame('EXP-1', $entity->getIFirmaId());
        $this->assertNotNull($entity->getIFirmaSyncedAt());
        $this->assertNotNull($entity->getIFirmaStateHash());
    }

    public function testCreateActivityCostOniFirma(): void
    {
        $this->client->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'ACT-1']));

        $entity = $this->makeVatExpenseEntity();
        $response = $entity->createActivityCostOniFirma($this->api);

        $this->assertSame('ACT-1', $response->id);
        $this->assertSame('ACT-1', $entity->getIFirmaId());
    }

    public function testCreatePhoneInternetCostOniFirma(): void
    {
        $this->client->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'PHN-1']));

        $entity = $this->makeVatExpenseEntity();
        $response = $entity->createPhoneInternetCostOniFirma($this->api);

        $this->assertSame('PHN-1', $response->id);
    }

    public function testVatExpenseIsSyncStale(): void
    {
        $entity = $this->makeVatExpenseEntity();
        $this->assertTrue($entity->isSyncStale());

        $entity->setIFirmaId('EXP-1');
        $entity->setIFirmaStateHash('wrong');
        $this->assertTrue($entity->isSyncStale());
    }

    public function testVatExpenseIsNotStaleAfterSync(): void
    {
        $this->client->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'EXP-1']));

        $entity = $this->makeVatExpenseEntity();
        $entity->createVatPurchaseOniFirma($this->api);

        $this->assertFalse($entity->isSyncStale());
    }

    public function testCreateOtherCostOniFirmaSetsId(): void
    {
        $this->client->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'OTH-1']));

        $entity = $this->makeOtherExpenseEntity();
        $response = $entity->createOtherCostOniFirma($this->api);

        $this->assertSame('OTH-1', $response->id);
        $this->assertSame('OTH-1', $entity->getIFirmaId());
        $this->assertNotNull($entity->getIFirmaSyncedAt());
    }

    public function testOtherExpenseIsSyncStale(): void
    {
        $entity = $this->makeOtherExpenseEntity();
        $this->assertTrue($entity->isSyncStale());
    }

    public function testOtherExpenseIsNotStaleAfterSync(): void
    {
        $this->client->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'OTH-1']));

        $entity = $this->makeOtherExpenseEntity();
        $entity->createOtherCostOniFirma($this->api);

        $this->assertFalse($entity->isSyncStale());
    }

    public function testVatExpenseDoesNotSetIdWhenResponseIdIsNull(): void
    {
        $this->client->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK']));

        $entity = $this->makeVatExpenseEntity();
        $entity->createVatPurchaseOniFirma($this->api);

        $this->assertNull($entity->getIFirmaId());
        $this->assertNotNull($entity->getIFirmaSyncedAt());
    }
}
