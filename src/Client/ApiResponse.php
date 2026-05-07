<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Client;

use function array_key_exists;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;

/**
 * Typed wrapper around a decoded JSON response from the iFirma API.
 * All accessor methods narrow the underlying mixed value to the expected scalar type
 * using explicit type guards, satisfying PHPStan level 10 without unsafe casts.
 */
final readonly class ApiResponse
{
    /** @param array<int|string, mixed> $data */
    public function __construct(private array $data)
    {
    }

    public function getInt(string $key, int $default = 0): int
    {
        $v = $this->data[$key] ?? $default;

        return match (true) {
            is_int($v) => $v,
            is_float($v), is_string($v) => (int) $v,
            is_bool($v) => $v ? 1 : 0,
            default => $default,
        };
    }

    public function getString(string $key, string $default = ''): string
    {
        $v = $this->data[$key] ?? $default;

        return match (true) {
            is_string($v) => $v,
            is_int($v), is_float($v) => (string) $v,
            is_bool($v) => $v ? '1' : '0',
            default => $default,
        };
    }

    public function getFloat(string $key, float $default = 0.0): float
    {
        $v = $this->data[$key] ?? $default;

        return match (true) {
            is_float($v) => $v,
            is_int($v), is_string($v) => (float) $v,
            is_bool($v) => $v ? 1.0 : 0.0,
            default => $default,
        };
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $v = $this->data[$key] ?? $default;

        return match (true) {
            is_bool($v) => $v,
            is_int($v) => 0 !== $v,
            is_float($v) => 0.0 !== $v,
            is_string($v) => '' !== $v && '0' !== $v,
            default => $default,
        };
    }

    public function getNullableString(string $key): ?string
    {
        if (!array_key_exists($key, $this->data)) {
            return null;
        }

        $v = $this->data[$key];

        return match (true) {
            is_string($v) => $v,
            is_int($v), is_float($v) => (string) $v,
            is_bool($v) => $v ? '1' : '0',
            default => null,
        };
    }

    /** @return list<self> */
    public function getResponseList(string $key): array
    {
        $list = $this->data[$key] ?? [];

        if (!is_array($list)) {
            return [];
        }
        $result = [];

        foreach ($list as $item) {
            if (is_array($item)) {
                $result[] = new self($item);
            }
        }

        return $result;
    }

    public function getFirstResponse(string $key): ?self
    {
        return $this->getResponseList($key)[0] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function isEmpty(): bool
    {
        return [] === $this->data;
    }

    /** @return array<int|string, mixed> */
    public function toArray(): array
    {
        return $this->data;
    }
}
