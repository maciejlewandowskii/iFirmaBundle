<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Service;

use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use maciejlewandowskii\iFirmaApi\Service\VatDictionaryService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class VatDictionaryServiceTest extends TestCase
{
    private MockObject&iFirmaClientInterface $client;

    private VatDictionaryService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(iFirmaClientInterface::class);
        $this->service = new VatDictionaryService(
            $this->client,
            Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator(),
        );
    }

    public function testGetEuVatRatesCallsCorrectEndpoint(): void
    {
        $this->client->expects($this->once())
            ->method('get')
            ->with('/slownik/stawki_vat/DE.json', AuthKeyType::Invoice, [])
            ->willReturn(new ApiResponse([
                'KodKraju' => 'de',
                'NazwaKraju' => 'Niemcy',
                'StawkiVat' => [
                    ['Rodzaj' => 'POD', 'Wartosc' => 19.0],
                    ['Rodzaj' => 'PR1', 'Wartosc' => 7.0],
                ],
            ]));

        $result = $this->service->getEuVatRates('DE');

        $this->assertSame('de', $result->countryCode);
        $this->assertSame('Niemcy', $result->countryName);
        $this->assertCount(2, $result->rates);
        $this->assertSame('POD', $result->rates[0]->type);
        $this->assertSame(19.0, $result->rates[0]->value);
        $this->assertSame('PR1', $result->rates[1]->type);
    }

    public function testGetEuVatRatesPassesDateParameter(): void
    {
        $this->client->expects($this->once())
            ->method('get')
            ->with('/slownik/stawki_vat/PL.json', AuthKeyType::Invoice, ['data' => '2023-01-01'])
            ->willReturn(new ApiResponse([
                'KodKraju' => 'pl',
                'NazwaKraju' => 'Polska',
                'StawkiVat' => [],
            ]));

        $result = $this->service->getEuVatRates('PL', '2023-01-01');

        $this->assertSame('pl', $result->countryCode);
        $this->assertCount(0, $result->rates);
    }

    public function testGetEuVatRatesHandlesEmptyRates(): void
    {
        $this->client->method('get')->willReturn(new ApiResponse([
            'KodKraju' => 'MT',
            'NazwaKraju' => 'Malta',
            'StawkiVat' => [],
        ]));

        $result = $this->service->getEuVatRates('MT');

        $this->assertSame('MT', $result->countryCode);
        $this->assertSame([], $result->rates);
    }
}
