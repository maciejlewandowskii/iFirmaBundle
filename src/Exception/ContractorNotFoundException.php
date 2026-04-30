<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Exception;

use function sprintf;

class ContractorNotFoundException extends iFirmaException
{
    public function __construct(string $identifier)
    {
        parent::__construct(sprintf('Contractor not found: %s', $identifier));
    }
}
