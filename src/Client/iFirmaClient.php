<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Client;

use function is_array;
use function is_int;
use function is_string;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

use JsonException;
use maciejlewandowskii\iFirmaApi\Authentication\AuthenticatorInterface;
use maciejlewandowskii\iFirmaApi\Authentication\Credentials;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use maciejlewandowskii\iFirmaApi\Exception\ApiException;
use maciejlewandowskii\iFirmaApi\Exception\AuthenticationException;
use maciejlewandowskii\iFirmaApi\Exception\HttpException;
use maciejlewandowskii\iFirmaApi\Exception\iFirmaException;
use maciejlewandowskii\iFirmaApi\Exception\RateLimitException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function sprintf;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

final readonly class iFirmaClient implements iFirmaClientInterface
{
    private const string BASE_URL = 'https://www.ifirma.pl/iapi';

    public function __construct(
        private HttpClientInterface $httpClient,
        private AuthenticatorInterface $authenticator,
        private Credentials $credentials,
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    /**
     * @param array<string, mixed> $queryParams
     *
     * @throws AuthenticationException
     * @throws RateLimitException
     * @throws HttpException
     * @throws ApiException
     */
    public function get(string $path, AuthKeyType $keyType, array $queryParams = []): ApiResponse
    {
        return $this->request('GET', $path, $keyType, [], $queryParams);
    }

    /**
     * @param array<string, mixed> $body
     *
     * @throws AuthenticationException
     * @throws RateLimitException
     * @throws HttpException
     * @throws ApiException
     */
    public function post(string $path, AuthKeyType $keyType, array $body, array $queryParams = []): ApiResponse
    {
        return $this->request('POST', $path, $keyType, $body, $queryParams);
    }

    /**
     * @param array<string, mixed> $body
     *
     * @throws AuthenticationException
     * @throws RateLimitException
     * @throws HttpException
     * @throws ApiException
     */
    public function put(string $path, AuthKeyType $keyType, array $body): ApiResponse
    {
        return $this->request('PUT', $path, $keyType, $body);
    }

    /**
     * @param array<string, mixed> $queryParams
     *
     * @throws AuthenticationException
     * @throws RateLimitException
     * @throws HttpException
     */
    public function getRaw(string $path, AuthKeyType $keyType, array $queryParams = []): string
    {
        $url = self::BASE_URL . $path;
        $keyName = $keyType->value;

        $authHeader = $this->authenticator->buildAuthorizationHeader(
            $url,
            $this->credentials->getUsername(),
            $keyName,
            '',
        );

        $options = [
            'headers' => [
                'Authentication' => $authHeader,
                'Accept' => '*/*',
            ],
        ];

        if ([] !== $queryParams) {
            $options['query'] = $queryParams;
        }

        $this->logger->debug('iFirma API request', [
            'method' => 'GET',
            'url' => $url,
            'keyType' => $keyName,
        ]);

        try {
            $response = $this->httpClient->request('GET', $url, $options);
            $statusCode = $this->assertSuccessStatus($response, $url);
            $content = $response->getContent(false);

            $this->logger->debug('iFirma API response', ['url' => $url, 'status' => $statusCode]);

            return $content;
        } catch (iFirmaException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new HttpException(0, sprintf('iFirma API request failed: %s', $e->getMessage()), $e);
        }
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, mixed> $queryParams
     *
     * @throws AuthenticationException
     * @throws RateLimitException
     * @throws HttpException
     * @throws ApiException
     */
    private function request(
        string $method,
        string $path,
        AuthKeyType $keyType,
        array $body = [],
        array $queryParams = [],
    ): ApiResponse {
        $url = self::BASE_URL . $path;
        $keyName = $keyType->value;

        try {
            $requestBody = [] === $body ? '' : json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            throw new HttpException(0, 'Failed to encode request body: ' . $e->getMessage(), $e);
        }

        $authHeader = $this->authenticator->buildAuthorizationHeader(
            $url,
            $this->credentials->getUsername(),
            $keyName,
            $requestBody,
        );

        $options = [
            'headers' => [
                'Authentication' => $authHeader,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json; charset=UTF-8',
            ],
        ];

        if ([] !== $queryParams) {
            $options['query'] = $queryParams;
        }

        if ('' !== $requestBody) {
            $options['body'] = $requestBody;
        }

        $this->logger->debug('iFirma API request', [
            'method' => $method,
            'url' => $url,
            'keyType' => $keyName,
        ]);

        try {
            $response = $this->httpClient->request($method, $url, $options);
            $statusCode = $this->assertSuccessStatus($response, $url);
            $content = $response->getContent(false);

            $apiResponse = $this->decodeJsonResponse($content, $url);

            $this->logger->debug('iFirma API response', ['url' => $url, 'status' => $statusCode]);

            return $apiResponse;
        } catch (iFirmaException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new HttpException(0, sprintf('iFirma API request failed: %s', $e->getMessage()), $e);
        }
    }

    /**
     * @throws AuthenticationException
     * @throws RateLimitException
     * @throws HttpException
     * @throws TransportExceptionInterface
     */
    private function assertSuccessStatus(ResponseInterface $response, string $url): int
    {
        $statusCode = $response->getStatusCode();

        if (401 === $statusCode || 403 === $statusCode) {
            throw new AuthenticationException(sprintf('Authentication failed (HTTP %d)', $statusCode));
        }

        if (429 === $statusCode) {
            throw new RateLimitException();
        }

        if ($statusCode >= 400) {
            throw new HttpException($statusCode, sprintf('HTTP %d from iFirma API: %s', $statusCode, $url));
        }

        return $statusCode;
    }

    /**
     * Decodes the JSON body, unwraps the 'response' envelope when present,
     * and throws ApiException if the API returned a non-zero Kod.
     *
     * @throws HttpException
     * @throws ApiException
     */
    private function decodeJsonResponse(string $content, string $url): ApiResponse
    {
        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new HttpException(0, sprintf('Failed to decode iFirma response from %s: %s', $url, $e->getMessage()), $e);
        }

        if (!is_array($decoded)) {
            throw new HttpException(0, sprintf('Unexpected iFirma response from %s: expected JSON object', $url));
        }

        $payload = (isset($decoded['response']) && is_array($decoded['response']))
            ? $decoded['response']
            : $decoded;

        $kod = $payload['Kod'] ?? null;

        if (is_int($kod) && 0 !== $kod) {
            $info = $payload['Informacja'] ?? null;

            throw new ApiException(is_string($info) ? $info : sprintf('iFirma API error code %d', $kod), $kod);
        }

        return new ApiResponse($payload);
    }
}
