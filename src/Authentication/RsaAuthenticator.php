<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Authentication;

use function is_string;

use maciejlewandowskii\iFirmaApi\Exception\AuthenticationException;

use function sprintf;

/**
 * Authenticator using an RSA private key.
 * Signs SHA-1 hash of the message with the private key, then base64-encodes it.
 */
final readonly class RsaAuthenticator implements AuthenticatorInterface
{
    public function __construct(// @codeCoverageIgnore
        private string $privateKeyPem,
    ) {
    }

    public function buildAuthorizationHeader(string $url, string $username, string $keyName, string $requestBody): string
    {
        $privateKey = openssl_pkey_get_private($this->privateKeyPem);

        if (false === $privateKey) {
            throw new AuthenticationException('Failed to load RSA private key');
        }

        $message = $url . $username . $keyName . $requestBody;
        $sha1Hash = sha1($message, true);

        $signature = '';
        $result = openssl_private_encrypt($sha1Hash, $signature, $privateKey);

        if (!$result) {
            throw new AuthenticationException('RSA signing failed: ' . openssl_error_string()); // @codeCoverageIgnore
        }

        if (!is_string($signature)) {
            throw new AuthenticationException('RSA signing produced unexpected output'); // @codeCoverageIgnore
        }

        return sprintf('IAPIS user=%s, rsa=%s', $username, base64_encode($signature));
    }
}
