<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Invoice;

use maciejlewandowskii\iFirmaApi\Enum\OssVatRateType;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class OssInvoicePositionRequest
{
    public function __construct(
        #[SerializedName('NazwaPelna')]
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 300)]
        public string $name,

        #[SerializedName('Jednostka')]
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 10)]
        public string $unit,

        #[SerializedName('CenaJednostkowa')]
        #[Assert\Positive]
        #[Assert\LessThan(100_000_000)]
        public float $unitPrice,

        #[SerializedName('Ilosc')]
        #[Assert\Positive]
        public float $quantity,

        #[SerializedName('StawkaVat')]
        #[Assert\PositiveOrZero]
        public float $vatRate,

        #[SerializedName('TypStawkiVat')]
        #[Assert\NotNull]
        public OssVatRateType $vatRateType,

        #[SerializedName('NazwaPelnaObca')]
        #[Assert\Length(min: 1, max: 300)]
        public ?string $foreignName = null,

        #[SerializedName('JednostkaObca')]
        #[Assert\Length(min: 1, max: 10)]
        public ?string $foreignUnit = null,
    ) {
    }
}
