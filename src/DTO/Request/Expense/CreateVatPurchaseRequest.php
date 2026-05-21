<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Expense;

use maciejlewandowskii\iFirmaApi\Enum\KSeFDesignation;
use maciejlewandowskii\iFirmaApi\Enum\SaleType;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateVatPurchaseRequest
{
    public function __construct(
        #[SerializedName('NumerFaktury')]
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 50)]
        public string $invoiceNumber,

        #[SerializedName('OznaczenieKSeF')]
        #[Assert\NotNull]
        public KSeFDesignation $kSeFDesignation,

        #[SerializedName('DataWystawienia')]
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $issueDate,

        #[SerializedName('RodzajSprzedazy')]
        #[Assert\NotNull]
        public SaleType $saleType,

        #[SerializedName('NazwaWydatku')]
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 50)]
        public string $expenseName,

        #[SerializedName('KwotaNetto23')]
        #[Assert\NotNull]
        #[Assert\PositiveOrZero]
        #[Assert\LessThan(100_000_000)]
        public float $netAmount23,

        #[SerializedName('KwotaNetto08')]
        #[Assert\NotNull]
        #[Assert\PositiveOrZero]
        #[Assert\LessThan(100_000_000)]
        public float $netAmount08,

        #[SerializedName('KwotaNetto05')]
        #[Assert\NotNull]
        #[Assert\PositiveOrZero]
        #[Assert\LessThan(100_000_000)]
        public float $netAmount05,

        #[SerializedName('KwotaNetto00')]
        #[Assert\NotNull]
        #[Assert\PositiveOrZero]
        #[Assert\LessThan(100_000_000)]
        public float $netAmount00,

        #[SerializedName('KwotaNettoZw')]
        #[Assert\NotNull]
        #[Assert\PositiveOrZero]
        #[Assert\LessThan(100_000_000)]
        public float $netAmountExempt,

        #[SerializedName('KwotaVat23')]
        #[Assert\NotNull]
        #[Assert\PositiveOrZero]
        #[Assert\LessThan(100_000_000)]
        public float $vatAmount23,

        #[SerializedName('KwotaVat08')]
        #[Assert\NotNull]
        #[Assert\PositiveOrZero]
        #[Assert\LessThan(100_000_000)]
        public float $vatAmount08,

        #[SerializedName('KwotaVat05')]
        #[Assert\NotNull]
        #[Assert\PositiveOrZero]
        #[Assert\LessThan(100_000_000)]
        public float $vatAmount05,

        #[SerializedName('Kontrahent')]
        #[Assert\Valid]
        public ?ExpenseContractorRequest $contractor = null,

        #[SerializedName('IdentyfikatorKontrahenta')]
        #[Assert\Length(max: 15)]
        public ?string $contractorIdentifier = null,

        #[SerializedName('PrefiksUEKontrahenta')]
        #[Assert\Length(max: 2)]
        public ?string $contractorEuPrefix = null,

        #[SerializedName('NIPKontrahenta')]
        #[Assert\Length(max: 13)]
        public ?string $contractorTaxId = null,

        #[SerializedName('NumerKSeF')]
        public ?string $kSeFNumber = null,

        #[SerializedName('Offline24KSeF')]
        public ?bool $offline24KSeF = null,

        #[SerializedName('DataWplywu')]
        #[Assert\Date]
        public ?string $receiptDate = null,

        #[SerializedName('TerminPlatnosci')]
        #[Assert\Date]
        public ?string $paymentDeadline = null,
    ) {
    }
}
