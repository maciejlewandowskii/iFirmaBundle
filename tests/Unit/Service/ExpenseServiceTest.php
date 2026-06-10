<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Service;

use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\CreateOtherCostRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\CreateVatPurchaseRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\ExpenseContractorRequest;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use maciejlewandowskii\iFirmaApi\Enum\ExpenseDocumentType;
use maciejlewandowskii\iFirmaApi\Enum\KSeFDesignation;
use maciejlewandowskii\iFirmaApi\Enum\SaleType;
use maciejlewandowskii\iFirmaApi\Service\ExpenseService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class ExpenseServiceTest extends TestCase
{
    private MockObject&iFirmaClientInterface $client;

    private ExpenseService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(iFirmaClientInterface::class);
        $this->service = new ExpenseService(
            $this->client,
            Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator(),
        );
    }

    private function makeVatRequest(): CreateVatPurchaseRequest
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

    private function makeOtherCostRequest(): CreateOtherCostRequest
    {
        return new CreateOtherCostRequest(
            documentType: ExpenseDocumentType::Receipt,
            documentNumber: 'R/2024/1',
            issueDate: '2024-03-01',
            paymentDeadline: '2024-03-15',
            expenseName: 'Receipt cost',
            amount: 50.0,
            contractor: new ExpenseContractorRequest(
                name: 'Vendor Sp. z o.o.',
                postalCode: '00-001',
                city: 'Warszawa',
            ),
        );
    }

    public function testCreateVatPurchasePostsToCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/zakuptowaruvat.json', AuthKeyType::Expense)
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK']));

        $result = $this->service->createVatPurchase($this->makeVatRequest());

        $this->assertSame(0, $result->code);
    }

    public function testCreateActivityCostPostsToCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/kosztdzialalnoscivat.json', AuthKeyType::Expense)
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'Created', 'Wynik' => '42']));

        $result = $this->service->createActivityCost($this->makeVatRequest());

        $this->assertSame('42', $result->id);
    }

    public function testCreateOtherCostPostsToCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/kosztdzialalnosci.json', AuthKeyType::Expense)
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK']));

        $result = $this->service->createOtherCost($this->makeOtherCostRequest());

        $this->assertSame(0, $result->code);
        $this->assertNull($result->id);
    }

    public function testCreatePhoneInternetCostPostsToCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/oplatatelefon.json', AuthKeyType::Expense)
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => '7']));

        $result = $this->service->createPhoneInternetCost($this->makeVatRequest());

        $this->assertSame('7', $result->id);
    }
}
