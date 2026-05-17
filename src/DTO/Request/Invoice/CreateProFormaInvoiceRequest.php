<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Invoice;

use maciejlewandowskii\iFirmaApi\Enum\CalculationBasis;
use maciejlewandowskii\iFirmaApi\Enum\PaymentMethod;
use maciejlewandowskii\iFirmaApi\Enum\ProFormaInvoiceType;
use maciejlewandowskii\iFirmaApi\Enum\RecipientSignatureType;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateProFormaInvoiceRequest
{
    public function __construct(
        #[SerializedName('LiczOd')]
        #[Assert\NotNull]
        public CalculationBasis $calculationBasis,

        #[SerializedName('TypFakturyKrajowej')]
        #[Assert\NotNull]
        public ProFormaInvoiceType $invoiceType,

        #[SerializedName('DataWystawienia')]
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $issueDate,

        #[SerializedName('NumerZamowienia')]
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 30)]
        public string $orderNumber,

        #[SerializedName('SposobZaplaty')]
        #[Assert\NotNull]
        public PaymentMethod $paymentMethod,

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

        #[SerializedName('SplitPayment')]
        public ?bool $splitPayment = null,

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
    ) {
    }
}
