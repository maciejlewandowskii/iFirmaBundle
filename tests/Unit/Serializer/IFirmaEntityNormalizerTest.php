<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Serializer;

use maciejlewandowskii\iFirmaApi\Attribute\SkipSynchronization;
use maciejlewandowskii\iFirmaApi\Serializer\IFirmaEntityNormalizer;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class IFirmaEntityNormalizerTest extends TestCase
{
    private IFirmaEntityNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new IFirmaEntityNormalizer(new ObjectNormalizer());
    }

    /**
     * @throws ReflectionException
     * @throws ExceptionInterface
     */
    public function testNormalizeConvertsToPascalCaseAndRespectsSkipAttribute(): void
    {
        $entity = new class {
            // @noinspection PhpUnused — accessed via reflection by IFirmaEntityNormalizer
            public string $name = 'Test Name';

            // @noinspection PhpUnused
            public string $postal_code = '12-345';

            #[SkipSynchronization]
            // @noinspection PhpUnused
            public string $internal_note = 'Should be skipped';
        };

        $normalized = $this->normalizer->normalize($entity, null, ['ifirma_sync' => true]);

        $this->assertArrayHasKey('Name', $normalized);
        $this->assertArrayHasKey('PostalCode', $normalized);
        $this->assertArrayNotHasKey('InternalNote', $normalized);
        $this->assertEquals('Test Name', $normalized['Name']);
        $this->assertEquals('12-345', $normalized['PostalCode']);
    }

    public function testSupportsNormalization(): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization(new stdClass(), null, ['ifirma_sync' => true]));
        $this->assertFalse($this->normalizer->supportsNormalization(new stdClass()));
        $this->assertFalse($this->normalizer->supportsNormalization('not an object', null, ['ifirma_sync' => true]));
    }

    /**
     * @throws ReflectionException
     * @throws ExceptionInterface
     */
    public function testNormalizeReturnsEmptyArrayForNonObject(): void
    {
        $result = $this->normalizer->normalize('not-an-object', null, ['ifirma_sync' => true]);
        $this->assertSame([], $result);
    }

    public function testGetSupportedTypesReturnsObjectMapping(): void
    {
        $types = $this->normalizer->getSupportedTypes(null);
        $this->assertArrayHasKey('object', $types);
        $this->assertTrue($types['object']);
    }
}
