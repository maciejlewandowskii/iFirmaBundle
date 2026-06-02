<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Synchronization;

use maciejlewandowskii\iFirmaApi\Concern\IFirmaEntityTrait;

trait HasIFirmaIntegration
{
    use IFirmaEntityTrait;

    public function isSynchronized(): bool
    {
        return $this->isSynced();
    }
}
