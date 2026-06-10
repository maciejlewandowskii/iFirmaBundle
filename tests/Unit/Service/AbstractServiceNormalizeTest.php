<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Service;

use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\Enum\CalculationBasis;
use maciejlewandowskii\iFirmaApi\Enum\PaymentMethod;
use maciejlewandowskii\iFirmaApi\Service\AbstractService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AbstractServiceNormalizeTest extends TestCase
{
    private AbstractServiceTestDouble $service;

    protected function setUp(): void
    {
        $this->service = new AbstractServiceTestDouble(
            $this->createMock(iFirmaClientInterface::class),
            $this->createMock(ValidatorInterface::class),
        );
    }

    public function testConvertsEnumToBackingValueWithPascalCaseKey(): void
    {
        $dto = new readonly class(CalculationBasis::Net, PaymentMethod::Transfer) {
            public function __construct(
                public CalculationBasis $basis,
                public PaymentMethod $method,
            ) {
            }
        };

        $result = $this->service->exposeToArray($dto);

        $this->assertSame('NET', $result['Basis']);
        $this->assertSame('PRZ', $result['Method']);
    }

    public function testDropsNullValues(): void
    {
        $dto = new readonly class('test', null) {
            public function __construct(
                public string $name,
                public ?string $optional,
            ) {
            }
        };

        $result = $this->service->exposeToArray($dto);

        $this->assertArrayHasKey('Name', $result);
        $this->assertArrayNotHasKey('Optional', $result);
    }

    public function testHandlesNestedObjects(): void
    {
        $inner = new readonly class('Warsaw', null) {
            public function __construct(
                public string $city,
                public ?string $street,
            ) {
            }
        };

        $dto = new readonly class($inner) {
            public function __construct(
                public object $address,
            ) {
            }
        };

        $result = $this->service->exposeToArray($dto);

        $this->assertIsArray($result['Address']);
        $this->assertSame('Warsaw', $result['Address']['City']);
        $this->assertArrayNotHasKey('Street', $result['Address']);
    }

    public function testSerializedNameAttributeOverridesPascalCase(): void
    {
        $dto = new readonly class('5252344078') {
            public function __construct(
                #[SerializedName('NIP')]
                public string $taxId,
            ) {
            }
        };

        $result = $this->service->exposeToArray($dto);

        $this->assertArrayHasKey('NIP', $result);
        $this->assertArrayNotHasKey('TaxId', $result);
        $this->assertSame('5252344078', $result['NIP']);
    }

    public function testPreservingCaseUsesOriginalPropertyNames(): void
    {
        $dto = new readonly class('Warszawa', '00-001') {
            public function __construct(
                public string $cityName,
                public string $postalCode,
            ) {
            }
        };

        $result = $this->service->exposeToArrayPreservingCase($dto);

        $this->assertArrayHasKey('cityName', $result);
        $this->assertArrayHasKey('postalCode', $result);
        $this->assertArrayNotHasKey('CityName', $result);
    }
}

final class AbstractServiceTestDouble extends AbstractService
{
    /**
     * @throws ExceptionInterface
     *
     * @return array<string, mixed>
     */
    public function exposeToArray(object $dto): array
    {
        return $this->toArray($dto);
    }

    /**
     * @throws ExceptionInterface
     *
     * @return array<string, mixed>
     */
    public function exposeToArrayPreservingCase(object $dto): array
    {
        return $this->toArrayPreservingCase($dto);
    }
}
