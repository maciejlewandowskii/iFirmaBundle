<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use maciejlewandowskii\iFirmaApi\Bridge\Doctrine\EntityResolver;
use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
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
use maciejlewandowskii\iFirmaApi\Message\SyncEntityMessage;
use maciejlewandowskii\iFirmaApi\MessageHandler\SyncEntityMessageHandler;
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

/**
 * Concrete class needed because class_exists() check in the handler requires a real class.
 */
final class StubInvoiceEntity implements IFirmaInvoiceInterface
{
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
}

final class SyncEntityMessageHandlerTest extends TestCase
{
    private MockObject&iFirmaClientInterface $client;

    private MockObject&EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = $this->createMock(iFirmaClientInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
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

    public function testInvokeResolvesAndSyncsEntity(): void
    {
        $entity = new StubInvoiceEntity();

        $this->entityManager->expects($this->once())
            ->method('find')
            ->with(StubInvoiceEntity::class, 5)
            ->willReturn($entity);

        $this->client->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Identyfikator' => 'FV/1']));

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnCallback(static fn (object $e): object => $e);

        $handler = new SyncEntityMessageHandler(
            new EntityResolver($this->entityManager),
            new SynchronizationManager($this->makeApi(), $eventDispatcher),
        );

        ($handler)(new SyncEntityMessage(StubInvoiceEntity::class, 5));

        $this->assertSame('FV/1', $entity->getIFirmaId());
    }

    public function testInvokeThrowsWhenClassDoesNotExist(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $handler = new SyncEntityMessageHandler(
            new EntityResolver($this->entityManager),
            new SynchronizationManager($this->makeApi(), $eventDispatcher),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/does not exist/');

        ($handler)(new SyncEntityMessage('NonExistent\\Class', 1));
    }
}
