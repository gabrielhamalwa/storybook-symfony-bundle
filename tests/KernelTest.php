<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests;

use Pentatrion\ViteBundle\PentatrionViteBundle;
use Storybook\SymfonyBundle\Asset\AssetPipelineInterface;
use Storybook\SymfonyBundle\Asset\NullAssetPipeline;
use Storybook\SymfonyBundle\Asset\PentatrionViteAssetPipeline;
use Storybook\SymfonyBundle\Controller\StorybookController;
use Storybook\SymfonyBundle\StorybookBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\UX\TwigComponent\TwigComponentBundle;

final class KernelTest extends KernelTestCase
{
    protected static function createKernel(array $options = []): Kernel
    {
        return new class('test', false, $options['asset_pipeline'] ?? 'none') extends Kernel {
            public function __construct(
                string $environment,
                bool $debug,
                private readonly string $assetPipeline,
            ) {
                parent::__construct($environment, $debug);
            }

            public function getCacheDir(): string
            {
                return parent::getCacheDir().'/'.$this->assetPipeline;
            }

            public function registerBundles(): iterable
            {
                return [
                    new FrameworkBundle(),
                    new TwigBundle(),
                    new TwigComponentBundle(),
                    new PentatrionViteBundle(),
                    new StorybookBundle(),
                ];
            }

            public function registerContainerConfiguration(LoaderInterface $loader): void
            {
                $loader->load(function ($container) {
                    $container->loadFromExtension('framework', ['test' => true]);
                    $container->loadFromExtension('twig', ['default_path' => '%kernel.project_dir%/templates']);
                    $container->loadFromExtension('twig_component', ['defaults' => [], 'anonymous_template_directory' => 'components']);
                    $container->loadFromExtension('storybook', [
                        'asset_pipeline' => $this->assetPipeline,
                    ]);
                });
            }
        };
    }

    public function testStorybookControllerIsRegistered(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        self::assertTrue($container->has(StorybookController::class));
        self::assertInstanceOf(StorybookController::class, $container->get(StorybookController::class));
    }

    public function testNullAssetPipelineIsWiredByDefault(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        self::assertInstanceOf(NullAssetPipeline::class, $this->getPipeline($container->get(StorybookController::class)));
    }

    public function testPentatrionViteAssetPipelineIsWiredWhenConfigured(): void
    {
        self::bootKernel(['asset_pipeline' => 'pentatrion_vite']);

        $container = self::getContainer();
        self::assertInstanceOf(PentatrionViteAssetPipeline::class, $this->getPipeline($container->get(StorybookController::class)));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Symfony's kernel can leave its exception handler registered, which PHPUnit 11
        // correctly reports as leaked test state.
        while (true) {
            $previousHandler = set_exception_handler(static fn (): null => null);
            restore_exception_handler();

            if (null === $previousHandler) {
                break;
            }

            restore_exception_handler();
        }
    }

    private function getPipeline(StorybookController $controller): AssetPipelineInterface
    {
        $reflection = new \ReflectionProperty($controller, 'assetExtractor');

        return $reflection->getValue($controller);
    }
}
