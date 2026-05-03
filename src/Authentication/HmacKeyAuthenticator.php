<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Authentication;

use maciejlewandowskii\iFirmaApi\Exception\AuthenticationException;

use function sprintf;

/**
 * Authenticator that holds the hex key internally and signs requests with HMAC-SHA1.
 * The iFirma API requires the key in hex string form; it is converted to binary before signing.
 */
final readonly class HmacKeyAuthenticator implements AuthenticatorInterface
{
    public function __construct(
        private string $hexKey,
    ) {
    }

    public function buildAuthorizationHeader(string $url, string $username, string $keyName, string $requestBody): string
    {
        $keyBinary = $this->hexToBinary($this->hexKey);
        $message = $url . $username . $keyName . $requestBody;
        $hash = hash_hmac('sha1', $message, $keyBinary);

        return sprintf('IAPIS user=%s, hmac-sha1=%s', $username, $hash);
    }

    private function hexToBinary(string $hex): string
    {
        if (!ctype_xdigit($hex) || 0 !== mb_strlen($hex) % 2) {
            throw new AuthenticationException('Invalid hex key: expected an even-length hexadecimal string');
        }

        return (string) hex2bin($hex);
    }
}
