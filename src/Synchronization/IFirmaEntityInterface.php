<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Synchronization;

use maciejlewandowskii\iFirmaApi\Contract\IFirmaEntityInterface as ContractInterface;

interface IFirmaEntityInterface extends ContractInterface
{
    public function isSynchronized(): bool;
}
