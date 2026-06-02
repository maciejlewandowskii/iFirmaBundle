<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\MessageHandler;

use function class_exists;

use maciejlewandowskii\iFirmaApi\Bridge\Doctrine\EntityResolver;
use maciejlewandowskii\iFirmaApi\Message\SyncEntityMessage;
use maciejlewandowskii\iFirmaApi\Synchronization\SynchronizationManager;
use RuntimeException;

use function sprintf;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SyncEntityMessageHandler
{
    public function __construct(
        private EntityResolver $entityResolver,
        private SynchronizationManager $synchronizationManager,
    ) {
    }

    public function __invoke(SyncEntityMessage $message): void
    {
        if (!class_exists($message->entityClass)) {
            throw new RuntimeException(sprintf('Entity class "%s" does not exist.', $message->entityClass));
        }

        $entity = $this->entityResolver->resolve($message->entityClass, $message->entityId);
        $this->synchronizationManager->sync($entity);
    }
}
