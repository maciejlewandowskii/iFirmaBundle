<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Order;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class OrderTrackingRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 50)]
        public string $number,
    ) {
    }
}
