<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Response\Contractor;

final readonly class ContractorCreatedResponse
{
    public function __construct(
        public int $code,
        public string $message,
        public string $id,
    ) {
    }
}
