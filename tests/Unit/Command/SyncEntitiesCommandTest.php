<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Command;

use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\Command\SyncEntitiesCommand;
use maciejlewandowskii\iFirmaApi\Concern\IFirmaEntityTrait;
use maciejlewandowskii\iFirmaApi\Concern\InvoiceActionsTrait;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaInvoiceInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoiceContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoicePositionRequest;
use maciejlewandowskii\iFirmaApi\Enum\CalculationBasis;
use maciejlewandowskii\iFirmaApi\Enum\FormatDateSale;
use maciejlewandowskii\iFirmaApi\Enum\PaymentMethod;
use maciejlewandowskii\iFirmaApi\Enum\RecipientSignatureType;
use maciejlewandowskii\iFirmaApi\Enum\VatRateType;
use maciejlewandowskii\iFirmaApi\iFirmaApi;
use maciejlewandowskii\iFirmaApi\Repository\IFirmaEntityRepositoryInterface;
use maciejlewandowskii\iFirmaApi\Service\AccountingMonthService;
use maciejlewandowskii\iFirmaApi\Service\ContractorService;
use maciejlewandowskii\iFirmaApi\Service\EmployeeService;
use maciejlewandowskii\iFirmaApi\Service\ExpenseService;
use maciejlewandowskii\iFirmaApi\Service\InvoiceService;
use maciejlewandowskii\iFirmaApi\Service\OrderService;
use maciejlewandowskii\iFirmaApi\Service\PaymentService;
use maciejlewandowskii\iFirmaApi\Service\VatDictionaryService;
use maciejlewandowskii\iFirmaApi\Synchronization\SynchronizationManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validation;

final class SyncEntitiesCommandTest extends TestCase
{
    private MockObject&iFirmaClientInterface $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(iFirmaClientInterface::class);
    }

    private function makeApi(): iFirmaApi
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        return new iFirmaApi(
            new InvoiceService($this->client, $validator),
            new ContractorService($this->client, $validator),
            new ExpenseService($this->client, $validator),
            new PaymentService($this->client, $validator),
            new OrderService($this->client, $validator),
            new AccountingMonthService($this->client, $validator),
            new VatDictionaryService($this->client, $validator),
            new EmployeeService($this->client, $validator),
        );
    }

    private function makeSyncManager(): SynchronizationManager
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnCallback(static fn (object $e): object => $e);

        return new SynchronizationManager($this->makeApi(), $eventDispatcher);
    }

    private static function makeInvoiceEntity(): IFirmaInvoiceInterface
    {
        return new class implements IFirmaInvoiceInterface {
            use IFirmaEntityTrait;
            use InvoiceActionsTrait;

            public function toCreateInvoiceRequest(): CreateInvoiceRequest
            {
                return new CreateInvoiceRequest(
                    calculationBasis: CalculationBasis::Net,
                    issueDate: '2024-03-01',
                    saleDate: '2024-03-01',
                    saleDateFormat: FormatDateSale::Daily,
                    paymentMethod: PaymentMethod::Transfer,
                    recipientSignatureType: RecipientSignatureType::WithoutSignatures,
                    contractor: new InvoiceContractorRequest(name: 'Test', postalCode: '00-001', city: 'Warszawa'),
                    positions: [new InvoicePositionRequest(
                        name: 'Item',
                        unit: 'szt',
                        vatRateType: VatRateType::Percentage,
                        quantity: 1.0,
                        unitPrice: 100.0,
                    )],
                );
            }
        };
    }

    public function testCommandSuccessfullySyncsEntities(): void
    {
        $entity = self::makeInvoiceEntity();

        $repository = new readonly class($entity) implements IFirmaEntityRepositoryInterface {
            public function __construct(private IFirmaInvoiceInterface $entity)
            {
            }

            public function getSupportedEntityClass(): string
            {
                return IFirmaInvoiceInterface::class;
            }

            public function findUnsynced(): array
            {
                return [$this->entity];
            }
        };

        $this->client->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Identyfikator' => 'FV/1']));

        $command = new SyncEntitiesCommand([$repository], $this->makeSyncManager());
        $tester = new CommandTester($command);

        $exitCode = $tester->execute(['entity-class' => IFirmaInvoiceInterface::class]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertSame('FV/1', $entity->getIFirmaId());
    }

    public function testCommandReturnsFailureWhenNoRepositoryFound(): void
    {
        $command = new SyncEntitiesCommand([], $this->makeSyncManager());
        $tester = new CommandTester($command);

        $exitCode = $tester->execute(['entity-class' => 'App\\Entity\\Unknown']);

        $this->assertSame(Command::FAILURE, $exitCode);
    }

    public function testCommandSuccessWhenNothingToSync(): void
    {
        $repository = new class implements IFirmaEntityRepositoryInterface {
            public function getSupportedEntityClass(): string
            {
                return IFirmaInvoiceInterface::class;
            }

            public function findUnsynced(): array
            {
                return [];
            }
        };

        $command = new SyncEntitiesCommand([$repository], $this->makeSyncManager());
        $tester = new CommandTester($command);

        $exitCode = $tester->execute(['entity-class' => IFirmaInvoiceInterface::class]);

        $this->assertSame(Command::SUCCESS, $exitCode);
    }

    public function testCommandReturnsFailureWhenSyncThrows(): void
    {
        $entity = self::makeInvoiceEntity();

        $repository = new readonly class($entity) implements IFirmaEntityRepositoryInterface {
            public function __construct(private IFirmaInvoiceInterface $entity)
            {
            }

            public function getSupportedEntityClass(): string
            {
                return IFirmaInvoiceInterface::class;
            }

            public function findUnsynced(): array
            {
                return [$this->entity];
            }
        };

        $this->client->method('post')
            ->willThrowException(new RuntimeException('API call failed'));

        $command = new SyncEntitiesCommand([$repository], $this->makeSyncManager());
        $tester = new CommandTester($command);

        $exitCode = $tester->execute(['entity-class' => IFirmaInvoiceInterface::class]);

        $this->assertSame(Command::FAILURE, $exitCode);
    }
}
