<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Integration;

use maciejlewandowskii\iFirmaApi\DTO\Response\VatDictionary\EuVatRateItemResponse;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
final class VatDictionaryIntegrationTest extends IntegrationTestCase
{
    public function testGetEuVatRatesForKnownCountry(): void
    {
        $result = $this->api()->vatDictionaryService->getEuVatRates('DE');

        $this->assertNotEmpty($result->countryCode);
        $this->assertNotEmpty($result->countryName);
        $this->assertNotEmpty($result->rates);

        foreach ($result->rates as $rate) {
            $this->assertNotEmpty($rate->type);
            $this->assertGreaterThanOrEqual(0.0, $rate->value);
        }
    }

    public function testGetEuVatRatesStandardRatePresent(): void
    {
        $result = $this->api()->vatDictionaryService->getEuVatRates('FR');

        $types = array_map(static fn (EuVatRateItemResponse $r): string => $r->type, $result->rates);

        $this->assertContains('POD', $types, 'Standard (POD) rate should be present for France');
    }

    public function testGetEuVatRatesWithHistoricalDate(): void
    {
        $result = $this->api()->vatDictionaryService->getEuVatRates('PL', '2023-01-01');

        $this->assertNotEmpty($result->countryCode);
        $this->assertNotEmpty($result->rates);
    }

    public function testGetEuVatRatesForAllMajorCountries(): void
    {
        foreach (['DE', 'FR', 'IT', 'ES', 'NL'] as $countryCode) {
            $result = $this->api()->vatDictionaryService->getEuVatRates($countryCode);

            $this->assertNotEmpty($result->rates, "Rates missing for $countryCode");
        }
    }
}
