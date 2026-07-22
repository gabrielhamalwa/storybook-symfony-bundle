<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\Component;

use PHPUnit\Framework\TestCase;
use Storybook\SymfonyBundle\Component\LiveComponentAdapter;
use Storybook\SymfonyBundle\Dto\RenderRequest;
use Storybook\SymfonyBundle\Tests\Fixtures\Twig\Components\LiveCounter;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\TwigFunction;

final class LiveComponentAdapterTest extends TestCase
{
    public function testThrowsWhenLiveComponentPackageIsNotInstalled(): void
    {
        if (\class_exists('Symfony\\UX\\LiveComponent\\LiveComponentBundle')) {
            $this->markTestSkipped('Symfony UX Live Component is installed.');
        }

        $adapter = new LiveComponentAdapter($this->createMock(Environment::class));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Symfony UX Live Component is not installed.');

        $adapter->render(new RenderRequest(
            id: 'live-counter--default',
            componentId: 'LiveCounter',
        ));
    }

    public function testRendersLiveComponentWhenAvailable(): void
    {
        if (!\class_exists('Symfony\\UX\\LiveComponent\\LiveComponentBundle')) {
            $this->markTestSkipped('Symfony UX Live Component is not installed.');
        }

        $adapter = new LiveComponentAdapter($this->createTwigEnvironment());
        $html = $adapter->render(new RenderRequest(
            id: 'live-counter--default',
            componentId: 'LiveCounter',
            args: ['count' => 5],
        ));

        self::assertSame('<div>Live counter</div>', $html);
    }

    public function testThrowsOnMissingComponentId(): void
    {
        if (!\class_exists('Symfony\\UX\\LiveComponent\\LiveComponentBundle')) {
            $this->markTestSkipped('Symfony UX Live Component is not installed.');
        }

        $adapter = new LiveComponentAdapter($this->createMock(Environment::class));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing component ID for live component adapter.');

        $adapter->render(new RenderRequest(id: 'live-counter--default'));
    }

    public function testRendersLiveCounterFixture(): void
    {
        if (!\class_exists('Symfony\\UX\\LiveComponent\\LiveComponentBundle')) {
            $this->markTestSkipped('Symfony UX Live Component is not installed.');
        }

        self::assertTrue(\class_exists(LiveCounter::class));

        $reflection = new \ReflectionClass(LiveCounter::class);
        $attributes = $reflection->getAttributes(AsLiveComponent::class);
        self::assertCount(1, $attributes);
        self::assertSame('LiveCounter', $attributes[0]->newInstance()->serviceConfig()['key']);

        $adapter = new LiveComponentAdapter($this->createTwigEnvironment());
        $html = $adapter->render(new RenderRequest(
            id: 'live-counter--default',
            componentId: 'LiveCounter',
            args: ['count' => 5],
        ));

        self::assertSame('<div>Live counter</div>', $html);
    }

    private function createTwigEnvironment(): Environment
    {
        $twig = new Environment(new ArrayLoader());
        $twig->addFunction(new TwigFunction(
            'component',
            static function (string $componentId, array $args): string {
                self::assertSame('LiveCounter', $componentId);
                self::assertSame(['count' => 5], $args);

                return '<div>Live counter</div>';
            },
            ['is_safe' => ['html']]
        ));

        return $twig;
    }
}
