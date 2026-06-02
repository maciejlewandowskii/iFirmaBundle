<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Bridge\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaEntityInterface;
use RuntimeException;

use function sprintf;

use Throwable;

final readonly class EntityResolver
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param class-string $entityClass
     *
     * @throws RuntimeException
     */
    public function resolve(string $entityClass, string|int $entityId): IFirmaEntityInterface
    {
        try {
            $entity = $this->entityManager->find($entityClass, $entityId);
        } catch (Throwable $e) {
            throw new RuntimeException(sprintf('Failed to load entity "%s": %s', $entityClass, $e->getMessage()), 0, $e);
        }

        if (null === $entity) {
            throw new RuntimeException(sprintf('Entity of class "%s" with ID "%s" not found.', $entityClass, $entityId));
        }

        if (!$entity instanceof IFirmaEntityInterface) {
            throw new RuntimeException(sprintf('Entity of class "%s" must implement IFirmaEntityInterface.', $entity::class));
        }

        return $entity;
    }
}
