<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Synchronization;

use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\Concern\ContractorActionsTrait;
use maciejlewandowskii\iFirmaApi\Concern\IFirmaEntityTrait;
use maciejlewandowskii\iFirmaApi\Concern\InvoiceActionsTrait;
use maciejlewandowskii\iFirmaApi\Concern\OtherExpenseActionsTrait;
use maciejlewandowskii\iFirmaApi\Concern\VatExpenseActionsTrait;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaContractorInterface;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaEntityInterface;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaInvoiceInterface;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaOtherExpenseInterface;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaVatExpenseInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Contractor\CreateContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Contractor\UpdateContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\CreateOtherCostRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\CreateVatPurchaseRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Expense\ExpenseContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\CreateInvoiceRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoiceContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Invoice\InvoicePositionRequest;
use maciejlewandowskii\iFirmaApi\Enum\CalculationBasis;
use maciejlewandowskii\iFirmaApi\Enum\ExpenseDocumentType;
use maciejlewandowskii\iFirmaApi\Enum\FormatDateSale;
use maciejlewandowskii\iFirmaApi\Enum\KSeFDesignation;
use maciejlewandowskii\iFirmaApi\Enum\PaymentMethod;
use maciejlewandowskii\iFirmaApi\Enum\RecipientSignatureType;
use maciejlewandowskii\iFirmaApi\Enum\SaleType;
use maciejlewandowskii\iFirmaApi\Enum\VatRateType;
use maciejlewandowskii\iFirmaApi\Event\PostSyncEvent;
use maciejlewandowskii\iFirmaApi\Event\PreSyncEvent;
use maciejlewandowskii\iFirmaApi\iFirmaApi;
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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validation;

final class SynchronizationManagerTest extends TestCase
{
    private MockObject&EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static fn (object $e): object => $e);
    }

    /**
     * @param array<string, mixed> $apiResponse
     */
    private function makeManager(array $apiResponse): SynchronizationManager
    {
        $client = $this->createMock(iFirmaClientInterface::class);
        $response = new ApiResponse($apiResponse);
        $client->method('post')->willReturn($response);
        $client->method('put')->willReturn($response);

        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $api = new iFirmaApi(
            new InvoiceService($client, $validator),
            new ContractorService($client, $validator),
            new ExpenseService($client, $validator),
            new PaymentService($client, $validator),
            new OrderService($client, $validator),
            new AccountingMonthService($client, $validator),
            new VatDictionaryService($client, $validator),
            new EmployeeService($client, $validator),
        );

        return new SynchronizationManager($api, $this->eventDispatcher);
    }

    private static function makeInvoiceRequest(): CreateInvoiceRequest
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
                name: 'Service',
                unit: 'szt',
                vatRateType: VatRateType::Percentage,
                quantity: 1.0,
                unitPrice: 100.0,
            )],
        );
    }

    private static function makeVatPurchaseRequest(): CreateVatPurchaseRequest
    {
        return new CreateVatPurchaseRequest(
            invoiceNumber: 'FV/2024/1',
            kSeFDesignation: KSeFDesignation::Off,
            issueDate: '2024-03-01',
            saleType: SaleType::Taxable,
            expenseName: 'Test expense',
            netAmount23: 100.0,
            netAmount08: 0.0,
            netAmount05: 0.0,
            netAmount00: 0.0,
            netAmountExempt: 0.0,
            vatAmount23: 23.0,
            vatAmount08: 0.0,
            vatAmount05: 0.0,
        );
    }

    private static function makeOtherCostRequest(): CreateOtherCostRequest
    {
        return new CreateOtherCostRequest(
            documentType: ExpenseDocumentType::Receipt,
            documentNumber: 'R/2024/1',
            issueDate: '2024-03-01',
            paymentDeadline: '2024-03-15',
            expenseName: 'Receipt cost',
            amount: 50.0,
            contractor: new ExpenseContractorRequest(name: 'Vendor', postalCode: '00-001', city: 'Warszawa'),
        );
    }

    public function testSyncInvoiceCreatesAndSetsId(): void
    {
        $manager = $this->makeManager(['Kod' => 0, 'Informacja' => 'OK', 'Identyfikator' => 'FV/1/2024']);
        $request = self::makeInvoiceRequest();
        $entity = new class($request) implements IFirmaInvoiceInterface {
            use IFirmaEntityTrait;
            use InvoiceActionsTrait;

            public function __construct(private readonly CreateInvoiceRequest $req)
            {
            }

            public function toCreateInvoiceRequest(): CreateInvoiceRequest
            {
                return $this->req;
            }
        };

        $manager->sync($entity);

        $this->assertSame('FV/1/2024', $entity->getIFirmaId());
        $this->assertNotNull($entity->getIFirmaSyncedAt());
        $this->assertNotNull($entity->getIFirmaStateHash());
    }

    public function testSyncInvoiceDispatchesPreAndPostEvents(): void
    {
        $manager = $this->makeManager(['Kod' => 0, 'Informacja' => 'OK', 'Identyfikator' => 'FV/1']);
        $request = self::makeInvoiceRequest();
        $entity = new class($request) implements IFirmaInvoiceInterface {
            use IFirmaEntityTrait;
            use InvoiceActionsTrait;

            public function __construct(private readonly CreateInvoiceRequest $req)
            {
            }

            public function toCreateInvoiceRequest(): CreateInvoiceRequest
            {
                return $this->req;
            }
        };

        $dispatched = [];
        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function (object $e) use (&$dispatched): object {
                $dispatched[] = $e::class;

                return $e;
            });

        $manager->sync($entity);

        $this->assertContains(PreSyncEvent::class, $dispatched);
        $this->assertContains(PostSyncEvent::class, $dispatched);
    }

    public function testSyncCanceledByPreEvent(): void
    {
        $manager = $this->makeManager(['Kod' => 0, 'Informacja' => 'OK', 'Identyfikator' => 'FV/1']);
        $request = self::makeInvoiceRequest();
        $entity = new class($request) implements IFirmaInvoiceInterface {
            use IFirmaEntityTrait;
            use InvoiceActionsTrait;

            public function __construct(private readonly CreateInvoiceRequest $req)
            {
            }

            public function toCreateInvoiceRequest(): CreateInvoiceRequest
            {
                return $this->req;
            }
        };

        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function (object $e): object {
                if ($e instanceof PreSyncEvent) {
                    $e->cancel();
                }

                return $e;
            });

        $result = $manager->sync($entity);

        $this->assertNull($entity->getIFirmaId());
        $this->assertInstanceOf(CreateInvoiceRequest::class, $result);
    }

    public function testSyncContractorCreatesWhenNotSynced(): void
    {
        $manager = $this->makeManager(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'CTR-1']);
        $entity = new class implements IFirmaContractorInterface {
            use ContractorActionsTrait;
            use IFirmaEntityTrait;

            public function toCreateContractorRequest(): CreateContractorRequest
            {
                return new CreateContractorRequest('Firma', '00-001', 'Warszawa');
            }

            public function toUpdateContractorRequest(): UpdateContractorRequest
            {
                return new UpdateContractorRequest('Firma', '00-001', 'Warszawa');
            }
        };

        $manager->sync($entity);

        $this->assertSame('CTR-1', $entity->getIFirmaId());
    }

    public function testSyncContractorUpdatesWhenAlreadySynced(): void
    {
        $manager = $this->makeManager(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'CTR-1']);
        $entity = new class implements IFirmaContractorInterface {
            use ContractorActionsTrait;
            use IFirmaEntityTrait;

            public function toCreateContractorRequest(): CreateContractorRequest
            {
                return new CreateContractorRequest('Firma', '00-001', 'Warszawa');
            }

            public function toUpdateContractorRequest(): UpdateContractorRequest
            {
                return new UpdateContractorRequest('Firma Updated', '00-001', 'Warszawa');
            }
        };

        $entity->setIFirmaId('EXISTING-ID');
        $manager->sync($entity);

        $this->assertNotNull($entity->getIFirmaSyncedAt());
    }

    public function testSyncVatExpenseSetsId(): void
    {
        $manager = $this->makeManager(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'EXP-1']);
        $request = self::makeVatPurchaseRequest();
        $entity = new class($request) implements IFirmaVatExpenseInterface {
            use IFirmaEntityTrait;
            use VatExpenseActionsTrait;

            public function __construct(private readonly CreateVatPurchaseRequest $req)
            {
            }

            public function toCreateVatPurchaseRequest(): CreateVatPurchaseRequest
            {
                return $this->req;
            }
        };

        $manager->sync($entity);

        $this->assertSame('EXP-1', $entity->getIFirmaId());
        $this->assertNotNull($entity->getIFirmaSyncedAt());
    }

    public function testSyncOtherExpenseSetsId(): void
    {
        $manager = $this->makeManager(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'OTH-1']);
        $request = self::makeOtherCostRequest();
        $entity = new class($request) implements IFirmaOtherExpenseInterface {
            use IFirmaEntityTrait;
            use OtherExpenseActionsTrait;

            public function __construct(private readonly CreateOtherCostRequest $req)
            {
            }

            public function toCreateOtherCostRequest(): CreateOtherCostRequest
            {
                return $this->req;
            }
        };

        $manager->sync($entity);

        $this->assertSame('OTH-1', $entity->getIFirmaId());
    }

    public function testSyncContractorCanceledByPreEvent(): void
    {
        $manager = $this->makeManager(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'CTR-1']);
        $entity = new class implements IFirmaContractorInterface {
            use ContractorActionsTrait;
            use IFirmaEntityTrait;

            public function toCreateContractorRequest(): CreateContractorRequest
            {
                return new CreateContractorRequest('Firma', '00-001', 'Warszawa');
            }

            public function toUpdateContractorRequest(): UpdateContractorRequest
            {
                return new UpdateContractorRequest('Firma', '00-001', 'Warszawa');
            }
        };

        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function (object $e): object {
                if ($e instanceof PreSyncEvent) {
                    $e->cancel();
                }

                return $e;
            });

        $result = $manager->sync($entity);

        $this->assertNull($entity->getIFirmaId());
        $this->assertInstanceOf(CreateContractorRequest::class, $result);
    }

    public function testSyncVatExpenseCanceledByPreEvent(): void
    {
        $manager = $this->makeManager(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'EXP-1']);
        $request = self::makeVatPurchaseRequest();
        $entity = new class($request) implements IFirmaVatExpenseInterface {
            use IFirmaEntityTrait;
            use VatExpenseActionsTrait;

            public function __construct(private readonly CreateVatPurchaseRequest $req)
            {
            }

            public function toCreateVatPurchaseRequest(): CreateVatPurchaseRequest
            {
                return $this->req;
            }
        };

        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function (object $e): object {
                if ($e instanceof PreSyncEvent) {
                    $e->cancel();
                }

                return $e;
            });

        $result = $manager->sync($entity);

        $this->assertNull($entity->getIFirmaId());
        $this->assertInstanceOf(CreateVatPurchaseRequest::class, $result);
    }

    public function testSyncOtherExpenseCanceledByPreEvent(): void
    {
        $manager = $this->makeManager(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'OTH-1']);
        $request = self::makeOtherCostRequest();
        $entity = new class($request) implements IFirmaOtherExpenseInterface {
            use IFirmaEntityTrait;
            use OtherExpenseActionsTrait;

            public function __construct(private readonly CreateOtherCostRequest $req)
            {
            }

            public function toCreateOtherCostRequest(): CreateOtherCostRequest
            {
                return $this->req;
            }
        };

        $this->eventDispatcher->method('dispatch')
            ->willReturnCallback(static function (object $e): object {
                if ($e instanceof PreSyncEvent) {
                    $e->cancel();
                }

                return $e;
            });

        $result = $manager->sync($entity);

        $this->assertNull($entity->getIFirmaId());
        $this->assertInstanceOf(CreateOtherCostRequest::class, $result);
    }

    public function testSyncThrowsForUnknownEntityType(): void
    {
        $manager = $this->makeManager([]);
        $entity = new class implements IFirmaEntityInterface {
            use IFirmaEntityTrait;
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/must implement one of/');

        $manager->sync($entity);
    }
}
