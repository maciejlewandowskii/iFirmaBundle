<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Concern;

use DateTimeInterface;

trait IFirmaEntityTrait
{
    private ?string $iFirmaId = null;

    private ?DateTimeInterface $iFirmaSyncedAt = null;

    private ?string $iFirmaStateHash = null;

    public function getIFirmaId(): ?string
    {
        return $this->iFirmaId;
    }

    public function setIFirmaId(string $iFirmaId): void
    {
        $this->iFirmaId = $iFirmaId;
    }

    public function getIFirmaSyncedAt(): ?DateTimeInterface
    {
        return $this->iFirmaSyncedAt;
    }

    public function setIFirmaSyncedAt(DateTimeInterface $syncedAt): void
    {
        $this->iFirmaSyncedAt = $syncedAt;
    }

    public function getIFirmaStateHash(): ?string
    {
        return $this->iFirmaStateHash;
    }

    public function setIFirmaStateHash(?string $hash): void
    {
        $this->iFirmaStateHash = $hash;
    }

    public function isSynced(): bool
    {
        return null !== $this->iFirmaId;
    }
}
