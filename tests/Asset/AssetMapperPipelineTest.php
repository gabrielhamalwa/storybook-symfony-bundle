<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\Asset;

use PHPUnit\Framework\TestCase;
use Storybook\SymfonyBundle\Asset\AssetMapperPipeline;
use Storybook\SymfonyBundle\Dto\AssetCollection;
use Storybook\SymfonyBundle\Dto\AssetScript;
use Storybook\SymfonyBundle\Dto\AssetStyle;
use Symfony\Component\AssetMapper\ImportMap\ImportMapGenerator;

final class AssetMapperPipelineTest extends TestCase
{
    public function testReturnsEmptyAssetsWhenImportMapThrows(): void
    {
        $generator = $this->createMock(ImportMapGenerator::class);
        $generator->method('getRawImportMapData')->willThrowException(new \RuntimeException('missing'));

        $pipeline = new AssetMapperPipeline($generator, 'app');

        self::assertSame([
            'pipeline' => 'asset-mapper',
            'styles' => [],
            'scripts' => [],
            'importmap' => null,
        ], $pipeline->getAssets());
    }

    public function testReturnsImportmapAndEagerAssets(): void
    {
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
    }

    public function testExtractIncludesFullImportmap(): void
    {
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
    }
}
