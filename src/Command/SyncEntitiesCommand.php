<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Command;

use function count;

use maciejlewandowskii\iFirmaApi\Repository\IFirmaEntityRepositoryInterface;
use maciejlewandowskii\iFirmaApi\Synchronization\SynchronizationManager;
use Override;
use RuntimeException;

use function sprintf;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'ifirma:sync', description: 'Synchronize unsynced entities with iFirma')]
final class SyncEntitiesCommand extends Command
{
    /**
     * @param iterable<IFirmaEntityRepositoryInterface> $repositories
     */
    public function __construct(
        private readonly iterable $repositories,
        private readonly SynchronizationManager $synchronizationManager,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->addArgument('entity-class', InputArgument::REQUIRED, 'Fully-qualified entity class to sync');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $entityClass */
        $entityClass = $input->getArgument('entity-class');

        $repository = $this->findRepository($entityClass);

        if (!$repository instanceof IFirmaEntityRepositoryInterface) {
            $io->error(sprintf('No IFirmaEntityRepositoryInterface found for "%s".', $entityClass));

            return Command::FAILURE;
        }

        $entities = $repository->findUnsynced();

        if ([] === $entities) {
            $io->success('Nothing to sync.');

            return Command::SUCCESS;
        }

        $io->progressStart(count($entities));
        $synced = 0;
        $failed = 0;

        foreach ($entities as $entity) {
            try {
                $this->synchronizationManager->sync($entity);
                ++$synced;
            } catch (RuntimeException $e) {
                $io->warning(sprintf('Failed to sync %s: %s', $entity::class, $e->getMessage()));
                ++$failed;
            } finally {
                $io->progressAdvance();
            }
        }

        $io->progressFinish();
        $io->success(sprintf('Synced %d entity(-ies). Failed: %d.', $synced, $failed));

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function findRepository(string $entityClass): ?IFirmaEntityRepositoryInterface
    {
        foreach ($this->repositories as $repository) {
            if ($repository->getSupportedEntityClass() === $entityClass) {
                return $repository;
            }
        }

        return null;
    }
}
