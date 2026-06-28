<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Event;

final class PreSyncEvent extends AbstractSyncEvent
{
    private bool $canceled = false;

    public function cancel(): void
    {
        $this->canceled = true;
    }

    public function isCanceled(): bool
    {
        return $this->canceled;
    }
}
