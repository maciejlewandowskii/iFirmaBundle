<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit;

use maciejlewandowskii\iFirmaApi\iFirmaApiFactory;
use PHPUnit\Framework\TestCase;

final class iFirmaApiFactoryTest extends TestCase
{
    private const string HEX_KEY = 'aabbccdd11223344aabbccdd11223344';

    public function testCreateWithRequiredParamsDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();

        iFirmaApiFactory::create(
            username: 'user@test.pl',
            invoiceKeyHex: self::HEX_KEY,
            subscriberKeyHex: self::HEX_KEY,
        );
    }

    public function testCreateWithAllOptionalParamsDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();

        iFirmaApiFactory::create(
            username: 'user@test.pl',
            invoiceKeyHex: self::HEX_KEY,
            subscriberKeyHex: self::HEX_KEY,
            expenseKeyHex: self::HEX_KEY,
            accountKeyHex: self::HEX_KEY,
        );
    }

    public function testCreateWithZeroRetriesDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();

        iFirmaApiFactory::create(
            username: 'user@test.pl',
            invoiceKeyHex: self::HEX_KEY,
            subscriberKeyHex: self::HEX_KEY,
            maxRetries: 0,
        );
    }
}
