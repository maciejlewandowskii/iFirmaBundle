<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Contract;

use DateTimeInterface;

interface IFirmaEntityInterface
{
    public function getIFirmaId(): ?string;

    public function setIFirmaId(string $iFirmaId): void;

    public function getIFirmaSyncedAt(): ?DateTimeInterface;

    public function setIFirmaSyncedAt(DateTimeInterface $syncedAt): void;

    public function getIFirmaStateHash(): ?string;

    public function setIFirmaStateHash(?string $hash): void;

    public function isSynced(): bool;
}
