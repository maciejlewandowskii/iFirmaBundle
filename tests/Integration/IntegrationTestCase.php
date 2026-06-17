<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Integration;

use function is_string;

use maciejlewandowskii\iFirmaApi\Exception\ApiException;
use maciejlewandowskii\iFirmaApi\iFirmaApi;
use maciejlewandowskii\iFirmaApi\iFirmaApiFactory;
use PHPUnit\Framework\TestCase;

use function sprintf;

abstract class IntegrationTestCase extends TestCase
{
    private static ?iFirmaApi $api = null;

    protected function setUp(): void
    {
        $username = $this->env('IFIRMA_USERNAME');
        $invoiceKey = $this->env('IFIRMA_INVOICE_KEY');
        $subscriberKey = $this->env('IFIRMA_SUBSCRIBER_KEY');

        if (!$username || !$invoiceKey || !$subscriberKey) {
            $this->markTestSkipped(
                'Integration tests require IFIRMA_USERNAME, IFIRMA_INVOICE_KEY, and IFIRMA_SUBSCRIBER_KEY. ' .
                'Copy .env.test to .env.test.local and fill in your credentials.',
            );
        }
    }

    protected function api(): iFirmaApi
    {
        if (!self::$api instanceof iFirmaApi) {
            self::$api = iFirmaApiFactory::create(
                username: $this->env('IFIRMA_USERNAME'),
                invoiceKeyHex: $this->env('IFIRMA_INVOICE_KEY'),
                subscriberKeyHex: $this->env('IFIRMA_SUBSCRIBER_KEY'),
                expenseKeyHex: $this->env('IFIRMA_EXPENSE_KEY') ?: null,
            );
        }

        return self::$api;
    }

    protected function requireExpenseKey(): void
    {
        if ('' === $this->env('IFIRMA_EXPENSE_KEY') || '0' === $this->env('IFIRMA_EXPENSE_KEY')) {
            $this->markTestSkipped('This test requires IFIRMA_EXPENSE_KEY env var.');
        }
    }

    /**
     * Call inside a catch(ApiException $e) block.
     * Converts known iFirma account-configuration errors into skipped tests
     * so the suite does not ERROR on infrastructure that is outside our control.
     */
    protected function skipOnKnownConfigError(ApiException $e): void
    {
        $knownPhrases = [
            'stosunku sprzedaży opodatkowanej do zwolnionej',
            'nie jest przystosowane',
            'Funkcja niedostępna',
            'niedostępna dla',
            'OSS',
            'IOSS',
            'metoda kasowa',
            'nievatowiec',
            'ryczałt',
            'KSeF',
            'e-Faktura',
            'Nie posiadasz uprawnień',
        ];

        foreach ($knownPhrases as $phrase) {
            if (false !== mb_stripos($e->getMessage(), $phrase)) {
                $this->markTestSkipped(
                    'iFirma account not configured for this test: ' . $e->getMessage(),
                );
            }
        }
    }

    protected function accountingDate(): string
    {
        $month = $this->api()->accountingMonthService->get();

        return sprintf('%04d-%02d-01', $month->year, $month->month);
    }

    protected function addDays(string $date, int $days): string
    {
        $ts = mktime(
            0, 0, 0,
            (int) mb_substr($date, 5, 2),
            (int) mb_substr($date, 8, 2) + $days,
            (int) mb_substr($date, 0, 4),
        );

        return false !== $ts ? date('Y-m-d', $ts) : $date;
    }

    /**
     * Reads an env var from $_ENV (Symfony Dotenv) or falls back to getenv() (system env).
     */
    private function env(string $key): string
    {
        $value = $_ENV[$key] ?? getenv($key);

        return is_string($value) ? $value : '';
    }
}
