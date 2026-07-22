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
        $container->setParameter('storybook.cors_allowed_origins', $config['cors_allowed_origins']);

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
        if ('auto' === $config['asset_pipeline']) {
            return;
        }

        $pipeline = $config['asset_pipeline'];

        if ('pentatrion_vite' === $pipeline && class_exists(EntrypointsLookupCollection::class)) {
            $this->registerExtractor($container, PentatrionViteAssetPipeline::class, $config['entrypoint']);

            return;
        }

        if ('encore' === $pipeline && interface_exists(EntrypointLookupInterface::class)) {
            $this->registerExtractor($container, EncoreAssetPipeline::class, $config['entrypoint']);

            return;
        }

        if ('asset_mapper' === $pipeline && class_exists(ImportMapGenerator::class)) {
            $this->registerExtractor($container, AssetMapperPipeline::class, $config['entrypoint']);
            if (!$container->has(ImportMapGenerator::class)) {
                $container->setAlias(ImportMapGenerator::class, 'asset_mapper.importmap.generator');
            }

            return;
        }

        $this->registerExtractor($container, NullAssetPipeline::class, $config['entrypoint']);
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
