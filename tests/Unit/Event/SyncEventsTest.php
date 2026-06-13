<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Event;

use maciejlewandowskii\iFirmaApi\Contract\IFirmaEntityInterface;
use maciejlewandowskii\iFirmaApi\Event\PostSyncEvent;
use maciejlewandowskii\iFirmaApi\Event\PreSyncEvent;
use PHPUnit\Framework\TestCase;
use stdClass;

final class SyncEventsTest extends TestCase
{
    public function testPreSyncEvent(): void
    {
        $entity = $this->createMock(IFirmaEntityInterface::class);
        $dto = new stdClass();
        $event = new PreSyncEvent($entity, $dto);

        $this->assertSame($entity, $event->getEntity());
        $this->assertSame($dto, $event->getRequestDto());
        $this->assertFalse($event->isCanceled());

        $event->cancel();
        $this->assertTrue($event->isCanceled());
    }

    public function testPostSyncEvent(): void
    {
        $entity = $this->createMock(IFirmaEntityInterface::class);
        $dto = new stdClass();
        $responseDto = new stdClass();
        $event = new PostSyncEvent($entity, $dto, $responseDto);

        $this->assertSame($entity, $event->getEntity());
        $this->assertSame($dto, $event->getRequestDto());
        $this->assertSame($responseDto, $event->getResponseDto());
    }
}
