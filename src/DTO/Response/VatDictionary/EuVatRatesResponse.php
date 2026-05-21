<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Response\VatDictionary;

final readonly class EuVatRatesResponse
{
    /** @param EuVatRateItemResponse[] $rates */
    public function __construct(
        public string $countryCode,
        public string $countryName,
        public array $rates,
    ) {
    }
}
