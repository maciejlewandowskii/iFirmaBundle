<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Authentication;

use maciejlewandowskii\iFirmaApi\Authentication\Credentials;
use maciejlewandowskii\iFirmaApi\Authentication\CredentialsHmacAuthenticator;
use maciejlewandowskii\iFirmaApi\Exception\AuthenticationException;
use PHPUnit\Framework\TestCase;

final class CredentialsHmacAuthenticatorTest extends TestCase
{
    private const string VALID_HEX_KEY = 'aabbccdd11223344aabbccdd11223344';

    public function testBuildsHeaderWithCorrectFormat(): void
    {
        $credentials = new Credentials('user@test.pl', self::VALID_HEX_KEY, self::VALID_HEX_KEY);
        $auth = new CredentialsHmacAuthenticator($credentials);

        $header = $auth->buildAuthorizationHeader(
            'https://www.ifirma.pl/iapi/fakturakraj.json',
            'user@test.pl',
            'faktura',
            '{}',
        );

        $this->assertStringStartsWith('IAPIS user=user@test.pl, hmac-sha1=', $header);
    }

    public function testHeaderIsDeterministic(): void
    {
        $credentials = new Credentials('u', self::VALID_HEX_KEY, self::VALID_HEX_KEY);
        $auth = new CredentialsHmacAuthenticator($credentials);

        $args = ['https://www.ifirma.pl/iapi/x.json', 'u', 'faktura', '{"a":1}'];

        $this->assertSame(
            $auth->buildAuthorizationHeader(...$args),
            $auth->buildAuthorizationHeader(...$args),
        );
    }

    public function testDifferentBodyProducesDifferentHash(): void
    {
        $credentials = new Credentials('u', self::VALID_HEX_KEY, self::VALID_HEX_KEY);
        $auth = new CredentialsHmacAuthenticator($credentials);

        $url = 'https://www.ifirma.pl/iapi/x.json';

        $h1 = $auth->buildAuthorizationHeader($url, 'u', 'faktura', '{"body":1}');
        $h2 = $auth->buildAuthorizationHeader($url, 'u', 'faktura', '{"body":2}');

        $this->assertNotSame($h1, $h2);
    }

    public function testDifferentKeyTypesProduceDifferentHashes(): void
    {
        $subscriberKey = 'ccddaabb44332211ccddaabb44332211';
        $credentials = new Credentials('u', self::VALID_HEX_KEY, $subscriberKey);
        $auth = new CredentialsHmacAuthenticator($credentials);

        $url = 'https://www.ifirma.pl/iapi/x.json';

        $h1 = $auth->buildAuthorizationHeader($url, 'u', 'faktura', '');
        $h2 = $auth->buildAuthorizationHeader($url, 'u', 'abonent', '');

        $this->assertNotSame($h1, $h2);
    }

    public function testThrowsOnInvalidHexKey(): void
    {
        $this->expectException(AuthenticationException::class);

        $credentials = new Credentials('u', 'GGGG_not_hex', 'bbbb');
        $auth = new CredentialsHmacAuthenticator($credentials);
        $auth->buildAuthorizationHeader('https://x.com', 'u', 'faktura', '');
    }
}
