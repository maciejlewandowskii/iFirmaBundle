<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Invoice;

use maciejlewandowskii\iFirmaApi\Enum\AdditionalNoteType;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class AdditionalNoteRequest
{
    public function __construct(
        #[SerializedName('Typ')]
        #[Assert\NotNull]
        public AdditionalNoteType $type,

        #[SerializedName('Info')]
        #[Assert\NotBlank]
        public string $info,

        #[SerializedName('Data')]
        #[Assert\Date]
        public ?string $date = null,
    ) {
    }
}
