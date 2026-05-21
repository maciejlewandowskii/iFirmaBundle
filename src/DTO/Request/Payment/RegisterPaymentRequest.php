<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Payment;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegisterPaymentRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $invoiceType,

        #[Assert\NotBlank]
        public string $invoiceNumber,

        #[Assert\NotNull]
        #[Assert\PositiveOrZero]
        public float $amount,

        #[Assert\Date]
        public ?string $date = null,

        #[Assert\PositiveOrZero]
        public ?float $amountPln = null,

        #[Assert\PositiveOrZero]
        public ?float $exchangeRate = null,
    ) {
    }
}
