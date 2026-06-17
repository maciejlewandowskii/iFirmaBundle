<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Integration;

use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
final class AccountingMonthIntegrationTest extends IntegrationTestCase
{
    public function testGetCurrentAccountingMonth(): void
    {
        $response = $this->api()->accountingMonthService->get();

        $this->assertGreaterThanOrEqual(1, $response->month);
        $this->assertLessThanOrEqual(12, $response->month);
        $this->assertGreaterThan(2000, $response->year);
    }

    public function testGetApiLimitReturnsPositiveGranted(): void
    {
        $limit = $this->api()->accountingMonthService->getApiLimit();

        $this->assertGreaterThan(0, $limit->granted);
        $this->assertGreaterThanOrEqual(0, $limit->used);
        $this->assertLessThanOrEqual($limit->granted, $limit->used);
    }
}
