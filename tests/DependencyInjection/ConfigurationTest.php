<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Storybook\SymfonyBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), []);

        self::assertSame('auto', $config['asset_pipeline']);
        self::assertSame('app', $config['entrypoint']);
        self::assertSame([], $config['cors_allowed_origins']);
    }

    public function testPentatrionVitePipelineCanBeConfigured(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [
            [
                'asset_pipeline' => 'pentatrion_vite',
                'entrypoint' => 'storybook',
            ],
        ]);

        self::assertSame('pentatrion_vite', $config['asset_pipeline']);
        self::assertSame('storybook', $config['entrypoint']);
    }
}
