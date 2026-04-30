<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Exception;

class RateLimitException extends iFirmaException
{
    public function __construct()
    {
        parent::__construct('iFirma API rate limit exceeded');
    }
}
