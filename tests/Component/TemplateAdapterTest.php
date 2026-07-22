<?php

declare(strict_types=1);
use Storybook\SymfonyBundle\Component\TemplateAdapter;
use Storybook\SymfonyBundle\Dto\RenderRequest;
use Twig\Environment;
test('renders template from template field', function () {
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
});
test('falls back to component id', function () {
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
});
test('keeps paths without templates prefix', function () {
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
});
test('throws on missing template', function () {
    $adapter = new TemplateAdapter($this->createMock(Environment::class));

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Missing template path.');

    $adapter->render(new RenderRequest(id: 'alert--default'));
});
