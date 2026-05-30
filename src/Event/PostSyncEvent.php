<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Event;

use maciejlewandowskii\iFirmaApi\Contract\IFirmaEntityInterface;

final class PostSyncEvent extends AbstractSyncEvent
{
    public function __construct(
        IFirmaEntityInterface $entity,
        object $requestDto,
        private readonly object $responseDto,
    ) {
        parent::__construct($entity, $requestDto);
    }

    public function getResponseDto(): object
    {
        return $this->responseDto;
    }
}
