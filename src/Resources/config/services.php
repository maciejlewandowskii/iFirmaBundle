<?php

declare(strict_types=1);

use maciejlewandowskii\iFirmaApi\Command\SyncEntitiesCommand;
use maciejlewandowskii\iFirmaApi\iFirmaApi;
use maciejlewandowskii\iFirmaApi\iFirmaApiFactory;
use maciejlewandowskii\iFirmaApi\Repository\IFirmaEntityRepositoryInterface;
use maciejlewandowskii\iFirmaApi\Synchronization\SynchronizationManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

// @noinspection PhpUnused — loaded by PhpFileLoader, not called directly within this project
return static function (ContainerConfigurator $container, ContainerBuilder $builder): void {
    $services = $container->services()->defaults()->autowire()->autoconfigure();

    $services->set(iFirmaApi::class)
        ->factory(iFirmaApiFactory::create(...))
        ->args([
            '%ifirma_api.credentials.username%',
            '%ifirma_api.credentials.invoice_key%',
            '%ifirma_api.credentials.subscriber_key%',
            '%ifirma_api.credentials.expense_key%',
        ]);

    $services->set(SynchronizationManager::class)
        ->args([
            service(iFirmaApi::class),
            service('event_dispatcher'),
        ]);

    $services->set(SyncEntitiesCommand::class)
        ->tag('console.command')
        ->args([
            tagged_iterator('ifirma_api.entity_repository'),
            service(SynchronizationManager::class),
        ]);

    $builder->registerForAutoconfiguration(IFirmaEntityRepositoryInterface::class)
        ->addTag('ifirma_api.entity_repository');
};
