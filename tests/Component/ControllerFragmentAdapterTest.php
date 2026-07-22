<?php

declare(strict_types=1);
use Storybook\SymfonyBundle\Component\ControllerFragmentAdapter;
use Storybook\SymfonyBundle\Dto\RenderRequest;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
test('renders controller fragment from controller field', function () {
    $fragmentHandler = $this->createMock(FragmentHandler::class);
    $fragmentHandler
        ->expects(self::once())
        ->method('render')
        ->willReturnCallback(function (ControllerReference $reference, string $renderer, array $options) {
            self::assertSame('App\\Controller\\AlertController::fragment', $reference->controller);
            self::assertSame(['message' => 'Hello'], $reference->query);
            self::assertSame('inline', $renderer);
            self::assertSame(['ignore_errors' => false], $options);

            return '<div class="alert">Hello</div>';
        });

    $adapter = new ControllerFragmentAdapter($fragmentHandler);
    $html = $adapter->render(new RenderRequest(
        id: 'alert-fragment--default',
        controller: 'App\\Controller\\AlertController::fragment',
        args: ['message' => 'Hello'],
    ));

    self::assertSame('<div class="alert">Hello</div>', $html);
});
test('falls back to component id', function () {
    $fragmentHandler = $this->createMock(FragmentHandler::class);
    $fragmentHandler
        ->method('render')
        ->willReturn('<div class="alert">Fallback</div>');

    $adapter = new ControllerFragmentAdapter($fragmentHandler);
    $html = $adapter->render(new RenderRequest(
        id: 'alert-fragment--default',
        componentId: 'App\\Controller\\AlertController::fragment',
    ));

    self::assertSame('<div class="alert">Fallback</div>', $html);
});
test('throws on missing controller reference', function () {
    $adapter = new ControllerFragmentAdapter($this->createMock(FragmentHandler::class));

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Missing controller reference.');

    $adapter->render(new RenderRequest(id: 'alert-fragment--default'));
});
