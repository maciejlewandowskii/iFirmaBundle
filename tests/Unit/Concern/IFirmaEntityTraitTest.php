<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Concern;

use DateTimeImmutable;
use maciejlewandowskii\iFirmaApi\Concern\IFirmaEntityTrait;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaEntityInterface;
use PHPUnit\Framework\TestCase;

final class IFirmaEntityTraitTest extends TestCase
{
    private IFirmaEntityInterface $entity;

    protected function setUp(): void
    {
        $this->entity = new class implements IFirmaEntityInterface {
            use IFirmaEntityTrait;
        };
    }

    public function testInitialStateIsNull(): void
    {
        $this->assertNull($this->entity->getIFirmaId());
        $this->assertNull($this->entity->getIFirmaSyncedAt());
        $this->assertNull($this->entity->getIFirmaStateHash());
        $this->assertFalse($this->entity->isSynced());
    }

    public function testSetAndGetIFirmaId(): void
    {
        $this->entity->setIFirmaId('abc-123');
        $this->assertSame('abc-123', $this->entity->getIFirmaId());
        $this->assertTrue($this->entity->isSynced());
    }

    public function testSetAndGetSyncedAt(): void
    {
        $now = new DateTimeImmutable();
        $this->entity->setIFirmaSyncedAt($now);
        $this->assertSame($now, $this->entity->getIFirmaSyncedAt());
    }

    public function testSetAndGetStateHash(): void
    {
        $this->entity->setIFirmaStateHash('hash42');
        $this->assertSame('hash42', $this->entity->getIFirmaStateHash());
    }

    public function testClearStateHash(): void
    {
        $this->entity->setIFirmaStateHash('hash');
        $this->entity->setIFirmaStateHash(null);
        $this->assertNull($this->entity->getIFirmaStateHash());
    }
}
