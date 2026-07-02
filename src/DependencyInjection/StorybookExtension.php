<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\DependencyInjection;

use Pentatrion\ViteBundle\Service\EntrypointsLookupCollection;
use Storybook\SymfonyBundle\Asset\AssetPipelineInterface;
use Storybook\SymfonyBundle\Asset\NullAssetPipeline;
use Storybook\SymfonyBundle\Asset\PentatrionViteAssetPipeline;
use Storybook\SymfonyBundle\Controller\StorybookController;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

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
        if ('pentatrion_vite' === $config['asset_pipeline'] && class_exists(EntrypointsLookupCollection::class)) {
            $container
                ->register(AssetPipelineInterface::class, PentatrionViteAssetPipeline::class)
                ->setAutowired(true)
                ->setArgument('$entrypoint', $config['entrypoint']);

            return;
        }

        $container->register(AssetPipelineInterface::class, NullAssetPipeline::class);
    }
}
