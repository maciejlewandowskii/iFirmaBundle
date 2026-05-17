<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Invoice;

use maciejlewandowskii\iFirmaApi\Enum\CalculationBasis;
use maciejlewandowskii\iFirmaApi\Enum\Currency;
use maciejlewandowskii\iFirmaApi\Enum\FormatDateSale;
use maciejlewandowskii\iFirmaApi\Enum\InvoiceLanguage;
use maciejlewandowskii\iFirmaApi\Enum\PaymentMethod;
use maciejlewandowskii\iFirmaApi\Enum\RecipientSignatureType;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateOssInvoiceRequest
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

        #[SerializedName('KrajDostawy')]
        #[Assert\NotBlank]
        #[Assert\Length(exactly: 2)]
        public string $deliveryCountry,

        #[SerializedName('RodzajPodpisuOdbiorcy')]
        #[Assert\NotNull]
        public RecipientSignatureType $recipientSignatureType,

        #[SerializedName('Kontrahent')]
        #[Assert\Valid]
        public ?InvoiceContractorRequest $contractor = null,

        /** @var OssInvoicePositionRequest[] */
        #[SerializedName('Pozycje')]
        #[Assert\NotNull]
        #[Assert\Count(min: 1)]
        #[Assert\Valid]
        public array $positions = [],

        #[SerializedName('LiczOd')]
        public ?CalculationBasis $calculationBasis = null,

        #[SerializedName('Jezyk')]
        public ?InvoiceLanguage $language = null,

        #[SerializedName('Waluta')]
        public ?Currency $currency = null,

        #[SerializedName('KursWalutyZDniaPoprzedzajacegoDzienWystawieniaFaktury')]
        #[Assert\PositiveOrZero]
        public ?float $exchangeRate = null,

        #[SerializedName('SposobZaplaty')]
        public ?PaymentMethod $paymentMethod = null,

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

        #[SerializedName('WidocznyNumerBdo')]
        public ?bool $showBdoNumber = null,

        #[SerializedName('SprzedazUslug')]
        public ?bool $servicesSale = null,

        #[SerializedName('UstalenieMiejscaUslugi1')]
        public ?string $serviceLocationRule1 = null,

        #[SerializedName('UstalenieMiejscaUslugi2')]
        public ?string $serviceLocationRule2 = null,

        #[SerializedName('KrajWysylki')]
        #[Assert\Length(exactly: 2)]
        public ?string $shipmentCountry = null,

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
