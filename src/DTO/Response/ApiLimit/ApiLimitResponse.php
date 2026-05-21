<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Response\ApiLimit;

final readonly class ApiLimitResponse
{
    public function __construct(
        public int $used,
        public int $granted,
    ) {
    }
}
