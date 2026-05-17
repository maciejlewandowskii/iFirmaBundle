<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Invoice;

use maciejlewandowskii\iFirmaApi\Enum\FlatRateTax;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ReceiptDocumentPositionRequest
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

        #[SerializedName('Ilosc')]
        #[Assert\Positive]
        public float $quantity,

        #[SerializedName('CenaJednostkowa')]
        #[Assert\Positive]
        #[Assert\LessThan(100_000_000)]
        public float $unitPrice,

        #[SerializedName('Rabat')]
        #[Assert\PositiveOrZero]
        #[Assert\LessThan(100)]
        public ?float $discount = null,

        #[SerializedName('StawkaRyczaltu')]
        public ?FlatRateTax $flatRateTax = null,
    ) {
    }
}
