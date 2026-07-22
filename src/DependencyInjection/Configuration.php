<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('storybook');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->enumNode('asset_pipeline')
                    ->values(['auto', 'pentatrion_vite', 'encore', 'asset_mapper', 'none'])
                    ->defaultValue('auto')
                ->end()
                ->scalarNode('entrypoint')->defaultValue('app')->end()
                ->arrayNode('cors_allowed_origins')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
            ->end();

        return $treeBuilder;
    }
}
