<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Storybook\SymfonyBundle\Asset\NullAssetPipeline;
use Storybook\SymfonyBundle\Controller\StorybookController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\TwigComponent\ComponentRendererInterface;

final class StorybookControllerTest extends TestCase
{
    public function testHealthReturnsOk(): void
    {
        $renderer = $this->createMock(ComponentRendererInterface::class);
        $controller = new StorybookController($renderer, new NullAssetPipeline());

        $response = $controller->health();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('{"status":"ok"}', $response->getContent());
    }

    public function testRenderReturnsHtmlFromComponentRenderer(): void
    {
        $renderer = $this->createMock(ComponentRendererInterface::class);
        $renderer
            ->method('createAndRender')
            ->with('Button', ['label' => 'Click me'])
            ->willReturn('<button>Click me</button>');

        $controller = new StorybookController($renderer, new NullAssetPipeline());
        $request = Request::create('/_storybook/render/button--primary', 'POST', [], [], [], [], json_encode([
            'componentId' => 'Button',
            'args' => ['label' => 'Click me'],
        ]));

        $response = $controller->render('button--primary', $request);

        self::assertSame(200, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        self::assertSame('<button>Click me</button>', $payload['html']);
        self::assertSame('button--primary', $payload['metadata']['component']);
        self::assertSame('none', $payload['assets']['pipeline']);
        self::assertSame([], $payload['assets']['styles']);
        self::assertSame([], $payload['assets']['scripts']);
    }

    public function testRenderReturnsBadRequestWithoutComponentId(): void
    {
        $renderer = $this->createMock(ComponentRendererInterface::class);
        $controller = new StorybookController($renderer, new NullAssetPipeline());
        $request = Request::create('/_storybook/render/button--primary', 'POST', [], [], [], [], json_encode([
            'args' => [],
        ]));

        $response = $controller->render('button--primary', $request);

        self::assertSame(400, $response->getStatusCode());
    }

    public function testRenderReturnsErrorWhenRendererThrows(): void
    {
        $renderer = $this->createMock(ComponentRendererInterface::class);
        $renderer
            ->method('createAndRender')
            ->willThrowException(new \RuntimeException('Component not found'));

        $controller = new StorybookController($renderer, new NullAssetPipeline());
        $request = Request::create('/_storybook/render/button--primary', 'POST', [], [], [], [], json_encode([
            'componentId' => 'Button',
        ]));

        $response = $controller->render('button--primary', $request);

        self::assertSame(500, $response->getStatusCode());
    }
}
