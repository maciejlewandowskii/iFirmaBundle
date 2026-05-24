<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Order;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class OrderItemRequest
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Positive]
        public float $quantity,

        #[Assert\NotNull]
        #[Assert\Positive]
        public float $totalPrice,

        #[Assert\NotNull]
        #[Assert\Positive]
        public float $price,

        #[Assert\Length(max: 200)]
        public ?string $id = null,

        #[Assert\Length(max: 300)]
        public ?string $name = null,

        #[Assert\PositiveOrZero]
        public ?float $totalTax = null,

        #[Assert\PositiveOrZero]
        public ?float $priceAfterDiscount = null,

        #[Assert\Length(max: 10)]
        public ?string $unit = null,

        public ?string $sku = null,

        public ?string $type = null,

        #[Assert\Length(max: 100)]
        public ?string $offerId = null,

        public ?bool $digital = null,
    ) {
    }
}
