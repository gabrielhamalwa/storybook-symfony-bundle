<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\Component;

use PHPUnit\Framework\TestCase;
use Storybook\SymfonyBundle\Component\LiveComponentAdapter;
use Storybook\SymfonyBundle\Dto\RenderRequest;
use Symfony\UX\TwigComponent\ComponentRendererInterface;

final class LiveComponentAdapterTest extends TestCase
{
    public function testThrowsWhenLiveComponentPackageIsNotInstalled(): void
    {
        if (\class_exists('Symfony\\UX\\LiveComponent\\LiveComponentBundle')) {
            $this->markTestSkipped('Symfony UX Live Component is installed.');
        }

        $adapter = new LiveComponentAdapter($this->createMock(ComponentRendererInterface::class));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Symfony UX Live Component is not installed.');

        $adapter->render(new RenderRequest(
            id: 'live-button--default',
            componentId: 'LiveButton',
        ));
    }

    public function testRendersLiveComponentWhenAvailable(): void
    {
        if (!\class_exists('Symfony\\UX\\LiveComponent\\LiveComponentBundle')) {
            $this->markTestSkipped('Symfony UX Live Component is not installed.');
        }

        $renderer = $this->createMock(ComponentRendererInterface::class);
        $renderer
            ->method('createAndRender')
            ->with('LiveButton', ['label' => 'Click me'])
            ->willReturn('<div>Live button</div>');

        $adapter = new LiveComponentAdapter($renderer);
        $html = $adapter->render(new RenderRequest(
            id: 'live-button--default',
            componentId: 'LiveButton',
            args: ['label' => 'Click me'],
        ));

        self::assertSame('<div>Live button</div>', $html);
    }

    public function testThrowsOnMissingComponentId(): void
    {
        if (!\class_exists('Symfony\\UX\\LiveComponent\\LiveComponentBundle')) {
            $this->markTestSkipped('Symfony UX Live Component is not installed.');
        }

        $adapter = new LiveComponentAdapter($this->createMock(ComponentRendererInterface::class));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing component ID for live component adapter.');

        $adapter->render(new RenderRequest(id: 'live-button--default'));
    }
}
