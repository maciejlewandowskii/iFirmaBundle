<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Unit\Bridge\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use maciejlewandowskii\iFirmaApi\Bridge\Doctrine\EntityResolver;
use maciejlewandowskii\iFirmaApi\Synchronization\IFirmaEntityInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class EntityResolverTest extends TestCase
{
    public function testResolveSuccessful(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entity = $this->createMock(IFirmaEntityInterface::class);

        /** @var class-string $entityClass */
        $entityClass = 'EntityClass'; // @phpstan-ignore-line
        $entityManager->expects($this->once())
            ->method('find')
            ->with($entityClass, 1)
            ->willReturn($entity);

        $resolver = new EntityResolver($entityManager);
        $result = $resolver->resolve($entityClass, 1);

        $this->assertSame($entity, $result);
    }

    public function testResolveNotFound(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('find')->willReturn(null);

        $resolver = new EntityResolver($entityManager);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Entity of class "EntityClass" with ID "1" not found.');

        /** @var class-string $entityClass */
        $entityClass = 'EntityClass'; // @phpstan-ignore-line
        $resolver->resolve($entityClass, 1);
    }

    public function testResolveInvalidInterface(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('find')->willReturn(new stdClass());

        $resolver = new EntityResolver($entityManager);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Entity of class "stdClass" must implement IFirmaEntityInterface.');

        $resolver->resolve('stdClass', 1);
    }

    public function testResolveWrapsDoctrineException(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('find')->willThrowException(new RuntimeException('DB error'));

        $resolver = new EntityResolver($entityManager);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Failed to load entity/');

        /** @var class-string $entityClass */
        $entityClass = 'EntityClass'; // @phpstan-ignore-line
        $resolver->resolve($entityClass, 1);
    }
}
