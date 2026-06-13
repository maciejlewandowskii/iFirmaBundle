<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Message;

use maciejlewandowskii\iFirmaApi\Message\SyncEntityMessage;
use PHPUnit\Framework\TestCase;

final class SyncEntityMessageTest extends TestCase
{
    public function testConstructorStoresProperties(): void
    {
        $message = new SyncEntityMessage('App\\Entity\\Invoice', 42);

        $this->assertSame('App\\Entity\\Invoice', $message->entityClass);
        $this->assertSame(42, $message->entityId);
    }

    public function testStringEntityId(): void
    {
        $message = new SyncEntityMessage('App\\Entity\\Invoice', 'uuid-123');

        $this->assertSame('uuid-123', $message->entityId);
    }
}
