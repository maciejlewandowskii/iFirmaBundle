<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Authentication;

use maciejlewandowskii\iFirmaApi\Authentication\HmacKeyAuthenticator;
use maciejlewandowskii\iFirmaApi\Exception\AuthenticationException;
use PHPUnit\Framework\TestCase;

final class HmacKeyAuthenticatorTest extends TestCase
{
    public function testBuildsCorrectHmacHeader(): void
    {
        // Key: 8 bytes = EAB0D8ACF3308F3B (from iFirma docs example format)
        $hexKey = 'EAB0D8ACF3308F3B';
        $authenticator = new HmacKeyAuthenticator($hexKey);

        $header = $authenticator->buildAuthorizationHeader(
            'https://www.ifirma.pl/iapi/fakturakraj.json',
            'testuser',
            'faktura',
            '{}',
        );

        $this->assertStringStartsWith('IAPIS user=testuser, hmac-sha1=', $header);

        // Verify the hash is deterministic
        $header2 = $authenticator->buildAuthorizationHeader(
            'https://www.ifirma.pl/iapi/fakturakraj.json',
            'testuser',
            'faktura',
            '{}',
        );
        $this->assertSame($header, $header2);
    }

    public function testDifferentBodyProducesDifferentHash(): void
    {
        $authenticator = new HmacKeyAuthenticator('EAB0D8ACF3308F3B');

        $header1 = $authenticator->buildAuthorizationHeader(
            'https://www.ifirma.pl/iapi/fakturakraj.json',
            'testuser',
            'faktura',
            '{"body":1}',
        );

        $header2 = $authenticator->buildAuthorizationHeader(
            'https://www.ifirma.pl/iapi/fakturakraj.json',
            'testuser',
            'faktura',
            '{"body":2}',
        );

        $this->assertNotSame($header1, $header2);
    }

    public function testThrowsOnInvalidHexKey(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessageMatches('/Invalid hex key/');

        $authenticator = new HmacKeyAuthenticator('ZZZZZZZZ');
        $authenticator->buildAuthorizationHeader('https://example.com', 'user', 'faktura', '');
    }
}
