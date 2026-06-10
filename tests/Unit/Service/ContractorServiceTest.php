<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Service;

use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Contractor\CreateContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Contractor\UpdateContractorRequest;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use maciejlewandowskii\iFirmaApi\Exception\ApiException;
use maciejlewandowskii\iFirmaApi\Exception\ContractorNotFoundException;
use maciejlewandowskii\iFirmaApi\Service\ContractorService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class ContractorServiceTest extends TestCase
{
    private MockObject&iFirmaClientInterface $client;

    private ContractorService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(iFirmaClientInterface::class);
        $this->service = new ContractorService(
            $this->client,
            Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator(),
        );
    }

    /** @return array<string, mixed> */
    private function contractorApiData(): array
    {
        return [
            'Nazwa' => 'ACME Sp. z o.o.',
            'KodPocztowy' => '00-001',
            'Miejscowosc' => 'Warszawa',
            'Identyfikator' => 'CTR-1',
            'NIP' => '5252344078',
            'OsobaFizyczna' => false,
            'ZgodaNaEfaktury' => false,
            'JestDostawca' => false,
            'JestOdbiorca' => true,
            'AdresZagraniczny' => false,
            'PodmiotPowiazany' => false,
        ];
    }

    public function testCreateReturnsContractorCreatedResponse(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/kontrahenci.json', AuthKeyType::Invoice)
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'CTR-99']));

        $result = $this->service->create(new CreateContractorRequest(
            name: 'Test Sp. z o.o.',
            postalCode: '00-001',
            city: 'Warszawa',
        ));

        $this->assertSame(0, $result->code);
        $this->assertSame('CTR-99', $result->id);
    }

    public function testUpdateCallsCorrectPathAndReturnsResponse(): void
    {
        $this->client->expects($this->once())
            ->method('put')
            ->with($this->stringContains('CTR-1'), AuthKeyType::Invoice)
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'Updated', 'Wynik' => 'CTR-1']));

        $result = $this->service->update('CTR-1', new UpdateContractorRequest(
            name: 'Updated Name',
            postalCode: '00-002',
            city: 'Kraków',
        ));

        $this->assertSame('CTR-1', $result->id);
    }

    public function testGetReturnsHydratedContractorResponse(): void
    {
        $this->client->expects($this->once())
            ->method('get')
            ->willReturn(new ApiResponse(['Wynik' => [$this->contractorApiData()]]));

        $result = $this->service->get('CTR-1');

        $this->assertSame('ACME Sp. z o.o.', $result->name);
        $this->assertSame('Warszawa', $result->city);
        $this->assertSame('5252344078', $result->taxId);
        $this->assertTrue($result->isRecipient);
    }

    public function testGetThrowsContractorNotFoundWhenApiThrows(): void
    {
        $this->expectException(ContractorNotFoundException::class);

        $this->client->method('get')->willThrowException(new ApiException('Not found', 404));

        $this->service->get('NONEXISTENT');
    }

    public function testGetThrowsContractorNotFoundWhenWynikEmpty(): void
    {
        $this->expectException(ContractorNotFoundException::class);

        $this->client->method('get')->willReturn(new ApiResponse(['Wynik' => []]));

        $this->service->get('CTR-999');
    }

    public function testSearchYieldsContractorResponses(): void
    {
        $this->client->method('get')
            ->willReturn(new ApiResponse(['Wynik' => [$this->contractorApiData(), $this->contractorApiData()]]));

        $results = iterator_to_array($this->service->search('ACME'));

        $this->assertCount(2, $results);
        $this->assertSame('ACME Sp. z o.o.', $results[0]->name);
    }

    public function testSearchReturnsEmptyGeneratorWhenNoResults(): void
    {
        $this->client->method('get')->willReturn(new ApiResponse(['Wynik' => []]));

        $results = iterator_to_array($this->service->search('nothing'));

        $this->assertCount(0, $results);
    }
}
