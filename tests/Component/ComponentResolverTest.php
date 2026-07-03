<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\Component;

use PHPUnit\Framework\TestCase;
use Storybook\SymfonyBundle\Component\ComponentResolver;
use Storybook\SymfonyBundle\Component\ControllerFragmentAdapter;
use Storybook\SymfonyBundle\Component\LiveComponentAdapter;
use Storybook\SymfonyBundle\Component\TemplateAdapter;
use Storybook\SymfonyBundle\Component\TwigComponentAdapter;
use Storybook\SymfonyBundle\Dto\RenderRequest;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\UX\TwigComponent\ComponentRendererInterface;
use Twig\Environment;

final class ComponentResolverTest extends TestCase
{
    private function createResolver(?LiveComponentAdapter $liveComponentAdapter = null): ComponentResolver
    {
        return new ComponentResolver(
            new TwigComponentAdapter($this->createMock(ComponentRendererInterface::class)),
            new TemplateAdapter($this->createMock(Environment::class)),
            new ControllerFragmentAdapter($this->createMock(FragmentHandler::class)),
            $liveComponentAdapter,
        );
    }

    public function testResolvesTwigComponentByDefault(): void
    {
        $resolver = $this->createResolver();
        $adapter = $resolver->resolve(new RenderRequest(id: 'button--primary', componentId: 'Button'));

        self::assertInstanceOf(TwigComponentAdapter::class, $adapter);
    }

    public function testResolvesTemplateAdapterForTemplatePath(): void
    {
        $resolver = $this->createResolver();
        $adapter = $resolver->resolve(new RenderRequest(id: 'alert--primary', componentId: 'components/alert.html.twig'));

        self::assertInstanceOf(TemplateAdapter::class, $adapter);
    }

    public function testResolvesTemplateAdapterForExplicitAdapter(): void
    {
        $resolver = $this->createResolver();
        $adapter = $resolver->resolve(new RenderRequest(id: 'alert--primary', adapter: 'template'));

        self::assertInstanceOf(TemplateAdapter::class, $adapter);
    }

    public function testResolvesControllerAdapterForControllerReference(): void
    {
        $resolver = $this->createResolver();
        $adapter = $resolver->resolve(new RenderRequest(id: 'fragment--primary', componentId: 'App\\Controller\\DefaultController::fragment'));

        self::assertInstanceOf(ControllerFragmentAdapter::class, $adapter);
    }

    public function testResolvesControllerAdapterForExplicitAdapter(): void
    {
        $resolver = $this->createResolver();
        $adapter = $resolver->resolve(new RenderRequest(id: 'fragment--primary', adapter: 'controller'));

        self::assertInstanceOf(ControllerFragmentAdapter::class, $adapter);
    }

    public function testResolvesLiveAdapterForExplicitAdapter(): void
    {
        $resolver = $this->createResolver(
            new LiveComponentAdapter($this->createMock(ComponentRendererInterface::class))
        );
        $adapter = $resolver->resolve(new RenderRequest(id: 'live--primary', adapter: 'live'));

        self::assertInstanceOf(LiveComponentAdapter::class, $adapter);
    }

    public function testThrowsWhenLiveAdapterIsNotAvailable(): void
    {
        $resolver = $this->createResolver();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Live component adapter is not available.');

        $resolver->resolve(new RenderRequest(id: 'live--primary', adapter: 'live'));
    }
}
