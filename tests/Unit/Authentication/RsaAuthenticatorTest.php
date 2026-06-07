<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Authentication;

use maciejlewandowskii\iFirmaApi\Authentication\RsaAuthenticator;
use maciejlewandowskii\iFirmaApi\Exception\AuthenticationException;

use const OPENSSL_KEYTYPE_RSA;

use PHPUnit\Framework\TestCase;

final class RsaAuthenticatorTest extends TestCase
{
    private string $privatePem = '';

    protected function setUp(): void
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        $this->assertNotFalse($key, 'openssl_pkey_new() failed — check OpenSSL availability');

        $pem = '';
        openssl_pkey_export($key, $pem);
        $this->assertIsString($pem);
        $this->privatePem = $pem;
    }

    public function testBuildsRsaAuthorizationHeader(): void
    {
        $auth = new RsaAuthenticator($this->privatePem);

        $header = $auth->buildAuthorizationHeader(
            'https://www.ifirma.pl/iapi/fakturakraj.json',
            'user@test.pl',
            'faktura',
            '{}',
        );

        $this->assertStringStartsWith('IAPIS user=user@test.pl, rsa=', $header);
    }

    public function testHeaderContainsBase64EncodedSignature(): void
    {
        $auth = new RsaAuthenticator($this->privatePem);

        $header = $auth->buildAuthorizationHeader(
            'https://www.ifirma.pl/iapi/x.json',
            'u',
            'faktura',
            '',
        );

        // Everything after "rsa=" is base64
        $base64Part = mb_substr($header, (int) mb_strpos($header, 'rsa=') + 4);
        $this->assertNotEmpty(base64_decode($base64Part, true));
    }

    public function testDifferentBodiesProduceDifferentSignatures(): void
    {
        $auth = new RsaAuthenticator($this->privatePem);

        $h1 = $auth->buildAuthorizationHeader('https://x.com', 'u', 'faktura', 'body1');
        $h2 = $auth->buildAuthorizationHeader('https://x.com', 'u', 'faktura', 'body2');

        $this->assertNotSame($h1, $h2);
    }

    public function testThrowsAuthenticationExceptionForInvalidPem(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessageMatches('/Failed to load RSA private key/');

        $auth = new RsaAuthenticator('not-a-valid-pem');
        $auth->buildAuthorizationHeader('https://x.com', 'u', 'faktura', '');
    }
}
