<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Client;

use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use maciejlewandowskii\iFirmaApi\Exception\ApiException;
use maciejlewandowskii\iFirmaApi\Exception\AuthenticationException;
use maciejlewandowskii\iFirmaApi\Exception\HttpException;
use maciejlewandowskii\iFirmaApi\Exception\RateLimitException;

interface iFirmaClientInterface
{
    /**
     * @param array<string, mixed> $queryParams
     *
     * @throws AuthenticationException
     * @throws RateLimitException
     * @throws HttpException
     * @throws ApiException
     */
    public function get(string $path, AuthKeyType $keyType, array $queryParams = []): ApiResponse;

    /**
     * @param array<string, mixed> $body
     * @param array<string, mixed> $queryParams
     *
     * @throws AuthenticationException
     * @throws RateLimitException
     * @throws HttpException
     * @throws ApiException
     */
    public function post(string $path, AuthKeyType $keyType, array $body, array $queryParams = []): ApiResponse;

    /**
     * @param array<string, mixed> $body
     *
     * @throws AuthenticationException
     * @throws RateLimitException
     * @throws HttpException
     * @throws ApiException
     */
    public function put(string $path, AuthKeyType $keyType, array $body): ApiResponse;

    /**
     * @param array<string, mixed> $queryParams
     *
     * @throws AuthenticationException
     * @throws RateLimitException
     * @throws HttpException
     */
    public function getRaw(string $path, AuthKeyType $keyType, array $queryParams = []): string;
}
