<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Event;

use maciejlewandowskii\iFirmaApi\Contract\IFirmaEntityInterface;

abstract class AbstractSyncEvent
{
    public function __construct(
        private readonly IFirmaEntityInterface $entity,
        private readonly object $requestDto,
    ) {
    }

    public function getEntity(): IFirmaEntityInterface
    {
        return $this->entity;
    }

    public function getRequestDto(): object
    {
        return $this->requestDto;
    }
}
