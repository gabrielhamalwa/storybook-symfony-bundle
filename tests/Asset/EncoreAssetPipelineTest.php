<?php

declare(strict_types=1);
use Storybook\SymfonyBundle\Asset\EncoreAssetPipeline;
use Storybook\SymfonyBundle\Dto\AssetCollection;
use Storybook\SymfonyBundle\Dto\AssetScript;
use Storybook\SymfonyBundle\Dto\AssetStyle;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
test('returns empty assets when entrypoint throws', function () {
    $lookup = $this->createMock(EntrypointLookupInterface::class);
    $lookup->method('getCssFiles')->willThrowException(new \RuntimeException('missing'));

    $pipeline = new EncoreAssetPipeline($lookup, 'app');

    self::assertSame([
        'pipeline' => 'webpack-encore',
        'styles' => [],
        'scripts' => [],
    ], $pipeline->getAssets());
});
test('returns normalized assets for entrypoint', function () {
    $lookup = $this->createMock(EntrypointLookupInterface::class);
    $lookup->method('getCssFiles')->with('app')->willReturn(['/build/app.css']);
    $lookup->method('getJavaScriptFiles')->with('app')->willReturn(['/build/app.js']);

    $pipeline = new EncoreAssetPipeline($lookup, 'app');

    self::assertSame([
        'pipeline' => 'webpack-encore',
        'styles' => [['url' => '/build/app.css']],
        'scripts' => [['url' => '/build/app.js', 'type' => 'module']],
    ], $pipeline->getAssets());
});
test('extract returns typed collection', function () {
    $lookup = $this->createMock(EntrypointLookupInterface::class);
    $lookup->method('getCssFiles')->with('storybook')->willReturn(['/build/storybook.css']);
    $lookup->method('getJavaScriptFiles')->with('storybook')->willReturn(['/build/storybook.js']);

    $collection = (new EncoreAssetPipeline($lookup, 'storybook'))->extract();

    self::assertSame('webpack-encore', $collection->pipeline);
    self::assertEquals([new AssetStyle('/build/storybook.css', 'webpack-encore')], $collection->styles);
    self::assertEquals([new AssetScript('/build/storybook.js', 'module', 'webpack-encore')], $collection->scripts);
});
