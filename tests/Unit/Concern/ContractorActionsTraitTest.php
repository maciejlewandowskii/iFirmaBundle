<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Concern;

use LogicException;
use maciejlewandowskii\iFirmaApi\Client\ApiResponse;
use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\Concern\ContractorActionsTrait;
use maciejlewandowskii\iFirmaApi\Concern\IFirmaEntityTrait;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaContractorInterface;
use maciejlewandowskii\iFirmaApi\DTO\Request\Contractor\CreateContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Contractor\UpdateContractorRequest;
use maciejlewandowskii\iFirmaApi\iFirmaApi;
use maciejlewandowskii\iFirmaApi\Service\AccountingMonthService;
use maciejlewandowskii\iFirmaApi\Service\ContractorService;
use maciejlewandowskii\iFirmaApi\Service\EmployeeService;
use maciejlewandowskii\iFirmaApi\Service\ExpenseService;
use maciejlewandowskii\iFirmaApi\Service\InvoiceService;
use maciejlewandowskii\iFirmaApi\Service\OrderService;
use maciejlewandowskii\iFirmaApi\Service\PaymentService;
use maciejlewandowskii\iFirmaApi\Service\VatDictionaryService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class ContractorActionsTraitTest extends TestCase
{
    private MockObject&iFirmaClientInterface $client;

    private iFirmaApi $api;

    protected function setUp(): void
    {
        $this->client = $this->createMock(iFirmaClientInterface::class);
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $this->api = new iFirmaApi(
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

    private function makeEntity(): IFirmaContractorInterface
    {
        return new class implements IFirmaContractorInterface {
            use ContractorActionsTrait;
            use IFirmaEntityTrait;

            public function toCreateContractorRequest(): CreateContractorRequest
            {
                return new CreateContractorRequest('Firma Sp. z o.o.', '00-001', 'Warszawa');
            }

            public function toUpdateContractorRequest(): UpdateContractorRequest
            {
                return new UpdateContractorRequest('Firma Sp. z o.o. Updated', '00-001', 'Warszawa');
            }
        };
    }

    public function testCreateOniFirmaSetsIdAndHash(): void
    {
        $this->client->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'CTR-123']));

        $entity = $this->makeEntity();
        $response = $entity->createOniFirma($this->api);

        $this->assertSame('CTR-123', $response->id);
        $this->assertSame('CTR-123', $entity->getIFirmaId());
        $this->assertNotNull($entity->getIFirmaSyncedAt());
        $this->assertNotNull($entity->getIFirmaStateHash());
    }

    public function testUpdateOniFirma(): void
    {
        $this->client->method('put')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'CTR-123']));

        $entity = $this->makeEntity();
        $entity->setIFirmaId('CTR-123');

        $response = $entity->updateOniFirma($this->api);

        $this->assertSame('CTR-123', $response->id);
        $this->assertNotNull($entity->getIFirmaSyncedAt());
        $this->assertNotNull($entity->getIFirmaStateHash());
    }

    public function testUpdateThrowsWhenNotSynced(): void
    {
        $this->expectException(LogicException::class);
        $this->makeEntity()->updateOniFirma($this->api);
    }

    public function testIsSyncStaleWhenNotSynced(): void
    {
        $this->assertTrue($this->makeEntity()->isSyncStale());
    }

    public function testIsSyncStaleReturnsFalseAfterSync(): void
    {
        $this->client->method('post')
            ->willReturn(new ApiResponse(['Kod' => 0, 'Informacja' => 'OK', 'Wynik' => 'CTR-1']));

        $entity = $this->makeEntity();
        $entity->createOniFirma($this->api);

        $this->assertFalse($entity->isSyncStale());
    }

    public function testIsSyncStaleReturnsTrueWhenHashDiffers(): void
    {
        $entity = $this->makeEntity();
        $entity->setIFirmaId('CTR-1');
        $entity->setIFirmaStateHash('wrong-hash');

        $this->assertTrue($entity->isSyncStale());
    }
}
