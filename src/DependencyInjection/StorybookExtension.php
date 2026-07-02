<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\DependencyInjection;

use Pentatrion\ViteBundle\Service\EntrypointsLookupCollection;
use Storybook\SymfonyBundle\Asset\AssetExtractorInterface;
use Storybook\SymfonyBundle\Asset\AssetMapperPipeline;
use Storybook\SymfonyBundle\Asset\AssetPipelineInterface;
use Storybook\SymfonyBundle\Asset\EncoreAssetPipeline;
use Storybook\SymfonyBundle\Asset\NullAssetPipeline;
use Storybook\SymfonyBundle\Asset\PentatrionViteAssetPipeline;
use Storybook\SymfonyBundle\Controller\StorybookController;
use Symfony\Component\AssetMapper\ImportMap\ImportMapGenerator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

final class StorybookExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);

        $container->setParameter('storybook.asset_pipeline', $config['asset_pipeline']);
        $container->setParameter('storybook.entrypoint', $config['entrypoint']);

        $configPath = \dirname(__DIR__).'/Resources/config';

        if (is_dir($configPath)) {
            $loader = new PhpFileLoader($container, new FileLocator($configPath));
            $loader->load('services.php');
        }

        $this->registerAssetPipeline($container, $config);
    }

    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration();
    }

    public function getAlias(): string
    {
        return 'storybook';
    }

    private function registerAssetPipeline(ContainerBuilder $container, array $config): void
    {
        $pipeline = $config['asset_pipeline'];
        if ('auto' === $pipeline) {
            $pipeline = $this->detectPipeline($container);
        }

        if ('pentatrion_vite' === $pipeline && class_exists(EntrypointsLookupCollection::class)) {
            $this->registerExtractor($container, PentatrionViteAssetPipeline::class, $config['entrypoint']);

            return;
        }

        if ('encore' === $pipeline && class_exists(EntrypointLookupInterface::class)) {
            $this->registerExtractor($container, EncoreAssetPipeline::class, $config['entrypoint']);

            return;
        }

        if ('asset_mapper' === $pipeline && class_exists(ImportMapGenerator::class)) {
            $this->registerExtractor($container, AssetMapperPipeline::class, $config['entrypoint']);

            return;
        }

        $this->registerExtractor($container, NullAssetPipeline::class, $config['entrypoint']);
    }

    private function detectPipeline(ContainerBuilder $container): string
    {
        if (class_exists(EntrypointsLookupCollection::class) && $container->has(EntrypointsLookupCollection::class)) {
            return 'pentatrion_vite';
        }

        if (class_exists(EntrypointLookupInterface::class) && $container->has('webpack_encore.entrypoint_lookup_collection')) {
            return 'encore';
        }

        if (class_exists(ImportMapGenerator::class) && $container->has(ImportMapGenerator::class)) {
            return 'asset_mapper';
        }

        return 'none';
    }

    private function registerExtractor(ContainerBuilder $container, string $class, string $entrypoint): void
    {
        $container
            ->register(AssetExtractorInterface::class, $class)
            ->setAutowired(true)
            ->setArgument('$entrypoint', $entrypoint);

        $container->setAlias(AssetPipelineInterface::class, AssetExtractorInterface::class);
    }
}
