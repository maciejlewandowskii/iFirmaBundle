<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Order;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class OrderAddressRequest
{
    public function __construct(
        #[Assert\Length(max: 500)]
        public ?string $firstName = null,

        #[Assert\Length(max: 500)]
        public ?string $lastName = null,

        #[Assert\Length(max: 500)]
        public ?string $company = null,

        #[Assert\Length(max: 65)]
        public ?string $address1 = null,

        #[Assert\Length(max: 65)]
        public ?string $address2 = null,

        #[Assert\Length(max: 65)]
        public ?string $city = null,

        #[Assert\Length(max: 500)]
        public ?string $state = null,

        #[Assert\Length(max: 16)]
        public ?string $postcode = null,

        #[Assert\Length(max: 70)]
        public ?string $country = null,

        #[Assert\Email]
        #[Assert\Length(max: 65)]
        public ?string $email = null,

        #[Assert\Length(max: 32)]
        public ?string $phone = null,

        #[Assert\Length(max: 500)]
        public ?string $regon = null,

        #[Assert\Length(max: 40)]
        public ?string $nip = null,
    ) {
    }
}
