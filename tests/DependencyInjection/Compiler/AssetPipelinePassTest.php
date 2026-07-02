<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Pentatrion\ViteBundle\Service\EntrypointsLookupCollection;
use Storybook\SymfonyBundle\Asset\AssetExtractorInterface;
use Storybook\SymfonyBundle\Asset\AssetMapperPipeline;
use Storybook\SymfonyBundle\Asset\AssetPipelineInterface;
use Storybook\SymfonyBundle\Asset\EncoreAssetPipeline;
use Storybook\SymfonyBundle\Asset\NullAssetPipeline;
use Storybook\SymfonyBundle\Asset\PentatrionViteAssetPipeline;
use Storybook\SymfonyBundle\DependencyInjection\Compiler\AssetPipelinePass;
use Storybook\SymfonyBundle\DependencyInjection\StorybookExtension;
use Symfony\Component\AssetMapper\ImportMap\ImportMapGenerator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;

final class AssetPipelinePassTest extends TestCase
{
    public function testDetectsPentatrionVite(): void
    {
        $container = $this->createContainer('auto');
        $container->register(EntrypointsLookupCollection::class, EntrypointsLookupCollection::class);

        (new AssetPipelinePass())->process($container);

        $this->assertServiceIs(PentatrionViteAssetPipeline::class, $container);
    }

    public function testDetectsEncore(): void
    {
        $container = $this->createContainer('auto');
        $container->register('webpack_encore.entrypoint_lookup_collection', EntrypointLookupCollection::class);

        (new AssetPipelinePass())->process($container);

        $this->assertServiceIs(EncoreAssetPipeline::class, $container);
    }

    public function testDetectsAssetMapper(): void
    {
        $container = $this->createContainer('auto');
        $container->register('asset_mapper.importmap.generator', ImportMapGenerator::class);

        (new AssetPipelinePass())->process($container);

        $this->assertServiceIs(AssetMapperPipeline::class, $container);
    }

    public function testFallsBackToNull(): void
    {
        $container = $this->createContainer('auto');

        (new AssetPipelinePass())->process($container);

        $this->assertServiceIs(NullAssetPipeline::class, $container);
    }

    public function testDoesNothingForExplicitPipeline(): void
    {
        $container = $this->createContainer('none');
        $container->setDefinition(AssetExtractorInterface::class, new \Symfony\Component\DependencyInjection\Definition(NullAssetPipeline::class));

        (new AssetPipelinePass())->process($container);

        $this->assertServiceIs(NullAssetPipeline::class, $container);
    }

    private function createContainer(string $pipeline): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('storybook.asset_pipeline', $pipeline);
        $container->setParameter('storybook.entrypoint', 'app');
        $container->setAlias(AssetPipelineInterface::class, AssetExtractorInterface::class);

        return $container;
    }

    private function assertServiceIs(string $class, ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(AssetExtractorInterface::class);
        $this->assertSame($class, $definition->getClass());
    }
}
