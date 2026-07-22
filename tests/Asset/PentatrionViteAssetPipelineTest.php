<?php

declare(strict_types=1);
use Pentatrion\ViteBundle\Service\EntrypointsLookup;
use Pentatrion\ViteBundle\Service\EntrypointsLookupCollection;
use Storybook\SymfonyBundle\Asset\PentatrionViteAssetPipeline;
test('returns empty assets when no entrypoints file', function () {
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
});
test('returns normalized assets for entrypoint', function () {
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
});
test('uses configured entrypoint', function () {
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
});
test('does not duplicate base already included by vite plugin', function () {
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
});
