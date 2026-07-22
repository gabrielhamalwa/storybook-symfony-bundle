<?php

declare(strict_types=1);
use Storybook\SymfonyBundle\Component\TwigComponentAdapter;
use Storybook\SymfonyBundle\Dto\RenderRequest;
use Symfony\UX\TwigComponent\ComponentRendererInterface;
test('renders component with args', function () {
    $renderer = $this->createMock(ComponentRendererInterface::class);
    $renderer
        ->method('createAndRender')
        ->with('Button', ['label' => 'Click me'])
        ->willReturn('<button>Click me</button>');

    $adapter = new TwigComponentAdapter($renderer);
    $html = $adapter->render(new RenderRequest(
        id: 'button--primary',
        componentId: 'Button',
        args: ['label' => 'Click me'],
    ));

    self::assertSame('<button>Click me</button>', $html);
});
test('throws on missing component id', function () {
    $adapter = new TwigComponentAdapter($this->createMock(ComponentRendererInterface::class));

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Missing component ID for Twig component adapter.');

    $adapter->render(new RenderRequest(id: 'button--primary'));
});
