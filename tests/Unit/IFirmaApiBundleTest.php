<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit;

use maciejlewandowskii\iFirmaApi\IFirmaApiBundle;
use PHPUnit\Framework\TestCase;

final class IFirmaApiBundleTest extends TestCase
{
    public function testGetPathReturnsProjectRoot(): void
    {
        $bundle = new IFirmaApiBundle();

        $this->assertSame(dirname(__DIR__, 2), $bundle->getPath());
    }
}
