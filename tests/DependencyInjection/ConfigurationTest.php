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

        self::assertSame('storybook', $config['environment']);
        self::assertSame('%kernel.project_dir%', $config['project_dir']);
        self::assertSame('%kernel.project_dir%/public', $config['public_dir']);
        self::assertSame('auto', $config['asset_pipeline']);
        self::assertSame('app', $config['entrypoint']);
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
