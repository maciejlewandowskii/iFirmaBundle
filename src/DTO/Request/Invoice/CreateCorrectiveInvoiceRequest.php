<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Invoice;

use maciejlewandowskii\iFirmaApi\Enum\CorrectiveReasonType;
use maciejlewandowskii\iFirmaApi\Enum\PaymentMethod;
use maciejlewandowskii\iFirmaApi\Enum\RecipientSignatureType;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateCorrectiveInvoiceRequest
{
    public function __construct(
        #[SerializedName('PowodKorekty')]
        #[Assert\NotNull]
        public CorrectiveReasonType $correctionReason,

        #[SerializedName('DataWystawienia')]
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $issueDate,

        /** @var CorrectiveInvoicePositionRequest[] */
        #[SerializedName('Pozycje')]
        #[Assert\NotNull]
        #[Assert\Count(min: 1)]
        #[Assert\Valid]
        public array $positions,

        #[SerializedName('PowodKorektyNaWydruku')]
        public ?bool $showCorrectionReason = null,

        #[SerializedName('SpelnionoWarunki')]
        public ?bool $conditionsMet = null,

        #[SerializedName('MiejsceWystawienia')]
        #[Assert\Length(max: 50)]
        public ?string $issuePlace = null,

        #[SerializedName('TerminPlatnosci')]
        #[Assert\Date]
        public ?string $paymentDeadline = null,

        #[SerializedName('Zaplacono')]
        #[Assert\PositiveOrZero]
        #[Assert\LessThan(100_000_000)]
        public ?float $amountPaid = null,

        #[SerializedName('SposobZaplaty')]
        public ?PaymentMethod $paymentMethod = null,

        #[SerializedName('NumerKontaBankowego')]
        #[Assert\Length(max: 28)]
        public ?string $bankAccountNumber = null,

        #[SerializedName('SplitPayment')]
        public ?bool $splitPayment = null,

        #[SerializedName('NazwaSeriiNumeracji')]
        public ?string $numberingSeriesName = null,

        #[SerializedName('NazwaSzablonu')]
        public ?string $templateName = null,

        #[SerializedName('RodzajPodpisuOdbiorcy')]
        public ?RecipientSignatureType $recipientSignatureType = null,

        #[SerializedName('PodpisOdbiorcy')]
        #[Assert\Length(max: 70)]
        public ?string $recipientSignature = null,

        #[SerializedName('PodpisWystawcy')]
        #[Assert\Length(max: 70)]
        public ?string $issuerSignature = null,

        #[SerializedName('Uwagi')]
        #[Assert\Length(max: 1000)]
        public ?string $notes = null,
    ) {
    }
}
