<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Response\VatDictionary;

final readonly class EuVatRateItemResponse
{
    public function __construct(
        public string $type,
        public float $value,
    ) {
    }
}
