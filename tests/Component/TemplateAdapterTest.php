<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\Component;

use PHPUnit\Framework\TestCase;
use Storybook\SymfonyBundle\Component\TemplateAdapter;
use Storybook\SymfonyBundle\Dto\RenderRequest;
use Twig\Environment;

final class TemplateAdapterTest extends TestCase
{
    public function testRendersTemplateFromTemplateField(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig
            ->method('render')
            ->with('components/Alert.html.twig', ['message' => 'Hello'])
            ->willReturn('<div class="alert">Hello</div>');

        $adapter = new TemplateAdapter($twig);
        $html = $adapter->render(new RenderRequest(
            id: 'alert--default',
            template: 'templates/components/Alert.html.twig',
            args: ['message' => 'Hello'],
        ));

        self::assertSame('<div class="alert">Hello</div>', $html);
    }

    public function testFallsBackToComponentId(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig
            ->method('render')
            ->with('components/Alert.html.twig', [])
            ->willReturn('<div class="alert">Alert</div>');

        $adapter = new TemplateAdapter($twig);
        $html = $adapter->render(new RenderRequest(
            id: 'alert--default',
            componentId: 'templates/components/Alert.html.twig',
        ));

        self::assertSame('<div class="alert">Alert</div>', $html);
    }

    public function testKeepsPathsWithoutTemplatesPrefix(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig
            ->method('render')
            ->with('components/Alert.html.twig', [])
            ->willReturn('<div class="alert">Alert</div>');

        $adapter = new TemplateAdapter($twig);
        $html = $adapter->render(new RenderRequest(
            id: 'alert--default',
            template: 'components/Alert.html.twig',
        ));

        self::assertSame('<div class="alert">Alert</div>', $html);
    }

    public function testThrowsOnMissingTemplate(): void
    {
        $adapter = new TemplateAdapter($this->createMock(Environment::class));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing template path.');

        $adapter->render(new RenderRequest(id: 'alert--default'));
    }
}
