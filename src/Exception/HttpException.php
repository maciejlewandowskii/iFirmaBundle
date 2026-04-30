<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Exception;

use function sprintf;

use Throwable;

class HttpException extends iFirmaException
{
    public function __construct(
        private readonly int $statusCode,
        string $message = '',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message ?: sprintf('HTTP error %d', $statusCode), $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
