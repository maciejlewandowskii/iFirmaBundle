<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Exception;

use Throwable;

class ApiException extends iFirmaException
{
    public function __construct(
        string $message,
        private readonly int $apiCode,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $apiCode, $previous);
    }

    public function getApiCode(): int
    {
        return $this->apiCode;
    }
}
