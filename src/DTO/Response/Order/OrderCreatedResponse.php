<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Response\Order;

final readonly class OrderCreatedResponse
{
    public function __construct(
        public int $code,
        public string $message,
        public ?string $id = null,
    ) {
    }
}
