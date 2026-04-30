<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Exception;

use function sprintf;

class InvoiceNotFoundException extends iFirmaException
{
    public function __construct(string|int $identifier)
    {
        parent::__construct(sprintf('Invoice not found: %s', $identifier));
    }
}
