<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Event;

use maciejlewandowskii\iFirmaApi\Contract\IFirmaEntityInterface;

final class PreSyncEvent extends AbstractSyncEvent
{
    private bool $canceled = false;

    public function __construct(IFirmaEntityInterface $entity, object $requestDto)
    {
        parent::__construct($entity, $requestDto);
    }

    public function cancel(): void
    {
        $this->canceled = true;
    }

    public function isCanceled(): bool
    {
        return $this->canceled;
    }
}
