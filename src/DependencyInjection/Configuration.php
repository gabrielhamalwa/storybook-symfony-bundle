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
                ->scalarNode('environment')->defaultValue('storybook')->end()
                ->scalarNode('project_dir')->defaultValue('%kernel.project_dir%')->end()
                ->scalarNode('public_dir')->defaultValue('%kernel.project_dir%/public')->end()
                ->enumNode('asset_pipeline')
                    ->values(['auto', 'pentatrion_vite', 'encore', 'asset_mapper', 'none'])
                    ->defaultValue('auto')
                ->end()
                ->scalarNode('entrypoint')->defaultValue('app')->end()
                ->arrayNode('component_paths')
                    ->scalarPrototype()->end()
                    ->defaultValue(['src/Twig/Components'])
                ->end()
                ->scalarNode('template_dir')->defaultValue('templates/components')->end()
                ->scalarNode('title_prefix')->defaultValue('Components')->end()
            ->end();

        return $treeBuilder;
    }
}
