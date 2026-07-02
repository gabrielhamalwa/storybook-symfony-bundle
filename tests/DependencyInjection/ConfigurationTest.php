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
        self::assertSame(['src/Twig/Components'], $config['component_paths']);
        self::assertSame('templates/components', $config['template_dir']);
        self::assertSame('Components', $config['title_prefix']);
    }

    public function testComponentPathsCanBeConfigured(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [
            [
                'component_paths' => ['src/Ui/Components', 'src/Components'],
                'template_dir' => 'templates/ui',
                'title_prefix' => 'UI',
            ],
        ]);

        self::assertSame(['src/Ui/Components', 'src/Components'], $config['component_paths']);
        self::assertSame('templates/ui', $config['template_dir']);
        self::assertSame('UI', $config['title_prefix']);
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
