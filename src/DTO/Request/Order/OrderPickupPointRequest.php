<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Order;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class OrderPickupPointRequest
{
    public function __construct(
        #[Assert\Length(max: 200)]
        public ?string $method = null,

        #[Assert\Length(max: 15)]
        public ?string $externalId = null,

        #[Assert\Length(max: 200)]
        public ?string $name = null,

        #[Assert\Length(max: 200)]
        public ?string $description = null,

        #[Assert\Length(max: 200)]
        public ?string $street = null,

        #[Assert\Length(max: 200)]
        public ?string $zipCode = null,

        #[Assert\Length(max: 200)]
        public ?string $city = null,

        #[Assert\Length(max: 50)]
        public ?string $countryCode = null,
    ) {
    }
}
