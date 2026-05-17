<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Invoice;

use maciejlewandowskii\iFirmaApi\Enum\FlatRateTax;
use maciejlewandowskii\iFirmaApi\Enum\GtuCode;
use maciejlewandowskii\iFirmaApi\Enum\VatRate;
use maciejlewandowskii\iFirmaApi\Enum\VatRateType;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CorrectiveInvoicePositionRequest
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

        #[SerializedName('TypStawkiVat')]
        #[Assert\NotNull]
        public VatRateType $vatRateType,

        #[SerializedName('Ilosc')]
        #[Assert\PositiveOrZero]
        public float $quantity,

        #[SerializedName('CenaJednostkowa')]
        #[Assert\Positive]
        #[Assert\LessThan(100_000_000)]
        public float $unitPrice,

        #[SerializedName('StawkaVat')]
        public ?VatRate $vatRate = null,

        #[SerializedName('PKWiU')]
        #[Assert\Length(max: 30)]
        public ?string $pkwiu = null,

        #[SerializedName('PodstawaPrawna')]
        #[Assert\Length(max: 30)]
        public ?string $legalBasis = null,

        #[SerializedName('GTU')]
        public ?GtuCode $gtu = null,

        #[SerializedName('StawkaRyczaltu')]
        public ?FlatRateTax $flatRateTax = null,
    ) {
    }
}
