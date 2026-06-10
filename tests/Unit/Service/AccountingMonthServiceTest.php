<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Service;

use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\AccountingMonth\ChangeAccountingMonthRequest;
use maciejlewandowskii\iFirmaApi\Enum\AccountingMonthDirection;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use maciejlewandowskii\iFirmaApi\Service\AccountingMonthService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class AccountingMonthServiceTest extends TestCase
{
    private MockObject&iFirmaClientInterface $client;

    private AccountingMonthService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(iFirmaClientInterface::class);
        $this->service = new AccountingMonthService(
            $this->client,
            Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator(),
        );
    }

    public function testGetReturnsCorrectAccountingMonth(): void
    {
        $this->client->expects($this->once())
            ->method('get')
            ->with('/abonent/miesiacksiegowy.json', AuthKeyType::Subscriber)
            ->willReturn(new ApiResponse(['MiesiacKsiegowy' => 3, 'RokKsiegowy' => 2024]));

        $result = $this->service->get();

        $this->assertSame(3, $result->month);
        $this->assertSame(2024, $result->year);
    }

    public function testChangeCallsPutThenReturnsUpdatedMonth(): void
    {
        $this->client->expects($this->once())
            ->method('put')
            ->with('/abonent/miesiacksiegowy.json', AuthKeyType::Subscriber, $this->callback(
                static fn (array $body): bool => $body['MiesiacKsiegowy'] === AccountingMonthDirection::Next->value,
            ))
            ->willReturn(new ApiResponse([]));

        $this->client->expects($this->once())
            ->method('get')
            ->willReturn(new ApiResponse(['MiesiacKsiegowy' => 4, 'RokKsiegowy' => 2024]));

        $result = $this->service->change(new ChangeAccountingMonthRequest(AccountingMonthDirection::Next));

        $this->assertSame(4, $result->month);
    }

    public function testGetApiLimitReturnsUsedAndGranted(): void
    {
        $this->client->expects($this->once())
            ->method('get')
            ->with('/abonent/limit.json', AuthKeyType::Subscriber)
            ->willReturn(new ApiResponse(['LimitWykorzystany' => 42, 'LimitPrzyznany' => 2000]));

        $result = $this->service->getApiLimit();

        $this->assertSame(42, $result->used);
        $this->assertSame(2000, $result->granted);
    }

    public function testChangeWithTransferDataFlag(): void
    {
        $this->client->expects($this->once())
            ->method('put')
            ->with('/abonent/miesiacksiegowy.json', AuthKeyType::Subscriber, $this->callback(
                static fn (array $body): bool => true === $body['PrzeniesDaneZPoprzedniegoRoku'],
            ))
            ->willReturn(new ApiResponse([]));

        $this->client->method('get')
            ->willReturn(new ApiResponse(['MiesiacKsiegowy' => 1, 'RokKsiegowy' => 2025]));

        $this->service->change(new ChangeAccountingMonthRequest(AccountingMonthDirection::Previous, true));
    }
}
