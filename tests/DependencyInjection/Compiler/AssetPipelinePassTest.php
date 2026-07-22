<?php

declare(strict_types=1);
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
test('detects pentatrion vite', function () {
    $container = createContainer('auto');
    $container->register(EntrypointsLookupCollection::class, EntrypointsLookupCollection::class);

    (new AssetPipelinePass())->process($container);

    assertServiceIs(PentatrionViteAssetPipeline::class, $container);
});
test('detects encore', function () {
    $container = createContainer('auto');
    $container->register('webpack_encore.entrypoint_lookup_collection', EntrypointLookupCollection::class);

    (new AssetPipelinePass())->process($container);

    assertServiceIs(EncoreAssetPipeline::class, $container);
});
test('detects asset mapper', function () {
    $container = createContainer('auto');
    $container->register('asset_mapper.importmap.generator', ImportMapGenerator::class);

    (new AssetPipelinePass())->process($container);

    assertServiceIs(AssetMapperPipeline::class, $container);
});
test('falls back to null', function () {
    $container = createContainer('auto');

    (new AssetPipelinePass())->process($container);

    assertServiceIs(NullAssetPipeline::class, $container);
});
test('does nothing for explicit pipeline', function () {
    $container = createContainer('none');
    $container->setDefinition(AssetExtractorInterface::class, new \Symfony\Component\DependencyInjection\Definition(NullAssetPipeline::class));

    (new AssetPipelinePass())->process($container);

    assertServiceIs(NullAssetPipeline::class, $container);
});
function createContainer(string $pipeline): ContainerBuilder
{
    $container = new ContainerBuilder();
    $container->setParameter('storybook.asset_pipeline', $pipeline);
    $container->setParameter('storybook.entrypoint', 'app');
    $container->setAlias(AssetPipelineInterface::class, AssetExtractorInterface::class);

    return $container;
}
function assertServiceIs(string $class, ContainerBuilder $container): void
{
    $definition = $container->getDefinition(AssetExtractorInterface::class);
    expect($definition->getClass())->toBe($class);
}
