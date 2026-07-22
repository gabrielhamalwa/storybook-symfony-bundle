<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\Asset;

use PHPUnit\Framework\TestCase;
use Pentatrion\ViteBundle\Service\EntrypointsLookup;
use Pentatrion\ViteBundle\Service\EntrypointsLookupCollection;
use Storybook\SymfonyBundle\Asset\PentatrionViteAssetPipeline;

final class PentatrionViteAssetPipelineTest extends TestCase
{
    public function testReturnsEmptyAssetsWhenNoEntrypointsFile(): void
    {
        $lookup = $this->createMock(EntrypointsLookup::class);
        $lookup->method('hasFile')->willReturn(false);

        $collection = $this->createMock(EntrypointsLookupCollection::class);
        $collection->method('getEntrypointsLookup')->willReturn($lookup);

        $pipeline = new PentatrionViteAssetPipeline($collection, 'app');

        self::assertSame([
            'pipeline' => 'pentatrion-vite',
            'styles' => [],
            'scripts' => [],
            'importmap' => null,
        ], $pipeline->getAssets());
    }

    public function testReturnsNormalizedAssetsForEntrypoint(): void
    {
        $lookup = $this->createMock(EntrypointsLookup::class);
        $lookup->method('hasFile')->willReturn(true);
        $lookup->method('getBase')->willReturn('/build/');
        $lookup->method('getCSSFiles')->with('app')->willReturn(['assets/app.css']);
        $lookup->method('getJSFiles')->with('app')->willReturn(['assets/app.js']);

        $collection = $this->createMock(EntrypointsLookupCollection::class);
        $collection->method('getEntrypointsLookup')->willReturn($lookup);

        $pipeline = new PentatrionViteAssetPipeline($collection, 'app');

        self::assertSame([
            'pipeline' => 'pentatrion-vite',
            'styles' => [['url' => '/build/assets/app.css']],
            'scripts' => [['url' => '/build/assets/app.js', 'type' => 'module']],
            'importmap' => null,
        ], $pipeline->getAssets());
    }

    public function testUsesConfiguredEntrypoint(): void
    {
        $lookup = $this->createMock(EntrypointsLookup::class);
        $lookup->method('hasFile')->willReturn(true);
        $lookup->method('getBase')->willReturn('/build/');
        $lookup->method('getCSSFiles')->with('storybook')->willReturn([]);
        $lookup->method('getJSFiles')->with('storybook')->willReturn(['assets/storybook.js']);

        $collection = $this->createMock(EntrypointsLookupCollection::class);
        $collection->method('getEntrypointsLookup')->willReturn($lookup);

        $pipeline = new PentatrionViteAssetPipeline($collection, 'storybook');

        self::assertSame([
            'pipeline' => 'pentatrion-vite',
            'styles' => [],
            'scripts' => [['url' => '/build/assets/storybook.js', 'type' => 'module']],
            'importmap' => null,
        ], $pipeline->getAssets());
    }

    public function testDoesNotDuplicateBaseAlreadyIncludedByVitePlugin(): void
    {
        $lookup = $this->createMock(EntrypointsLookup::class);
        $lookup->method('hasFile')->willReturn(true);
        $lookup->method('getBase')->willReturn('/build/');
        $lookup->method('getCSSFiles')->with('app')->willReturn(['/build/assets/app.css']);
        $lookup->method('getJSFiles')->with('app')->willReturn(['/build/assets/app.js']);

        $collection = $this->createMock(EntrypointsLookupCollection::class);
        $collection->method('getEntrypointsLookup')->willReturn($lookup);

        $pipeline = new PentatrionViteAssetPipeline($collection, 'app');

        self::assertSame([
            'pipeline' => 'pentatrion-vite',
            'styles' => [['url' => '/build/assets/app.css']],
            'scripts' => [['url' => '/build/assets/app.js', 'type' => 'module']],
            'importmap' => null,
        ], $pipeline->getAssets());
    }
}
