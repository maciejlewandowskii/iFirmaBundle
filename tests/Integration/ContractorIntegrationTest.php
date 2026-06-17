<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Integration;

use maciejlewandowskii\iFirmaApi\DTO\Request\Contractor\CreateContractorRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Contractor\UpdateContractorRequest;
use maciejlewandowskii\iFirmaApi\Exception\ContractorNotFoundException;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;

use function sprintf;

#[Group('integration')]
final class ContractorIntegrationTest extends IntegrationTestCase
{
    public function testCreateContractor(): string
    {
        $response = $this->api()->contractorService->create(new CreateContractorRequest(
            name: 'Integration Test Sp. z o.o.',
            postalCode: '00-001',
            city: 'Warszawa',
            taxId: '5252344078',
            street: 'Testowa 1',
            email: 'test@example.com',
            phone: '+48123456789',
            isRecipient: true,
        ));

        $this->assertSame(0, $response->code);
        $this->assertNotEmpty($response->id);

        return $response->id;
    }

    #[Depends('testCreateContractor')]
    public function testGetContractor(string $contractorId): string
    {
        $contractor = $this->api()->contractorService->get($contractorId);

        $this->assertSame('Integration Test Sp. z o.o.', $contractor->name);
        $this->assertSame('00-001', $contractor->postalCode);
        $this->assertSame('Warszawa', $contractor->city);
        $this->assertSame('5252344078', $contractor->taxId);
        $this->assertTrue($contractor->isRecipient);

        return $contractorId;
    }

    #[Depends('testGetContractor')]
    public function testUpdateContractor(string $contractorId): string
    {
        $this->api()->contractorService->update(
            $contractorId,
            new UpdateContractorRequest(
                name: 'Integration Test Updated Sp. z o.o.',
                postalCode: '00-002',
                city: 'Kraków',
                taxId: '5252344078',
                street: 'Nowa 2',
                email: 'updated@example.com',
                isRecipient: true,
            ),
        );

        $updated = $this->api()->contractorService->get($contractorId);
        $this->assertSame('Integration Test Updated Sp. z o.o.', $updated->name);
        $this->assertSame('Kraków', $updated->city);

        return $contractorId;
    }

    #[Depends('testUpdateContractor')]
    public function testSearchContractor(string $contractorId): void
    {
        $results = $this->api()->contractorService->search('Integration Test Updated');

        $found = false;

        foreach ($results as $contractor) {
            if ($contractor->identifier === $contractorId) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, sprintf('Contractor %s not found in search results', $contractorId));
    }

    public function testGetNonExistentContractorThrows(): void
    {
        $this->expectException(ContractorNotFoundException::class);

        $this->api()->contractorService->get('NONEXISTENT_ID_' . time());
    }

    public function testSearchReturnsIterable(): void
    {
        $results = $this->api()->contractorService->search('Test');

        $this->assertIsIterable($results);
    }
}
