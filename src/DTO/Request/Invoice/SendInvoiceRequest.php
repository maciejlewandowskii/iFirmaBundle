<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Invoice;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SendInvoiceRequest
{
    public function __construct(
        #[SerializedName('Tekst')]
        #[Assert\Length(max: 1000)]
        public ?string $message = null,

        #[SerializedName('Przelew')]
        public ?bool $includeTransfer = null,

        #[SerializedName('Pobranie')]
        public ?bool $includeCashOnDelivery = null,

        #[SerializedName('MTransfer')]
        public ?string $mTransfer = null,

        #[SerializedName('SkrzynkaEmail')]
        public ?string $senderEmailBox = null,

        #[SerializedName('SzablonEmail')]
        public ?string $emailTemplate = null,

        #[SerializedName('SkrzynkaEmailOdbiorcy')]
        public ?string $recipientEmailBox = null,
    ) {
    }
}
