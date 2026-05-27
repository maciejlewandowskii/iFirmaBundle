<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DependencyInjection;

use Override;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/** @api */
final class Configuration implements ConfigurationInterface
{
    #[Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ifirma_api');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->arrayNode('credentials')
            ->isRequired()
            ->children()
            ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('invoice_key')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('subscriber_key')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('expense_key')->defaultValue('')->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
