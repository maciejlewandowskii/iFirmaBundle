<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Synchronization;

use DateTimeImmutable;
use maciejlewandowskii\iFirmaApi\Synchronization\HasIFirmaIntegration;
use maciejlewandowskii\iFirmaApi\Synchronization\IFirmaEntityInterface;
use PHPUnit\Framework\TestCase;

final class HasIFirmaIntegrationTest extends TestCase
{
    private IFirmaEntityInterface $entity;

    protected function setUp(): void
    {
        $this->entity = new class implements IFirmaEntityInterface {
            use HasIFirmaIntegration;
        };
    }

    public function testGetSetIFirmaId(): void
    {
        $this->assertNull($this->entity->getIFirmaId());
        $this->entity->setIFirmaId('test-id');
        $this->assertSame('test-id', $this->entity->getIFirmaId());
    }

    public function testGetSetIFirmaSyncedAt(): void
    {
        $this->assertNull($this->entity->getIFirmaSyncedAt());
        $now = new DateTimeImmutable();
        $this->entity->setIFirmaSyncedAt($now);
        $this->assertSame($now, $this->entity->getIFirmaSyncedAt());
    }

    public function testGetSetIFirmaStateHash(): void
    {
        $this->assertNull($this->entity->getIFirmaStateHash());
        $this->entity->setIFirmaStateHash('abc123');
        $this->assertSame('abc123', $this->entity->getIFirmaStateHash());
    }

    public function testIsSynced(): void
    {
        $this->assertFalse($this->entity->isSynced());
        $this->entity->setIFirmaId('test-id');
        $this->assertTrue($this->entity->isSynced());
    }

    public function testIsSynchronizedIsDeprecatedAliasForIsSynced(): void
    {
        $this->assertFalse($this->entity->isSynchronized());
        $this->entity->setIFirmaId('test-id');
        $this->assertTrue($this->entity->isSynchronized());
    }
}
