<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Order;

use maciejlewandowskii\iFirmaApi\Enum\Currency;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateOrderRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 40)]
        public string $id,

        #[Assert\NotBlank]
        public string $status,

        #[Assert\NotBlank]
        public string $created,

        #[Assert\NotNull]
        public Currency $currency,

        #[Assert\NotNull]
        #[Assert\PositiveOrZero]
        public float $shippingTotal,

        #[Assert\NotNull]
        #[Assert\Positive]
        public float $productsTotalNet,

        /** @var OrderItemRequest[] */
        #[Assert\NotNull]
        #[Assert\Count(min: 1)]
        #[Assert\Valid]
        public array $items,

        #[Assert\Length(max: 100)]
        public ?string $customId = null,

        public ?string $modified = null,

        #[Assert\Positive]
        public ?float $discountTotal = null,

        #[Assert\Positive]
        public ?float $discountTax = null,

        #[Assert\PositiveOrZero]
        public ?float $shippingTax = null,

        #[Assert\PositiveOrZero]
        public ?float $productsTotalTax = null,

        #[Assert\Length(max: 20)]
        public ?string $customerId = null,

        #[Assert\Length(max: 100)]
        public ?string $paymentMethod = null,

        #[Assert\Length(max: 500)]
        public ?string $transactionId = null,

        public ?bool $paid = null,

        public ?string $completed = null,

        #[Assert\Length(max: 40)]
        public ?string $clientLogin = null,

        #[Assert\Length(max: 2000)]
        public ?string $message = null,

        #[Assert\Length(max: 300)]
        public ?string $deliveryType = null,

        public ?bool $invoiceRequired = null,

        /** @var OrderTrackingRequest[]|null */
        #[Assert\Valid]
        public ?array $trackingList = null,

        #[Assert\Valid]
        public ?OrderAddressRequest $billing = null,

        #[Assert\Valid]
        public ?OrderAddressRequest $shipping = null,

        #[Assert\Valid]
        public ?OrderPickupPointRequest $pickupPoint = null,
    ) {
    }
}
