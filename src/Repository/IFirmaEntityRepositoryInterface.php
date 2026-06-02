<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Repository;

use maciejlewandowskii\iFirmaApi\Contract\IFirmaEntityInterface;

interface IFirmaEntityRepositoryInterface
{
    /**
     * @return class-string<IFirmaEntityInterface>
     */
    public function getSupportedEntityClass(): string;

    /**
     * @return list<IFirmaEntityInterface>
     */
    public function findUnsynced(): array;
}
