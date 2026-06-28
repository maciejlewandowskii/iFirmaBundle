<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DependencyInjection;

use Exception;
use Override;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/** @api */
final class IFirmaApiExtension extends Extension
{
    /**
     * @param list<array<string, mixed>> $configs
     *
     * @throws Exception
     */
    #[Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        /** @var array{credentials: array{username: string, invoice_key: string, subscriber_key: string, expense_key: string}} $config */
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $container->setParameter('ifirma_api.credentials.username', $config['credentials']['username']);
        $container->setParameter('ifirma_api.credentials.invoice_key', $config['credentials']['invoice_key']);
        $container->setParameter('ifirma_api.credentials.subscriber_key', $config['credentials']['subscriber_key']);
        $container->setParameter('ifirma_api.credentials.expense_key', $config['credentials']['expense_key']);
    }
}
