# iFirma API Bundle

[![Tests](https://github.com/maciejlewandowskii/iFirmaBundle/actions/workflows/tests.yml/badge.svg)](https://github.com/maciejlewandowskii/iFirmaBundle/actions/workflows/tests.yml)
[![Static Analysis](https://github.com/maciejlewandowskii/iFirmaBundle/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/maciejlewandowskii/iFirmaBundle/actions/workflows/static-analysis.yml)
[![Lint](https://github.com/maciejlewandowskii/iFirmaBundle/actions/workflows/lint.yml/badge.svg)](https://github.com/maciejlewandowskii/iFirmaBundle/actions/workflows/lint.yml)
[![Coverage](https://codecov.io/gh/maciejlewandowskii/iFirmaBundle/graph/badge.svg)](https://codecov.io/gh/maciejlewandowskii/iFirmaBundle)
[![Latest Version](https://img.shields.io/packagist/v/maciejlewandowskii/ifirma-bundle)](https://packagist.org/packages/maciejlewandowskii/ifirma-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/maciejlewandowskii/ifirma-bundle)](https://packagist.org/packages/maciejlewandowskii/ifirma-bundle)
[![License](https://img.shields.io/github/license/maciejlewandowskii/iFirmaBundle)](LICENSE)

Symfony bundle providing a clean integration with the [iFirma API](https://www.ifirma.pl/api) — create and manage invoices, contractors, expenses, payments, employees, and accounting months from your application.

## Requirements

- PHP 8.3+
- Symfony 7.2+

## Installation

```bash
composer require maciejlewandowskii/ifirma-bundle
```

Register the bundle in `config/bundles.php` (auto-registered if Symfony Flex is used):

```php
return [
    // ...
    maciejlewandowskii\iFirmaApi\IFirmaApiBundle::class => ['all' => true],
];
```

## Configuration

Create `config/packages/ifirma_api.yaml`:

```yaml
ifirma_api:
    credentials:
        username:       '%env(IFIRMA_USERNAME)%'
        invoice_key:    '%env(IFIRMA_INVOICE_KEY)%'
        subscriber_key: '%env(IFIRMA_SUBSCRIBER_KEY)%'
        expense_key:    '%env(IFIRMA_EXPENSE_KEY)%'  # optional
```

Add the corresponding environment variables to your `.env`:

```dotenv
IFIRMA_USERNAME=your_username
IFIRMA_INVOICE_KEY=your_invoice_key
IFIRMA_SUBSCRIBER_KEY=your_subscriber_key
IFIRMA_EXPENSE_KEY=your_expense_key
```

## Usage

Inject `iFirmaApi` (or any individual service) wherever you need it:

```php
use maciejlewandowskii\iFirmaApi\iFirmaApi;

class YourService
{
    public function __construct(private readonly iFirmaApi $iFirma) {}

    public function createInvoice(): void
    {
        $invoice = new InvoiceRequest(/* ... */);
        $response = $this->iFirma->invoiceService->create($invoice);
    }
}
```

### Available services

| Service | Description |
|---|---|
| `invoiceService` | Create, update, send, download invoices |
| `contractorService` | Manage contractors |
| `expenseService` | Add VAT and other expenses |
| `paymentService` | Record payments |
| `orderService` | Handle orders |
| `accountingMonthService` | Open / close accounting months |
| `vatDictionaryService` | Fetch VAT rate dictionaries |
| `employeeService` | Manage employees |

### Entity synchronization

Implement `HasIFirmaIntegration` on your Doctrine entity and use the `SynchronizationManager` to automatically push local entities to iFirma:

```php
use maciejlewandowskii\iFirmaApi\Synchronization\HasIFirmaIntegration;

class Invoice implements HasIFirmaIntegration
{
    // implement required methods
}
```

The bundle dispatches `PreSyncEvent` and `PostSyncEvent` so you can hook into the sync lifecycle. A `SyncEntitiesCommand` and an async `SyncEntityMessage` / `SyncEntityMessageHandler` are also included for batch or queue-based synchronization.

### Standalone (without Symfony)

```php
use maciejlewandowskii\iFirmaApi\iFirmaApiFactory;

$api = iFirmaApiFactory::create(
    username: 'your_username',
    invoiceKeyHex: 'your_invoice_key',
    subscriberKeyHex: 'your_subscriber_key',
);

$api->invoiceService->create(/* ... */);
```

## Development

### Running tests

```bash
# Unit tests (no credentials needed)
vendor/bin/phpunit --testsuite Unit

# Integration tests (require real iFirma credentials in .env.test.local)
vendor/bin/phpunit --testsuite Integration
```

Copy `.env.test` to `.env.test.local` and fill in your credentials to run integration tests:

```dotenv
IFIRMA_USERNAME=your_username
IFIRMA_INVOICE_KEY=your_invoice_key
IFIRMA_SUBSCRIBER_KEY=your_subscriber_key
IFIRMA_EXPENSE_KEY=your_expense_key# optional
```

### Code coverage

Coverage is enforced at **95% minimum** on every CI run (PHP 8.3 and 8.4). The full report is uploaded to [Codecov](https://codecov.io/gh/maciejlewandowskii/iFirmaBundle) on each push to `main`.

### Code style & static analysis

```bash
# Fix code style
vendor/bin/php-cs-fixer fix

# Apply Rector refactors
vendor/bin/rector process

# Run PHPStan (level 10)
vendor/bin/phpstan analyse
```

All three are enforced in CI via the [Lint](.github/workflows/lint.yml) and [Static Analysis](.github/workflows/static-analysis.yml) workflows. The security job also runs `composer audit` and `composer-require-checker` on every push.

## License

MIT — see [LICENSE](LICENSE).
