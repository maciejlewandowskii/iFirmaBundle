<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Invoice;

use maciejlewandowskii\iFirmaApi\Enum\CalculationBasis;
use maciejlewandowskii\iFirmaApi\Enum\FormatDateSale;
use maciejlewandowskii\iFirmaApi\Enum\PaymentMethod;
use maciejlewandowskii\iFirmaApi\Enum\RecipientSignatureType;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateShippingInvoiceRequest
{
    public function __construct(
        #[SerializedName('LiczOd')]
        #[Assert\NotNull]
        public CalculationBasis $calculationBasis,

        #[SerializedName('DataWystawienia')]
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $issueDate,

        #[SerializedName('DataOtrzymaniaZaplaty')]
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $paymentReceivedDate,

        #[SerializedName('DataSprzedazy')]
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $saleDate,

        #[SerializedName('FormatDatySprzedazy')]
        #[Assert\NotNull]
        public FormatDateSale $saleDateFormat,

        #[SerializedName('RodzajPodpisuOdbiorcy')]
        #[Assert\NotNull]
        public RecipientSignatureType $recipientSignatureType,

        #[SerializedName('Kontrahent')]
        #[Assert\Valid]
        public ?InvoiceContractorRequest $contractor = null,

        /** @var InvoicePositionRequest[] */
        #[SerializedName('Pozycje')]
        #[Assert\NotNull]
        #[Assert\Count(min: 1)]
        #[Assert\Valid]
        public array $positions = [],

        #[SerializedName('Zaplacono')]
        #[Assert\PositiveOrZero]
        #[Assert\LessThan(100_000_000)]
        public float $amountPaid = 0.0,

        #[SerializedName('SplitPayment')]
        public ?bool $splitPayment = null,

        #[SerializedName('NumerKontaBankowego')]
        #[Assert\Length(max: 28)]
        public ?string $bankAccountNumber = null,

        #[SerializedName('SposobZaplaty')]
        public ?PaymentMethod $paymentMethod = null,

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

        #[SerializedName('PodpisOdbiorcy')]
        #[Assert\Length(max: 70)]
        public ?string $recipientSignature = null,

        #[SerializedName('PodpisWystawcy')]
        #[Assert\Length(max: 70)]
        public ?string $issuerSignature = null,

        #[SerializedName('Uwagi')]
        #[Assert\Length(max: 1000)]
        public ?string $notes = null,

        #[SerializedName('WidocznyNumerGios')]
        public bool $showGiosNumber = false,

        #[SerializedName('WidocznyNumerBdo')]
        public bool $showBdoNumber = false,

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

        /** @var AdditionalNoteRequest[] */
        #[SerializedName('DodatkoweUwagi')]
        #[Assert\Valid]
        public ?array $additionalNotes = null,
    ) {
    }
}
