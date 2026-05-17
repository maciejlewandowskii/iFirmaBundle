<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Invoice;

use maciejlewandowskii\iFirmaApi\Enum\FormatDateSale;
use maciejlewandowskii\iFirmaApi\Enum\KpirEntryType;
use maciejlewandowskii\iFirmaApi\Enum\PaymentMethod;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateReceiptDocumentRequest
{
    public function __construct(
        #[SerializedName('DataWystawienia')]
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $issueDate,

        #[SerializedName('DataSprzedazy')]
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $saleDate,

        #[SerializedName('FormatDatySprzedazy')]
        #[Assert\NotNull]
        public FormatDateSale $saleDateFormat,

        #[SerializedName('SposobZaplaty')]
        #[Assert\NotNull]
        public PaymentMethod $paymentMethod,

        #[SerializedName('Kontrahent')]
        #[Assert\Valid]
        public ?InvoiceContractorRequest $contractor = null,

        /** @var ReceiptDocumentPositionRequest[] */
        #[SerializedName('Pozycje')]
        #[Assert\NotNull]
        #[Assert\Count(min: 1)]
        #[Assert\Valid]
        public array $positions = [],

        #[SerializedName('Zaplacono')]
        #[Assert\PositiveOrZero]
        #[Assert\LessThan(100_000_000)]
        public float $amountPaid = 0.0,

        #[SerializedName('NumerKontaBankowego')]
        #[Assert\Length(max: 28)]
        public ?string $bankAccountNumber = null,

        #[SerializedName('MiejsceWystawienia')]
        #[Assert\Length(max: 50)]
        public ?string $issuePlace = null,

        #[SerializedName('TerminPlatnosci')]
        #[Assert\Date]
        public ?string $paymentDeadline = null,

        #[SerializedName('NazwaSeriiNumeracji')]
        public ?string $numberingSeriesName = null,

        #[SerializedName('NazwaSzablonu')]
        public ?string $templateName = null,

        #[SerializedName('WpisDoKpir')]
        public ?KpirEntryType $kpirEntry = null,

        #[SerializedName('PodpisOdbiorcy')]
        #[Assert\Length(max: 70)]
        public ?string $recipientSignature = null,

        #[SerializedName('PodpisWystawcy')]
        #[Assert\Length(max: 70)]
        public ?string $issuerSignature = null,

        #[SerializedName('Uwagi')]
        #[Assert\Length(max: 1000)]
        public ?string $notes = null,

        #[SerializedName('Numer')]
        public ?int $number = null,

        #[SerializedName('IdentyfikatorKontrahenta')]
        #[Assert\Length(max: 15)]
        public ?string $contractorIdentifier = null,

        #[SerializedName('PrefiksUEKontrahenta')]
        #[Assert\Length(max: 2)]
        public ?string $contractorEuPrefix = null,

        #[SerializedName('NIPKontrahenta')]
        #[Assert\Length(max: 13)]
        public ?string $contractorTaxId = null,
    ) {
    }
}
