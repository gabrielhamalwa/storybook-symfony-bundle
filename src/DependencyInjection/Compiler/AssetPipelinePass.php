<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\DependencyInjection\Compiler;

use Pentatrion\ViteBundle\Service\EntrypointsLookupCollection;
use Storybook\SymfonyBundle\Asset\AssetExtractorInterface;
use Storybook\SymfonyBundle\Asset\AssetMapperPipeline;
use Storybook\SymfonyBundle\Asset\AssetPipelineInterface;
use Storybook\SymfonyBundle\Asset\EncoreAssetPipeline;
use Storybook\SymfonyBundle\Asset\NullAssetPipeline;
use Storybook\SymfonyBundle\Asset\PentatrionViteAssetPipeline;
use Symfony\Component\AssetMapper\ImportMap\ImportMapGenerator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

final readonly class AssetPipelinePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $pipeline = $container->getParameter('storybook.asset_pipeline');
        $entrypoint = $container->getParameter('storybook.entrypoint');

        if ('auto' !== $pipeline) {
            return;
        }

        $pipeline = $this->detectPipeline($container);

        $definition = match ($pipeline) {
            'pentatrion_vite' => new Definition(PentatrionViteAssetPipeline::class),
            'encore' => new Definition(EncoreAssetPipeline::class),
            'asset_mapper' => new Definition(AssetMapperPipeline::class),
            default => new Definition(NullAssetPipeline::class),
        };

        $definition->setAutowired(true);
        $definition->setArgument('$entrypoint', $entrypoint);

        $container->setDefinition(AssetExtractorInterface::class, $definition);
        $container->setAlias(AssetPipelineInterface::class, AssetExtractorInterface::class);

        if ('asset_mapper' === $pipeline && !$container->has(ImportMapGenerator::class)) {
            $container->setAlias(ImportMapGenerator::class, 'asset_mapper.importmap.generator');
        }
    }

    private function detectPipeline(ContainerBuilder $container): string
    {
        if ($container->has(EntrypointsLookupCollection::class)) {
            return 'pentatrion_vite';
        }

        if ($container->has('webpack_encore.entrypoint_lookup_collection')) {
            return 'encore';
        }

        if ($container->has('asset_mapper.importmap.generator')) {
            return 'asset_mapper';
        }

        return 'none';
    }
}
