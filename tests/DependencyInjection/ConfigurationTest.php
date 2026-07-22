<?php

declare(strict_types=1);
use Storybook\SymfonyBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;
test('default configuration', function () {
    $config = (new Processor())->processConfiguration(new Configuration(), []);

    self::assertSame('auto', $config['asset_pipeline']);
    self::assertSame('app', $config['entrypoint']);
    self::assertSame([], $config['cors_allowed_origins']);
});
test('pentatrion vite pipeline can be configured', function () {
    $config = (new Processor())->processConfiguration(new Configuration(), [
        [
            'asset_pipeline' => 'pentatrion_vite',
            'entrypoint' => 'storybook',
        ],
    ]);

    self::assertSame('pentatrion_vite', $config['asset_pipeline']);
    self::assertSame('storybook', $config['entrypoint']);
});
