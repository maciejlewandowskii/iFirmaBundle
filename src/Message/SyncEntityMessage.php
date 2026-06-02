<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Message;

final readonly class SyncEntityMessage
{
    public function __construct(
        public string $entityClass,
        public string|int $entityId,
    ) {
    }
}
