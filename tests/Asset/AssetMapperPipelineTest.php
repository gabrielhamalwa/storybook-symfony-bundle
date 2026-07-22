<?php

declare(strict_types=1);
use Storybook\SymfonyBundle\Asset\AssetMapperPipeline;
use Storybook\SymfonyBundle\Dto\AssetCollection;
use Storybook\SymfonyBundle\Dto\AssetScript;
use Storybook\SymfonyBundle\Dto\AssetStyle;
use Symfony\Component\AssetMapper\ImportMap\ImportMapGenerator;
test('returns empty assets when import map throws', function () {
    $generator = $this->createMock(ImportMapGenerator::class);
    $generator->method('getRawImportMapData')->willThrowException(new \RuntimeException('missing'));

    $pipeline = new AssetMapperPipeline($generator, 'app');

    self::assertSame([
        'pipeline' => 'asset-mapper',
        'styles' => [],
        'scripts' => [],
        'importmap' => null,
    ], $pipeline->getAssets());
});
test('returns importmap and eager assets', function () {
    $generator = $this->createMock(ImportMapGenerator::class);
    $generator->method('getRawImportMapData')->willReturn([
        'app' => ['path' => '/assets/app-hash.js', 'type' => 'js'],
        'app.css' => ['path' => '/assets/app-hash.css', 'type' => 'css'],
        'other' => ['path' => '/assets/other-hash.js', 'type' => 'js'],
    ]);
    $generator->method('findEagerEntrypointImports')->with('app')->willReturn(['app', 'app.css']);

    $pipeline = new AssetMapperPipeline($generator, 'app');

    self::assertSame([
        'pipeline' => 'asset-mapper',
        'styles' => [['url' => '/assets/app-hash.css']],
        'scripts' => [['url' => '/assets/app-hash.js', 'type' => 'module']],
        'importmap' => [
            'imports' => [
                'app' => '/assets/app-hash.js',
                'app.css' => '/assets/app-hash.css',
                'other' => '/assets/other-hash.js',
            ],
        ],
    ], $pipeline->getAssets());
});
test('always includes entrypoint script', function () {
    $generator = $this->createMock(ImportMapGenerator::class);
    $generator->method('getRawImportMapData')->willReturn([
        'app' => ['path' => '/assets/app-hash.js', 'type' => 'js'],
        'other' => ['path' => '/assets/other-hash.js', 'type' => 'js'],
    ]);
    $generator->method('findEagerEntrypointImports')->with('app')->willReturn(['other']);

    $collection = (new AssetMapperPipeline($generator, 'app'))->extract();

    self::assertCount(2, $collection->scripts);
    self::assertEquals('/assets/app-hash.js', $collection->scripts[0]->src);
    self::assertEquals('/assets/other-hash.js', $collection->scripts[1]->src);
});
test('extract includes full importmap', function () {
    $generator = $this->createMock(ImportMapGenerator::class);
    $generator->method('getRawImportMapData')->willReturn([
        'app' => ['path' => '/assets/app-hash.js', 'type' => 'js'],
        'other' => ['path' => '/assets/other-hash.js', 'type' => 'js'],
    ]);
    $generator->method('findEagerEntrypointImports')->with('app')->willReturn(['app']);

    $collection = (new AssetMapperPipeline($generator, 'app'))->extract();

    self::assertSame('asset-mapper', $collection->pipeline);
    self::assertEquals([new AssetScript('/assets/app-hash.js', 'module', 'asset-mapper')], $collection->scripts);
    self::assertEquals(['imports' => ['app' => '/assets/app-hash.js', 'other' => '/assets/other-hash.js']], $collection->importmap);
});
