<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Storybook\SymfonyBundle\Asset\NullAssetPipeline;
use Storybook\SymfonyBundle\Component\ControllerFragmentAdapter;
use Storybook\SymfonyBundle\Component\TemplateAdapter;
use Storybook\SymfonyBundle\Component\TwigComponentAdapter;
use Storybook\SymfonyBundle\Controller\StorybookController;
use Storybook\SymfonyBundle\Indexer\ComponentIndexerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\UX\TwigComponent\ComponentRendererInterface;
use Twig\Environment;

final class StorybookControllerTest extends TestCase
{
    private function createController(
        ComponentRendererInterface $renderer,
        ?Environment $twig = null,
        ?FragmentHandler $fragmentHandler = null,
        ?ComponentIndexerInterface $componentIndexer = null,
    ): StorybookController {
        $twig ??= $this->createMock(Environment::class);
        $fragmentHandler ??= $this->createMock(FragmentHandler::class);

        return new StorybookController(
            new TwigComponentAdapter($renderer),
            new TemplateAdapter($twig),
            new ControllerFragmentAdapter($fragmentHandler),
            new NullAssetPipeline(),
            null,
            $componentIndexer,
        );
    }

    public function testHealthReturnsOk(): void
    {
        $controller = $this->createController($this->createMock(ComponentRendererInterface::class));

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

        $controller = $this->createController($renderer);
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

    public function testRenderDispatchesToTemplateAdapter(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig
            ->method('render')
            ->with('components/Alert.html.twig', ['message' => 'Hello'])
            ->willReturn('<div class="alert">Hello</div>');

        $controller = $this->createController($this->createMock(ComponentRendererInterface::class), $twig);
        $request = Request::create('/_storybook/render/alert--default', 'POST', [], [], [], [], json_encode([
            'template' => 'templates/components/Alert.html.twig',
            'args' => ['message' => 'Hello'],
        ]));

        $response = $controller->render('alert--default', $request);

        self::assertSame(200, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        self::assertSame('<div class="alert">Hello</div>', $payload['html']);
    }

    public function testRenderDispatchesToControllerFragmentAdapter(): void
    {
        $fragmentHandler = $this->createMock(FragmentHandler::class);
        $fragmentHandler
            ->method('render')
            ->willReturn('<div class="alert">Hello</div>');

        $controller = $this->createController(
            $this->createMock(ComponentRendererInterface::class),
            null,
            $fragmentHandler,
        );
        $request = Request::create('/_storybook/render/alert-fragment--default', 'POST', [], [], [], [], json_encode([
            'controller' => 'App\\Controller\\AlertController::fragment',
            'args' => ['message' => 'Hello'],
        ]));

        $response = $controller->render('alert-fragment--default', $request);

        self::assertSame(200, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        self::assertSame('<div class="alert">Hello</div>', $payload['html']);
    }

    public function testRenderInfersTemplateAdapterFromComponentId(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig
            ->method('render')
            ->with('components/Alert.html.twig', [])
            ->willReturn('<div class="alert">Alert</div>');

        $controller = $this->createController($this->createMock(ComponentRendererInterface::class), $twig);
        $request = Request::create('/_storybook/render/alert-template--default', 'POST', [], [], [], [], json_encode([
            'componentId' => 'templates/components/Alert.html.twig',
        ]));

        $response = $controller->render('alert-template--default', $request);

        self::assertSame(200, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        self::assertSame('<div class="alert">Alert</div>', $payload['html']);
    }

    public function testRenderInfersControllerAdapterFromComponentId(): void
    {
        $fragmentHandler = $this->createMock(FragmentHandler::class);
        $fragmentHandler
            ->method('render')
            ->willReturn('<div class="alert">Controller</div>');

        $controller = $this->createController(
            $this->createMock(ComponentRendererInterface::class),
            null,
            $fragmentHandler,
        );
        $request = Request::create('/_storybook/render/alert-fragment--default', 'POST', [], [], [], [], json_encode([
            'componentId' => 'App\\Controller\\AlertController::fragment',
        ]));

        $response = $controller->render('alert-fragment--default', $request);

        self::assertSame(200, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        self::assertSame('<div class="alert">Controller</div>', $payload['html']);
    }

    public function testRenderReturnsBadRequestWithoutComponentId(): void
    {
        $renderer = $this->createMock(ComponentRendererInterface::class);
        $controller = $this->createController($renderer);
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

        $controller = $this->createController($renderer);
        $request = Request::create('/_storybook/render/button--primary', 'POST', [], [], [], [], json_encode([
            'componentId' => 'Button',
        ]));

        $response = $controller->render('button--primary', $request);

        self::assertSame(500, $response->getStatusCode());
    }

    public function testRenderReturnsBadRequestForInvalidControllerAdapter(): void
    {
        $controller = $this->createController($this->createMock(ComponentRendererInterface::class));
        $request = Request::create('/_storybook/render/alert-fragment--default', 'POST', [], [], [], [], json_encode([
            'adapter' => 'controller',
        ]));

        $response = $controller->render('alert-fragment--default', $request);

        self::assertSame(400, $response->getStatusCode());
    }

    public function testIndexReturnsEmptyArrayWhenIndexerIsUnavailable(): void
    {
        $controller = $this->createController($this->createMock(ComponentRendererInterface::class));

        $response = $controller->index();

        self::assertSame(200, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        self::assertSame([], $payload['components']);
    }

    public function testIndexReturnsComponentsFromIndexer(): void
    {
        $indexer = $this->createMock(ComponentIndexerInterface::class);
        $indexer
            ->method('index')
            ->willReturn([
                [
                    'id' => 'Button',
                    'type' => 'twig_component',
                    'title' => 'Components/Button',
                    'template' => 'templates/components/Button.html.twig',
                    'class' => 'App\\Twig\\Components\\Button',
                    'props' => [],
                ],
            ]);

        $controller = $this->createController(
            $this->createMock(ComponentRendererInterface::class),
            null,
            null,
            $indexer,
        );

        $response = $controller->index();

        self::assertSame(200, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        self::assertCount(1, $payload['components']);
        self::assertSame('Button', $payload['components'][0]['id']);
    }

    public function testIndexReturnsErrorWhenIndexerThrows(): void
    {
        $indexer = $this->createMock(ComponentIndexerInterface::class);
        $indexer
            ->method('index')
            ->willThrowException(new \RuntimeException('Indexing failed'));

        $controller = $this->createController(
            $this->createMock(ComponentRendererInterface::class),
            null,
            null,
            $indexer,
        );

        $response = $controller->index();

        self::assertSame(500, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        self::assertSame('Indexing failed', $payload['message']);
    }

    public function testSourceReturnsTemplateAndClassSource(): void
    {
        $indexer = $this->createMock(ComponentIndexerInterface::class);
        $indexer
            ->method('getComponentSource')
            ->with('Button')
            ->willReturn([
                'template' => '<button>Button</button>',
                'class' => '<?php class Button {}',
            ]);

        $controller = $this->createController(
            $this->createMock(ComponentRendererInterface::class),
            null,
            null,
            $indexer,
        );

        $response = $controller->source('Button');

        self::assertSame(200, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        self::assertSame('<button>Button</button>', $payload['template']);
        self::assertSame('<?php class Button {}', $payload['class']);
    }

    public function testSourceReturnsErrorWhenIndexerThrows(): void
    {
        $indexer = $this->createMock(ComponentIndexerInterface::class);
        $indexer
            ->method('getComponentSource')
            ->willThrowException(new \RuntimeException('Source failed'));

        $controller = $this->createController(
            $this->createMock(ComponentRendererInterface::class),
            null,
            null,
            $indexer,
        );

        $response = $controller->source('Button');

        self::assertSame(500, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        self::assertSame('Source failed', $payload['message']);
    }
}
